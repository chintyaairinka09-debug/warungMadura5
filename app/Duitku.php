<?php
declare(strict_types=1);

final class Duitku
{
    private const SANDBOX_URL = 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry';
    private const LIVE_URL = 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry';
    private const SANDBOX_METHODS_URL = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';
    private const LIVE_METHODS_URL = 'https://passport.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';
    private const SANDBOX_STATUS_URL = 'https://sandbox.duitku.com/webapi/api/merchant/transactionStatus';
    private const LIVE_STATUS_URL = 'https://passport.duitku.com/webapi/api/merchant/transactionStatus';

    public static function checkTransactionStatus(PDO $db, string $orderCode, ?int $customerUserId = null): string
    {
        self::assertConfigured();
        $sql = "SELECT t.id_transaksi,t.total_belanja,t.status_pembayaran,t.metode_pembayaran,p.status_pembayaran AS payment_status FROM tbl_transaksi t JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi";
        $params = [$orderCode];
        if ($customerUserId !== null) {
            $sql .= ' JOIN tbl_pelanggan c ON c.id_pelanggan=t.id_pelanggan';
        }
        $sql .= ' WHERE t.kode_transaksi=?';
        if ($customerUserId !== null) {
            $sql .= ' AND c.id_pengguna=?';
            $params[] = $customerUserId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch();
        if (!$order || $order['metode_pembayaran'] !== 'duitku') throw new RuntimeException('Pesanan Duitku tidak ditemukan.');
        if ($order['status_pembayaran'] !== 'menunggu' || $order['payment_status'] !== 'menunggu') return (string)$order['status_pembayaran'];
        if (!function_exists('curl_init')) throw new RuntimeException('Ekstensi PHP cURL belum aktif.');

        $payload = [
            'merchantCode' => DUITKU_MERCHANT_CODE,
            'merchantOrderId' => $orderCode,
            'signature' => hash_hmac('sha256', DUITKU_MERCHANT_CODE . $orderCode, DUITKU_API_KEY),
        ];
        $url = DUITKU_SANDBOX ? self::SANDBOX_STATUS_URL : self::LIVE_STATUS_URL;
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 15,
        ]);
        $body = curl_exec($curl);
        $httpStatus = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($body === false) throw new RuntimeException('Koneksi cek status Duitku gagal: ' . $curlError);
        $result = json_decode((string)$body, true);
        if ($httpStatus !== 200 || !is_array($result) || ($result['merchantOrderId'] ?? '') !== $orderCode || !in_array((string)($result['statusCode'] ?? ''), ['00','01','02'], true)) {
            error_log('[Duitku status] HTTP ' . $httpStatus . ' ' . (string)$body);
            throw new RuntimeException('Status transaksi belum bisa dibaca dari Duitku.');
        }
        if ((int)($result['amount'] ?? 0) !== (int)round((float)$order['total_belanja'])) {
            throw new RuntimeException('Nominal status Duitku tidak sama dengan total pesanan.');
        }

        $db->beginTransaction();
        try {
            $lock = $db->prepare("SELECT t.id_transaksi,t.status_pembayaran,p.status_pembayaran AS payment_status FROM tbl_transaksi t JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi WHERE t.kode_transaksi=? AND t.metode_pembayaran='duitku' FOR UPDATE");
            $lock->execute([$orderCode]);
            $current = $lock->fetch();
            if ($current && $current['status_pembayaran'] === 'menunggu' && $current['payment_status'] === 'menunggu') {
                if ($result['statusCode'] === '00') {
                    $reference = substr((string)($result['reference'] ?? ''), 0, 80);
                    $db->prepare("UPDATE tbl_transaksi SET status_pembayaran='lunas',status_pesanan='diproses',jumlah_bayar=total_belanja,catatan='Pembayaran Duitku berhasil' WHERE id_transaksi=?")
                        ->execute([(int)$current['id_transaksi']]);
                    $db->prepare("UPDATE tbl_pembayaran SET status_pembayaran='berhasil',referensi_simulasi=COALESCE(NULLIF(?,''),referensi_simulasi),waktu_pembayaran=NOW() WHERE id_transaksi=?")
                        ->execute([$reference,(int)$current['id_transaksi']]);
                } elseif ($result['statusCode'] === '02') {
                    self::restoreStock($db, (int)$current['id_transaksi']);
                    $db->prepare("UPDATE tbl_transaksi SET status_pembayaran='gagal',status_pesanan='dibatalkan',catatan='Invoice Duitku gagal atau kedaluwarsa' WHERE id_transaksi=?")
                        ->execute([(int)$current['id_transaksi']]);
                    $db->prepare("UPDATE tbl_pembayaran SET status_pembayaran='gagal',waktu_pembayaran=NOW() WHERE id_transaksi=?")
                        ->execute([(int)$current['id_transaksi']]);
                } else {
                    $reference = substr((string)($result['reference'] ?? ''), 0, 80);
                    $vaNumber = substr((string)($result['vaNumber'] ?? ''), 0, 80);
                    $db->prepare("UPDATE tbl_pembayaran SET referensi_simulasi=COALESCE(NULLIF(?,''),referensi_simulasi),va_number=COALESCE(NULLIF(?,''),va_number) WHERE id_transaksi=?")
                        ->execute([$reference,$vaNumber,(int)$current['id_transaksi']]);
                }
            }
            $db->commit();
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
        return $result['statusCode'] === '00' ? 'lunas' : ($result['statusCode'] === '02' ? 'gagal' : 'menunggu');
    }

    public static function getPaymentMethods(int $amount): array
    {
        self::assertConfigured();
        if ($amount < 1) return [];
        if (!function_exists('curl_init')) throw new RuntimeException('Ekstensi PHP cURL belum aktif.');
        $datetime = date('Y-m-d H:i:s');
        $payload = [
            'merchantcode' => DUITKU_MERCHANT_CODE,
            'amount' => $amount,
            'datetime' => $datetime,
            'signature' => hash_hmac('sha256', DUITKU_MERCHANT_CODE . $amount . $datetime, DUITKU_API_KEY),
        ];
        $url = DUITKU_SANDBOX ? self::SANDBOX_METHODS_URL : self::LIVE_METHODS_URL;
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 15,
        ]);
        $body = curl_exec($curl);
        $httpStatus = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($body === false) throw new RuntimeException('Koneksi ke Duitku gagal: ' . $curlError);
        $response = json_decode((string)$body, true);
        if ($httpStatus !== 200 || !is_array($response) || ($response['responseCode'] ?? '') !== '00' || !is_array($response['paymentFee'] ?? null)) {
            error_log('[Duitku payment methods] HTTP ' . $httpStatus . ' ' . (string)$body);
            throw new RuntimeException('Daftar metode pembayaran Duitku tidak dapat dimuat.');
        }
        return array_values(array_filter($response['paymentFee'], static function ($method): bool {
            return is_array($method)
                && preg_match('/\A[A-Z0-9]{2}\z/', (string)($method['paymentMethod'] ?? ''))
                && trim((string)($method['paymentName'] ?? '')) !== '';
        }));
    }

    public static function createInvoice(PDO $db, int $transactionId, string $orderCode, int $amount, string $paymentMethod, array $customer, array $items): array
    {
        self::assertConfigured();
        if (!function_exists('curl_init')) throw new RuntimeException('Ekstensi PHP cURL belum aktif.');

        $productDetails = 'Belanja WarungMadura ' . $orderCode;
        $payload = [
            'merchantCode' => DUITKU_MERCHANT_CODE,
            'paymentAmount' => $amount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $orderCode,
            'productDetails' => $productDetails,
            'customerVaName' => substr((string)$customer['nama_pelanggan'], 0, 20),
            'email' => filter_var((string)($customer['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: 'pelanggan@warungmadura.local',
            'phoneNumber' => (string)($customer['no_hp'] ?? ''),
            'callbackUrl' => self::absoluteUrl('index.php?page=duitku_callback'),
            'returnUrl' => self::absoluteUrl('index.php?page=portal&payment_return=1'),
            'signature' => hash_hmac('sha256', DUITKU_MERCHANT_CODE . $orderCode . $amount, DUITKU_API_KEY),
            'expiryPeriod' => 60,
            'itemDetails' => array_map(static function (array $item): array {
                return [
                    'name' => substr((string)$item['product']['nama_produk'], 0, 50),
                    'price' => (int)round((float)$item['product']['harga_jual']),
                    'quantity' => (int)$item['quantity'],
                ];
            }, $items),
        ];

        $curl = curl_init(DUITKU_SANDBOX ? self::SANDBOX_URL : self::LIVE_URL);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);
        $responseBody = curl_exec($curl);
        $httpStatus = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($responseBody === false) throw new RuntimeException('Koneksi ke Duitku gagal: ' . $curlError);
        $response = json_decode((string)$responseBody, true);
        $paymentUrl = is_array($response) ? (string)($response['paymentUrl'] ?? '') : '';
        $paymentHost = strtolower((string)parse_url($paymentUrl, PHP_URL_HOST));
        $allowedHosts = DUITKU_SANDBOX ? ['sandbox.duitku.com', 'app-sandbox.duitku.com'] : ['passport.duitku.com', 'app.duitku.com'];
        if ($httpStatus < 200 || $httpStatus >= 300 || !is_array($response) || ($response['statusCode'] ?? '') !== '00' || !in_array($paymentHost, $allowedHosts, true) || strtolower((string)parse_url($paymentUrl, PHP_URL_SCHEME)) !== 'https') {
            error_log('[Duitku] Invoice response: HTTP ' . $httpStatus . ' ' . (string)$responseBody);
            throw new RuntimeException('Duitku tidak dapat membuat invoice. Periksa konfigurasi sandbox dan koneksi server.');
        }

        $db->prepare('UPDATE tbl_pembayaran SET referensi_simulasi=?,url_pembayaran=?,va_number=?,kode_metode_gateway=? WHERE id_transaksi=?')->execute([
            substr((string)($response['reference'] ?? ''), 0, 80), substr($paymentUrl, 0, 500), substr((string)($response['vaNumber'] ?? ''), 0, 80), $paymentMethod, $transactionId,
        ]);
        $db->prepare("UPDATE tbl_transaksi SET catatan='Menunggu pembayaran Duitku' WHERE id_transaksi=? AND status_pembayaran='menunggu'")->execute([$transactionId]);
        return ['paymentUrl' => $paymentUrl];
    }

    public static function handleCallback(PDO $db): never
    {
        self::assertConfigured();
        $merchantCode = (string)($_POST['merchantCode'] ?? '');
        $amount = (string)($_POST['amount'] ?? '');
        $orderCode = (string)($_POST['merchantOrderId'] ?? '');
        $signature = (string)($_POST['signature'] ?? '');
        $resultCode = (string)($_POST['resultCode'] ?? '');
        if ($merchantCode !== DUITKU_MERCHANT_CODE || !ctype_digit($amount) || $orderCode === '') {
            http_response_code(400); echo 'INVALID'; exit;
        }
        $expected = hash_hmac('sha256', $merchantCode . $amount . $orderCode, DUITKU_API_KEY);
        if ($signature === '' || !hash_equals($expected, $signature)) {
            http_response_code(403); echo 'INVALID SIGNATURE'; exit;
        }

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT t.id_transaksi,t.total_belanja,t.status_pembayaran,p.status_pembayaran AS payment_status FROM tbl_transaksi t JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi WHERE t.kode_transaksi=? AND t.metode_pembayaran='duitku' FOR UPDATE");
            $stmt->execute([$orderCode]);
            $transaction = $stmt->fetch();
            if (!$transaction || (int)round((float)$transaction['total_belanja']) !== (int)$amount) {
                $db->rollBack(); http_response_code(400); echo 'INVALID ORDER'; exit;
            }
            if ($resultCode === '00' && $transaction['status_pembayaran'] === 'menunggu' && $transaction['payment_status'] === 'menunggu') {
                $reference = substr((string)($_POST['reference'] ?? ''), 0, 80);
                $db->prepare("UPDATE tbl_transaksi SET status_pembayaran='lunas',status_pesanan='diproses',jumlah_bayar=total_belanja,catatan='Pembayaran Duitku berhasil' WHERE id_transaksi=?")
                    ->execute([(int)$transaction['id_transaksi']]);
                $db->prepare("UPDATE tbl_pembayaran SET status_pembayaran='berhasil',referensi_simulasi=?,waktu_pembayaran=NOW() WHERE id_transaksi=?")
                    ->execute([$reference, (int)$transaction['id_transaksi']]);
            } elseif ($resultCode === '02' && $transaction['status_pembayaran'] === 'menunggu' && $transaction['payment_status'] === 'menunggu') {
                self::restoreStock($db, (int)$transaction['id_transaksi']);
                $db->prepare("UPDATE tbl_transaksi SET status_pembayaran='gagal',status_pesanan='dibatalkan',catatan='Invoice Duitku kedaluwarsa atau gagal' WHERE id_transaksi=?")
                    ->execute([(int)$transaction['id_transaksi']]);
                $db->prepare("UPDATE tbl_pembayaran SET status_pembayaran='gagal',waktu_pembayaran=NOW() WHERE id_transaksi=?")
                    ->execute([(int)$transaction['id_transaksi']]);
            }
            $db->commit();
            http_response_code(200); echo 'SUCCESS'; exit;
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[Duitku callback] ' . $error->getMessage());
            http_response_code(500); echo 'ERROR'; exit;
        }
    }

    private static function assertConfigured(): void
    {
        if (!defined('DUITKU_MERCHANT_CODE') || !defined('DUITKU_API_KEY') || !defined('DUITKU_SANDBOX') || DUITKU_API_KEY === '' || str_starts_with(DUITKU_MERCHANT_CODE, 'ISI_')) {
            throw new RuntimeException('Konfigurasi Duitku belum lengkap.');
        }
    }

    private static function restoreStock(PDO $db, int $transactionId): void
    {
        $details = $db->prepare('SELECT id_produk,jumlah FROM tbl_detail_transaksi WHERE id_transaksi=? ORDER BY id_produk');
        $details->execute([$transactionId]);
        foreach ($details->fetchAll() as $detail) {
            $productId = (int)$detail['id_produk'];
            $stockQuery = $db->prepare('SELECT stok FROM tbl_produk WHERE id_produk=? FOR UPDATE');
            $stockQuery->execute([$productId]);
            $before = $stockQuery->fetchColumn();
            if ($before === false) throw new RuntimeException('Produk invoice Duitku tidak ditemukan untuk pengembalian stok.');
            $quantity = (int)$detail['jumlah'];
            $after = (int)$before + $quantity;
            $db->prepare('UPDATE tbl_produk SET stok=? WHERE id_produk=?')->execute([$after, $productId]);
            $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,id_transaksi,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,NULL,?,'pembatalan',?,?,?,'Invoice Duitku gagal atau kedaluwarsa')")
                ->execute([$productId, $transactionId, $quantity, (int)$before, $after]);
        }
    }

    private static function absoluteUrl(string $path): string
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = $https ? 'https' : 'http';
        $host = preg_replace('/[^A-Za-z0-9.:-]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
        return $scheme . '://' . $host . BASE_URL . '/' . ltrim($path, '/');
    }
}

<?php
declare(strict_types=1);

final class Actions
{
    public static function handle(PDO $db): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        Csrf::verify();
        $action = (string)($_POST['action'] ?? '');

        try {
            if ($action === 'login') {
                $username = trim((string)($_POST['username'] ?? ''));
                $password = (string)($_POST['password'] ?? '');
                if (Auth::attempt($db, $username, $password)) {
                    flash('success', 'Login berhasil. Selamat datang di WarungMadura.');
                    redirect((Auth::user()['role'] ?? '') === 'pelanggan' ? 'portal' : 'dashboard');
                }
                flash('danger', 'Username, password, atau status akun tidak sesuai.');
                redirect('login');
            }

            if (!Auth::check()) redirect('login');
            if ((Auth::user()['role'] ?? '') === 'pelanggan') {
                if ($action === 'customer_checkout') {
                    self::customerCheckout($db, (int)Auth::user()['id_pengguna']);
                }
                if ($action === 'customer_upload_payment_proof') {
                    self::uploadPaymentProof($db, (int)Auth::user()['id_pengguna']);
                }
                if ($action === 'customer_cancel_order') {
                    self::cancelCustomerOrder($db, (int)Auth::user()['id_pengguna']);
                }
                redirect('portal');
            }
            $userId = (int)Auth::user()['id_pengguna'];

            switch ($action) {
                case 'save_product': self::saveProduct($db, $userId); break;
                case 'deactivate_product': self::deactivateProduct($db, $userId); break;
                case 'import_products': self::importProducts($db, $userId); break;
                case 'save_category': self::saveCategory($db, $userId); break;
                case 'toggle_category': self::toggleCategory($db, $userId); break;
                case 'save_customer': self::saveCustomer($db, $userId); break;
                case 'save_user': self::saveUser($db, $userId); break;
                case 'toggle_user': self::toggleUser($db, $userId); break;
                case 'save_supplier': self::saveSupplier($db, $userId); break;
                case 'save_dana_qris': self::saveDanaQris(); break;
                case 'receive_stock': self::receiveStock($db, $userId); break;
                case 'checkout': self::checkout($db, $userId); break;
                case 'update_order_status': self::updateOrderStatus($db, $userId); break;
                case 'retry_duitku_invoice': self::retryDuitkuInvoice($db, $userId); break;
                case 'check_duitku_status': self::checkDuitkuStatus($db, $userId); break;
                case 'approve_customer_payment': self::reviewCustomerPayment($db, $userId, true); break;
                case 'reject_customer_payment': self::reviewCustomerPayment($db, $userId, false); break;
                case 'topup_balance': self::topupBalance($db, $userId); break;
                case 'save_expense': self::saveExpense($db, $userId); break;
                case 'delete_expense': self::deleteExpense($db, $userId); break;
                default:
                    flash('warning', 'Aksi tidak dikenali.');
                    redirect('dashboard');
            }
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[WarungMadura] ' . $error->getMessage());
            $friendly = $error instanceof PDOException && str_contains($error->getMessage(), 'Duplicate')
                ? 'Data sudah digunakan. Periksa SKU, barcode, username, atau nama yang harus unik.'
                : 'Terjadi kendala saat menyimpan data. Periksa isian dan coba kembali.';
            flash('danger', $friendly);
            redirect((string)($_POST['_return'] ?? 'dashboard'));
        }
    }

    private static function back(string $page, string $message, string $type = 'success', array $params = []): never
    {
        flash($type, $message);
        redirect($page, $params);
    }

    private static function adminOnly(): void
    {
        if (!Auth::isAdmin()) self::back('dashboard', 'Aksi ini hanya dapat dilakukan Admin.', 'danger');
    }

    private static function saveProduct(PDO $db, int $userId): void
    {
        $id = (int)($_POST['id_produk'] ?? 0);
        if ($id > 0 || !Auth::isAdmin()) self::adminOnly();
        $sku = trim((string)($_POST['sku'] ?? ''));
        $name = trim((string)($_POST['nama_produk'] ?? ''));
        $category = (int)($_POST['id_kategori'] ?? 0);
        $priceBuy = (float)($_POST['harga_beli'] ?? 0);
        $priceSell = (float)($_POST['harga_jual'] ?? 0);
        $stock = max(0, (int)($_POST['stok'] ?? 0));
        $minimum = max(0, (int)($_POST['stok_minimum'] ?? 5));
        $unit = trim((string)($_POST['satuan'] ?? 'pcs')) ?: 'pcs';
        $barcode = trim((string)($_POST['barcode'] ?? '')) ?: $sku;
        $description = trim((string)($_POST['deskripsi'] ?? '')) ?: null;
        $image = trim((string)($_POST['gambar'] ?? '')) ?: null;
        if ($image !== null && product_image_src($image) === null) {
            self::back('products', 'URL gambar harus menggunakan alamat http atau https yang valid.', 'warning');
        }
        if ($sku === '' || $name === '' || $category < 1 || $priceSell <= 0 || $priceBuy < 0) {
            self::back('products', 'Lengkapi SKU, nama, kategori, dan harga jual yang valid.', 'warning');
        }
        $uploadedImage = self::saveProductImage();
        if ($uploadedImage !== null) $image = $uploadedImage;
        $check = $db->prepare("SELECT COUNT(*) FROM tbl_kategori WHERE id_kategori=? AND status_kategori='aktif'");
        $check->execute([$category]);
        if ((int)$check->fetchColumn() === 0) self::back('products', 'Kategori tidak ditemukan atau tidak aktif.', 'warning');
        if ($id > 0) {
            $stmt = $db->prepare('UPDATE tbl_produk SET id_kategori=?, id_merk=NULL, sku=?, barcode=?, nama_produk=?, deskripsi=?, harga_beli=?, harga_jual=?, satuan=?, stok_minimum=?, gambar=? WHERE id_produk=?');
            $stmt->execute([$category, $sku, $barcode, $name, $description, $priceBuy, $priceSell, $unit, $minimum, $image, $id]);
            log_activity($db, $userId, 'edit_produk', 'tbl_produk', $id, 'Admin mengubah produk ' . $name);
            self::back('products', 'Produk berhasil diperbarui.');
        }
        $stmt = $db->prepare('INSERT INTO tbl_produk (id_kategori,id_merk,sku,barcode,nama_produk,deskripsi,harga_beli,harga_jual,stok,satuan,stok_minimum,gambar) VALUES (?,NULL,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$category, $sku, $barcode, $name, $description, $priceBuy, $priceSell, $stock, $unit, $minimum, $image]);
        $newId = (int)$db->lastInsertId();
        if ($stock > 0) {
            $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,?, 'penyesuaian', ?,0,?,'Stok awal produk baru')")
                ->execute([$newId, $userId, $stock, $stock]);
        }
        log_activity($db, $userId, 'tambah_produk', 'tbl_produk', $newId, 'Menambahkan produk ' . $name);
        self::back('products', 'Produk baru berhasil ditambahkan.');
    }

    private static function saveProductImage(): ?string
    {
        $file = $_FILES['gambar_file'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if (in_array(($file['error'] ?? UPLOAD_ERR_OK), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            self::back('products', 'Ukuran gambar melebihi batas upload. Ukuran maksimal 5 MB.', 'warning');
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            self::back('products', 'Gambar gagal diunggah. Pilih ulang file gambar dan coba kembali.', 'warning');
        }
        if ((int)($file['size'] ?? 0) > 5 * 1024 * 1024) {
            self::back('products', 'Ukuran gambar maksimal 5 MB.', 'warning');
        }
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            self::back('products', 'File gambar tidak valid. Pilih file gambar dari perangkat ini.', 'warning');
        }

        $imageInfo = getimagesize($file['tmp_name']);
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$imageInfo || !isset($extensions[$mime]) || ($imageInfo['mime'] ?? '') !== $mime) {
            self::back('products', 'Format gambar harus JPG, PNG, atau WebP.', 'warning');
        }

        $directory = __DIR__ . '/../uploads/products';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            self::back('products', 'Folder penyimpanan gambar tidak dapat dibuat. Periksa izin folder uploads.', 'danger');
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            self::back('products', 'Gambar tidak dapat disimpan. Periksa izin folder uploads/products.', 'danger');
        }
        return 'uploads/products/' . $filename;
    }

    private static function saveDanaQris(): void
    {
        self::adminOnly();
        $file = $_FILES['qris_file'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            self::back('transactions', 'Pilih gambar QRIS DANA terlebih dahulu.', 'warning');
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            self::back('transactions', 'Gambar QRIS gagal diunggah. Pilih ulang file dan coba kembali.', 'warning');
        }
        if ((int)($file['size'] ?? 0) > 5 * 1024 * 1024 || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            self::back('transactions', 'File QRIS tidak valid atau melebihi batas 5 MB.', 'warning');
        }

        $imageInfo = getimagesize($file['tmp_name']);
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$imageInfo || !isset($extensions[$mime]) || ($imageInfo['mime'] ?? '') !== $mime) {
            self::back('transactions', 'Format gambar QRIS harus JPG, PNG, atau WebP.', 'warning');
        }

        $directory = __DIR__ . '/../uploads/qris';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            self::back('transactions', 'Folder penyimpanan QRIS tidak dapat dibuat.', 'danger');
        }
        $filename = 'dana-qris.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            self::back('transactions', 'Gambar QRIS tidak dapat disimpan. Periksa izin folder uploads/qris.', 'danger');
        }
        if (file_put_contents($directory . '/current.txt', $filename, LOCK_EX) === false) {
            self::back('transactions', 'QRIS tersimpan tetapi penunjuk file gagal diperbarui.', 'danger');
        }
        self::back('transactions', 'QRIS DANA berhasil diperbarui.');
    }

    private static function deactivateProduct(PDO $db, int $userId): void
    {
        self::adminOnly();
        $id = (int)($_POST['id_produk'] ?? 0);
        if ($id < 1) self::back('products', 'Produk tidak valid.', 'warning');
        $db->prepare("UPDATE tbl_produk SET status_produk='nonaktif' WHERE id_produk=?")->execute([$id]);
        log_activity($db, $userId, 'nonaktif_produk', 'tbl_produk', $id, 'Produk dinonaktifkan, bukan dihapus permanen');
        self::back('products', 'Produk dinonaktifkan. Riwayat transaksi tetap tersimpan.');
    }

    private static function importProducts(PDO $db, int $userId): void
    {
        self::adminOnly();
        if (empty($_FILES['csv_file']['tmp_name']) || !is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
            self::back('products', 'Pilih file CSV terlebih dahulu.', 'warning');
        }
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$handle) self::back('products', 'File CSV tidak dapat dibaca.', 'danger');
        $header = fgetcsv($handle);
        if (!$header) { fclose($handle); self::back('products', 'File CSV kosong.', 'warning'); }
        $header = array_map(static fn($x) => strtolower(trim(str_replace("\xEF\xBB\xBF", '', (string)$x))), $header);
        if (array_diff(['sku','nama_produk','nama_kategori','harga_jual'], $header)) {
            fclose($handle); self::back('products', 'Header CSV tidak sesuai template.', 'warning');
        }
        $indexes = array_flip($header);
        $inserted = 0; $skipped = 0; $rowNo = 1;
        $db->beginTransaction();
        while (($row = fgetcsv($handle)) !== false) {
            $rowNo++;
            if (count(array_filter($row, static fn($v) => trim((string)$v) !== '')) === 0) continue;
            $get = static fn(string $key, string $default = '') => trim((string)($row[$indexes[$key]] ?? $default));
            $sku = $get('sku'); $name = $get('nama_produk'); $catName = $get('nama_kategori');
            $sell = (float)$get('harga_jual', '0');
            if ($sku === '' || $name === '' || $catName === '' || $sell <= 0) { $skipped++; continue; }
            $cat = $db->prepare("SELECT id_kategori FROM tbl_kategori WHERE nama_kategori=? AND status_kategori='aktif' LIMIT 1");
            $cat->execute([$catName]); $catId = $cat->fetchColumn();
            if (!$catId) { $skipped++; continue; }
            $check = $db->prepare('SELECT COUNT(*) FROM tbl_produk WHERE sku=?'); $check->execute([$sku]);
            if ((int)$check->fetchColumn() > 0) { $skipped++; continue; }
            $stmt = $db->prepare('INSERT INTO tbl_produk (id_kategori,id_merk,sku,barcode,nama_produk,deskripsi,harga_beli,harga_jual,stok,satuan,stok_minimum,gambar) VALUES (?,NULL,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([(int)$catId, $sku, $get('barcode') ?: $sku, $name, $get('deskripsi') ?: null, (float)$get('harga_beli','0'), $sell, max(0,(int)$get('stok','0')), $get('satuan','pcs') ?: 'pcs', max(0,(int)$get('stok_minimum','5')), $get('gambar') ?: null]);
            $productId = (int)$db->lastInsertId();
            $initialStock = max(0, (int)$get('stok','0'));
            if ($initialStock > 0) {
                $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,?, 'penyesuaian', ?,0,?,'Stok awal dari import CSV')")
                    ->execute([$productId, $userId, $initialStock, $initialStock]);
            }
            $inserted++;
        }
        fclose($handle);
        $db->commit();
        log_activity($db, $userId, 'import_produk_csv', 'tbl_produk', null, "Import CSV: {$inserted} ditambahkan, {$skipped} dilewati");
        self::back('products', "Import selesai: {$inserted} produk ditambahkan, {$skipped} baris dilewati.");
    }

    private static function saveCategory(PDO $db, int $userId): void
    {
        self::adminOnly();
        $id = (int)($_POST['id_kategori'] ?? 0);
        $name = trim((string)($_POST['nama_kategori'] ?? ''));
        $desc = trim((string)($_POST['deskripsi'] ?? '')) ?: null;
        if ($name === '') self::back('categories', 'Nama kategori wajib diisi.', 'warning');
        if ($id > 0) {
            $db->prepare('UPDATE tbl_kategori SET nama_kategori=?,deskripsi=? WHERE id_kategori=?')->execute([$name,$desc,$id]);
            log_activity($db,$userId,'edit_kategori','tbl_kategori',$id,'Mengubah kategori '.$name);
        } else {
            $db->prepare('INSERT INTO tbl_kategori (nama_kategori,deskripsi) VALUES (?,?)')->execute([$name,$desc]);
            log_activity($db,$userId,'tambah_kategori','tbl_kategori',(int)$db->lastInsertId(),'Menambahkan kategori '.$name);
        }
        self::back('categories', 'Kategori berhasil disimpan.');
    }

    private static function toggleCategory(PDO $db, int $userId): void
    {
        self::adminOnly();
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("UPDATE tbl_kategori SET status_kategori=IF(status_kategori='aktif','nonaktif','aktif') WHERE id_kategori=?");
        $stmt->execute([$id]);
        log_activity($db,$userId,'ubah_status_kategori','tbl_kategori',$id,'Mengubah status kategori');
        self::back('categories', 'Kategori berhasil diperbarui.');
    }

    private static function saveCustomer(PDO $db, int $userId): void
    {
        $id = (int)($_POST['id_pelanggan'] ?? 0);
        if ($id > 0) self::adminOnly();
        $name = trim((string)($_POST['nama_pelanggan'] ?? ''));
        $phone = trim((string)($_POST['no_hp'] ?? '')) ?: null;
        $email = trim((string)($_POST['email'] ?? '')) ?: null;
        $address = trim((string)($_POST['alamat'] ?? '')) ?: null;
        $city = trim((string)($_POST['kota'] ?? '')) ?: null;
        $member = trim((string)($_POST['no_member'] ?? ''));
        $rfidUid = trim((string)($_POST['rfid_uid'] ?? '')) ?: null;
        if ($name === '') self::back('customers', 'Nama pelanggan wajib diisi.', 'warning');
        if ($id > 0) {
            $db->prepare('UPDATE tbl_pelanggan SET rfid_uid=?,nama_pelanggan=?,no_hp=?,email=?,alamat=?,kota=? WHERE id_pelanggan=?')->execute([$rfidUid,$name,$phone,$email,$address,$city,$id]);
            log_activity($db,$userId,'edit_pelanggan','tbl_pelanggan',$id,'Mengubah pelanggan '.$name);
        } else {
            if ($member === '') $member = 'WM' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
            $db->prepare('INSERT INTO tbl_pelanggan (no_member,rfid_uid,nama_pelanggan,no_hp,email,alamat,kota) VALUES (?,?,?,?,?,?,?)')->execute([$member,$rfidUid,$name,$phone,$email,$address,$city]);
            $customerId = (int)$db->lastInsertId();
            foreach (['tabungan','ewallet'] as $type) {
                $db->prepare('INSERT INTO tbl_saldo_pelanggan (id_pelanggan,jenis_saldo,saldo) VALUES (?,?,0)')->execute([$customerId,$type]);
            }
            log_activity($db,$userId,'tambah_pelanggan','tbl_pelanggan',$customerId,'Pelanggan baru '.$name);
        }
        self::back('customers', 'Data pelanggan berhasil disimpan.');
    }

    private static function saveUser(PDO $db, int $userId): void
    {
        self::adminOnly();
        $id = (int)($_POST['id_pengguna'] ?? 0);
        $name = trim((string)($_POST['nama_lengkap'] ?? ''));
        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? '')) ?: null;
        $phone = trim((string)($_POST['no_hp'] ?? '')) ?: null;
        $role = (string)($_POST['role'] ?? 'kasir');
        $password = (string)($_POST['password'] ?? '');

        if ($name === '' || $username === '' || !in_array($role, ['admin', 'kasir', 'pelanggan'], true)) {
            self::back('users', 'Nama, username, dan role wajib diisi dengan benar.', 'warning');
        }
        if ($id === 0 && strlen($password) < 8) {
            self::back('users', 'Password akun baru minimal 8 karakter.', 'warning');
        }
        if ($password !== '' && strlen($password) < 8) {
            self::back('users', 'Password minimal 8 karakter.', 'warning');
        }
        if ($id === $userId && $role !== 'admin') {
            self::back('users', 'Role akun Admin yang sedang digunakan tidak dapat diubah.', 'warning');
        }

        $db->beginTransaction();
        try {
            if ($id > 0) {
                $current = $db->prepare('SELECT id_pengguna, role FROM tbl_pengguna WHERE id_pengguna=? FOR UPDATE');
                $current->execute([$id]);
                $existing = $current->fetch();
                if (!$existing) throw new RuntimeException('Akun pengguna tidak ditemukan.');

                if ($password !== '') {
                    $db->prepare('UPDATE tbl_pengguna SET nama_lengkap=?,username=?,email=?,no_hp=?,role=?,password_hash=? WHERE id_pengguna=?')
                        ->execute([$name, $username, $email, $phone, $role, password_hash($password, PASSWORD_DEFAULT), $id]);
                } else {
                    $db->prepare('UPDATE tbl_pengguna SET nama_lengkap=?,username=?,email=?,no_hp=?,role=? WHERE id_pengguna=?')
                        ->execute([$name, $username, $email, $phone, $role, $id]);
                }
                $savedId = $id;
            } else {
                $db->prepare('INSERT INTO tbl_pengguna (nama_lengkap,username,email,no_hp,password_hash,role) VALUES (?,?,?,?,?,?)')
                    ->execute([$name, $username, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $role]);
                $savedId = (int)$db->lastInsertId();
            }

            if ($role === 'pelanggan') {
                $profile = $db->prepare('SELECT id_pelanggan FROM tbl_pelanggan WHERE id_pengguna=? FOR UPDATE');
                $profile->execute([$savedId]);
                $customerId = (int)($profile->fetchColumn() ?: 0);
                if ($customerId > 0) {
                    $db->prepare('UPDATE tbl_pelanggan SET nama_pelanggan=?,no_hp=?,email=? WHERE id_pelanggan=?')
                        ->execute([$name, $phone, $email, $customerId]);
                } else {
                    $memberNumber = 'WM' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
                    $db->prepare('INSERT INTO tbl_pelanggan (id_pengguna,no_member,nama_pelanggan,no_hp,email) VALUES (?,?,?,?,?)')
                        ->execute([$savedId, $memberNumber, $name, $phone, $email]);
                    $customerId = (int)$db->lastInsertId();
                }
                foreach (['tabungan', 'ewallet'] as $balanceType) {
                    $db->prepare('INSERT IGNORE INTO tbl_saldo_pelanggan (id_pelanggan,jenis_saldo,saldo) VALUES (?,?,0)')
                        ->execute([$customerId, $balanceType]);
                }
            } else {
                // Jika akun pelanggan diubah menjadi akun staf, nomor member tetap tersimpan,
                // tetapi profil member tidak lagi terhubung ke akun login staf.
                $db->prepare('UPDATE tbl_pelanggan SET id_pengguna=NULL WHERE id_pengguna=?')->execute([$savedId]);
            }

            log_activity($db, $userId, $id ? 'edit_pengguna' : 'tambah_pengguna', 'tbl_pengguna', $savedId, 'Akun ' . $username . ' (' . $role . ') disimpan');
            $db->commit();
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }

        self::back('users', $id ? 'Akun berhasil diperbarui.' : 'Akun baru berhasil dibuat.');
    }

    private static function toggleUser(PDO $db, int $userId): void
    {
        self::adminOnly();
        $id = (int)($_POST['id_pengguna'] ?? 0);
        if ($id === $userId) self::back('users','Kamu tidak dapat menonaktifkan akun yang sedang digunakan.','warning');
        $db->prepare("UPDATE tbl_pengguna SET status_pengguna=IF(status_pengguna='aktif','nonaktif','aktif') WHERE id_pengguna=?")->execute([$id]);
        log_activity($db,$userId,'ubah_status_pengguna','tbl_pengguna',$id,'Mengubah status akun');
        self::back('users','Status akun diperbarui.');
    }

    private static function saveSupplier(PDO $db, int $userId): void
    {
        self::adminOnly();
        $code = trim((string)($_POST['kode_supplier'] ?? ''));
        $name = trim((string)($_POST['nama_supplier'] ?? ''));
        $contact = trim((string)($_POST['nama_kontak'] ?? '')) ?: null;
        $phone = trim((string)($_POST['no_hp'] ?? '')) ?: null;
        $email = trim((string)($_POST['email'] ?? '')) ?: null;
        $address = trim((string)($_POST['alamat'] ?? '')) ?: null;
        if ($code === '' || $name === '') self::back('stock','Kode dan nama supplier wajib diisi.','warning');
        $db->prepare('INSERT INTO tbl_supplier (kode_supplier,nama_supplier,nama_kontak,no_hp,email,alamat) VALUES (?,?,?,?,?,?)')->execute([$code,$name,$contact,$phone,$email,$address]);
        log_activity($db,$userId,'tambah_supplier','tbl_supplier',(int)$db->lastInsertId(),'Supplier '.$name);
        self::back('stock','Supplier berhasil ditambahkan.');
    }

    private static function receiveStock(PDO $db, int $userId): void
    {
        if (!Auth::canUseOperations()) self::back('dashboard','Akses tidak diizinkan.','danger');
        $supplierId = (int)($_POST['id_supplier'] ?? 0) ?: null;
        $productIds = $_POST['id_produk'] ?? [];
        $qtys = $_POST['jumlah'] ?? [];
        $costs = $_POST['harga_beli'] ?? [];
        if (!is_array($productIds) || count($productIds) === 0) self::back('stock','Tambahkan minimal satu produk.','warning');
        $validLines = 0;
        foreach ($productIds as $index => $productValue) {
            if ((int)$productValue > 0 && (int)($qtys[$index] ?? 0) > 0) $validLines++;
        }
        if ($validLines === 0) self::back('stock','Masukkan produk dan jumlah barang yang valid.','warning');
        $db->beginTransaction();
        $number = 'TRM-' . date('Ymd-His') . '-' . random_int(100,999);
        $db->prepare("INSERT INTO tbl_penerimaan_barang (no_penerimaan,id_supplier,id_pengguna,status_penerimaan,catatan) VALUES (?,?,?,'diterima',?)")
            ->execute([$number,$supplierId,$userId,trim((string)($_POST['catatan'] ?? '')) ?: null]);
        $receiptId = (int)$db->lastInsertId();
        foreach ($productIds as $index => $productValue) {
            $productId = (int)$productValue; $qty = max(0,(int)($qtys[$index] ?? 0)); $cost = max(0,(float)($costs[$index] ?? 0));
            if ($productId < 1 || $qty < 1) continue;
            $product = $db->prepare('SELECT stok,nama_produk FROM tbl_produk WHERE id_produk=? AND status_produk="aktif" FOR UPDATE');
            $product->execute([$productId]); $row = $product->fetch();
            if (!$row) throw new RuntimeException('Produk stok masuk tidak ditemukan.');
            $before = (int)$row['stok']; $after = $before + $qty;
            $db->prepare('INSERT INTO tbl_detail_penerimaan_barang (id_penerimaan,id_produk,jumlah,harga_beli,subtotal) VALUES (?,?,?,?,?)')->execute([$receiptId,$productId,$qty,$cost,$qty*$cost]);
            $db->prepare('UPDATE tbl_produk SET stok=?, harga_beli=IF(? > 0, ?, harga_beli) WHERE id_produk=?')->execute([$after,$cost,$cost,$productId]);
            $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,id_penerimaan,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,?,?,'stok_masuk',?,?,?,?)")
                ->execute([$productId,$userId,$receiptId,$qty,$before,$after,'Penerimaan '.$number]);
        }
        $db->commit();
        log_activity($db,$userId,'penerimaan_barang','tbl_penerimaan_barang',$receiptId,'Penerimaan '.$number);
        self::back('stock','Barang berhasil diterima dan stok diperbarui.');
    }

    private static function checkout(PDO $db, int $userId): void
    {
        if (!Auth::canUseOperations()) self::back('dashboard','Akses tidak diizinkan.','danger');
        $cart = json_decode((string)($_POST['cart_json'] ?? '[]'), true);
        if (!is_array($cart) || !$cart) self::back('transactions','Keranjang masih kosong.','warning');
        $customerType = (string)($_POST['customer_type'] ?? 'umum');
        if (!in_array($customerType,['umum','member'],true)) self::back('transactions','Jenis pelanggan tidak valid.','warning');
        $customerId = null;
        if ($customerType === 'member') {
            $selectedCustomerId = (int)($_POST['id_pelanggan'] ?? 0);
            if ($selectedCustomerId <= 0) self::back('transactions','Pilih data pelanggan member terlebih dahulu.','warning');
            $customerCheck = $db->prepare("SELECT id_pelanggan FROM tbl_pelanggan WHERE id_pelanggan=? AND status_member='aktif'");
            $customerCheck->execute([$selectedCustomerId]);
            $customerId = $customerCheck->fetchColumn();
            if (!$customerId) self::back('transactions','Data pelanggan member tidak ditemukan atau sudah nonaktif.','warning');
            $customerId = (int)$customerId;
        }
        $method = (string)($_POST['metode_pembayaran'] ?? 'tunai');
        if (!in_array($method,['tunai','tabungan','ewallet','qris_dana'],true)) self::back('transactions','Metode pembayaran tidak valid.','warning');
        if (in_array($method,['tabungan','ewallet'],true) && !$customerId) self::back('transactions','Pembayaran saldo membutuhkan pelanggan terdaftar.','warning');
        if ($method === 'qris_dana' && (string)($_POST['qris_dana_confirmed'] ?? '') !== '1') {
            self::back('transactions','Kasir harus mengecek dana masuk dan mengonfirmasi pembayaran QRIS.','warning');
        }
        if ($method === 'qris_dana') {
            $qrisDirectory = __DIR__ . '/../uploads/qris';
            $pointer = $qrisDirectory . '/current.txt';
            $filename = is_file($pointer) ? trim((string)file_get_contents($pointer)) : '';
            if (!preg_match('/\Adana-qris\.(?:jpg|png|webp)\z/i', $filename) || !is_file($qrisDirectory . '/' . $filename)) {
                self::back('transactions','QRIS DANA Merchant belum diunggah oleh Admin.','warning');
            }
        }
        $cash = max(0,(float)($_POST['jumlah_bayar'] ?? 0));
        $normalized = [];
        foreach ($cart as $item) {
            $id = (int)($item['id'] ?? 0); $qty = (int)($item['qty'] ?? 0);
            if ($id > 0 && $qty > 0) $normalized[$id] = ($normalized[$id] ?? 0) + $qty;
        }
        if (!$normalized) self::back('transactions','Isi keranjang tidak valid.','warning');

        $db->beginTransaction();
        $items = []; $subtotal = 0.0;
        foreach ($normalized as $productId => $qty) {
            $stmt = $db->prepare("SELECT id_produk,sku,nama_produk,harga_jual,stok FROM tbl_produk WHERE id_produk=? AND status_produk='aktif' FOR UPDATE");
            $stmt->execute([$productId]); $product = $stmt->fetch();
            if (!$product) throw new RuntimeException('Ada produk yang tidak tersedia.');
            if ((int)$product['stok'] < $qty) throw new RuntimeException('Stok ' . $product['nama_produk'] . ' tidak mencukupi.');
            $line = (float)$product['harga_jual'] * $qty; $subtotal += $line;
            $items[] = ['product'=>$product,'qty'=>$qty,'line'=>$line];
        }
        $total = $subtotal;
        $balanceId = null; $balanceBefore = 0.0;
        if ($method === 'tunai') {
            if ($cash < $total) throw new RuntimeException('Uang pembayaran kurang dari total belanja.');
            $paid = $cash; $change = $cash - $total;
        } elseif (in_array($method,['tabungan','ewallet'],true)) {
            $balance = $db->prepare('SELECT id_saldo,saldo FROM tbl_saldo_pelanggan WHERE id_pelanggan=? AND jenis_saldo=? AND status_saldo="aktif" FOR UPDATE');
            $balance->execute([$customerId,$method]); $wallet = $balance->fetch();
            if (!$wallet) throw new RuntimeException('Saldo pelanggan untuk metode ini belum tersedia.');
            $balanceId = (int)$wallet['id_saldo']; $balanceBefore = (float)$wallet['saldo'];
            if ($balanceBefore < $total) throw new RuntimeException('Saldo pelanggan tidak mencukupi.');
            $paid = $total; $change = 0.0;
        } else {
            $paid = $total; $change = 0.0;
        }
        $code = 'WM-' . date('ymd-His') . '-' . random_int(100,999);
        $db->prepare("INSERT INTO tbl_transaksi (kode_transaksi,id_pelanggan,id_kasir,jenis_transaksi,status_pesanan,status_pembayaran,metode_pembayaran,metode_penerimaan,tanggal_transaksi,subtotal,diskon,biaya_pengiriman,total_belanja,jumlah_bayar,kembalian) VALUES (?,?,?,'toko','selesai','lunas',?,'ambil_toko',NOW(),?,0,0,?,?,?)")
            ->execute([$code,$customerId,$userId,$method,$subtotal,$total,$paid,$change]);
        $transactionId = (int)$db->lastInsertId();
        foreach ($items as $item) {
            $p = $item['product']; $qty = $item['qty']; $line = $item['line'];
            $db->prepare('INSERT INTO tbl_detail_transaksi (id_transaksi,id_produk,nama_produk,sku_produk,jumlah,harga_satuan,diskon_item,harga_modal,subtotal_item) VALUES (?,?,?,?,?,?,0,?,?)')
                ->execute([$transactionId,$p['id_produk'],$p['nama_produk'],$p['sku'],$qty,$p['harga_jual'],(float)$p['harga_beli'],$line]);
            $before = (int)$p['stok']; $after = $before - $qty;
            $db->prepare('UPDATE tbl_produk SET stok=? WHERE id_produk=?')->execute([$after,$p['id_produk']]);
            $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,id_transaksi,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,?,?,'penjualan',?,?,?,'Penjualan kasir')")
                ->execute([$p['id_produk'],$userId,$transactionId,-$qty,$before,$after]);
        }
        $db->prepare('INSERT INTO tbl_pembayaran (id_transaksi,metode_pembayaran,jumlah_bayar,status_pembayaran,referensi_simulasi,diproses_oleh,waktu_pembayaran) VALUES (?,?,?,"berhasil",?,?,NOW())')
            ->execute([$transactionId,$method,$paid,$method === 'qris_dana' ? null : 'SIM-' . strtoupper(bin2hex(random_bytes(4))),$userId]);
        if ($balanceId !== null) {
            $after = $balanceBefore - $total;
            $db->prepare('UPDATE tbl_saldo_pelanggan SET saldo=? WHERE id_saldo=?')->execute([$after,$balanceId]);
            $db->prepare("INSERT INTO tbl_mutasi_saldo (id_saldo,id_transaksi,id_pengguna,jenis_mutasi,arah,jumlah,saldo_sebelum,saldo_sesudah,keterangan) VALUES (?,?,?,'pembayaran','keluar',?,?,?,'Pembayaran transaksi toko')")
                ->execute([$balanceId,$transactionId,$userId,$total,$balanceBefore,$after]);
        }
        $db->commit();
        log_activity($db,$userId,'checkout','tbl_transaksi',$transactionId,'Transaksi '.$code);
        self::back('receipt','Transaksi berhasil.', 'success', ['id'=>$transactionId]);
    }

    private static function customerCheckout(PDO $db, int $userId): void
    {
        $profileStmt = $db->prepare("SELECT id_pelanggan,nama_pelanggan,no_hp,email FROM tbl_pelanggan WHERE id_pengguna=? AND status_member='aktif' LIMIT 1");
        $profileStmt->execute([$userId]);
        $customer = $profileStmt->fetch();
        $customerId = (int)($customer['id_pelanggan'] ?? 0);
        if ($customerId < 1) self::back('portal', 'Akun ini belum terhubung ke member aktif.', 'danger');

        $cart = json_decode((string)($_POST['cart_json'] ?? '[]'), true);
        if (!is_array($cart) || !$cart) self::back('portal', 'Keranjang masih kosong.', 'warning');
        $normalized = [];
        foreach ($cart as $item) {
            if (!is_array($item)) continue;
            $productId = (int)($item['id'] ?? 0);
            $quantity = (int)($item['qty'] ?? 0);
            if ($productId > 0 && $quantity > 0) {
                $normalized[$productId] = ($normalized[$productId] ?? 0) + $quantity;
            }
        }
        if (!$normalized) self::back('portal', 'Isi keranjang tidak valid.', 'warning');
        $paymentMethod = strtoupper(trim((string)($_POST['payment_method'] ?? '')));
        if (!preg_match('/\A[A-Z0-9]{2}\z/', $paymentMethod)) self::back('portal','Pilih metode pembayaran Duitku terlebih dahulu.','warning');

        $db->beginTransaction();
        $items = [];
        $subtotal = 0.0;
        foreach ($normalized as $productId => $quantity) {
            $productStmt = $db->prepare("SELECT id_produk,sku,nama_produk,harga_jual,harga_beli,stok FROM tbl_produk WHERE id_produk=? AND status_produk='aktif' FOR UPDATE");
            $productStmt->execute([$productId]);
            $product = $productStmt->fetch();
            if (!$product) throw new RuntimeException('Ada produk yang sudah tidak tersedia.');
            if ((int)$product['stok'] < $quantity) {
                throw new RuntimeException('Stok ' . $product['nama_produk'] . ' tidak mencukupi.');
            }
            $lineTotal = (float)$product['harga_jual'] * $quantity;
            $subtotal += $lineTotal;
            $items[] = ['product'=>$product,'quantity'=>$quantity,'total'=>$lineTotal];
        }
        if ($subtotal < 10000) {
            $db->rollBack();
            self::back('portal','Minimum pembayaran Duitku adalah Rp 10.000. Tambahkan produk lain ke keranjang.','warning');
        }

        $code = 'WM-ON-' . date('ymd-His') . '-' . random_int(100,999);
        $db->prepare("INSERT INTO tbl_transaksi (kode_transaksi,id_pelanggan,id_kasir,jenis_transaksi,status_pesanan,status_pembayaran,metode_pembayaran,metode_penerimaan,tanggal_transaksi,subtotal,diskon,biaya_pengiriman,total_belanja,jumlah_bayar,kembalian,catatan) VALUES (?,?,NULL,'online','menunggu','menunggu','duitku','ambil_toko',NOW(),?,0,0,?,0,0,'Menunggu pembayaran Duitku')")
            ->execute([$code,$customerId,$subtotal,$subtotal]);
        $transactionId = (int)$db->lastInsertId();

        foreach ($items as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];
            $before = (int)$product['stok'];
            $after = $before - $quantity;
            $db->prepare('INSERT INTO tbl_detail_transaksi (id_transaksi,id_produk,nama_produk,sku_produk,jumlah,harga_satuan,diskon_item,harga_modal,subtotal_item) VALUES (?,?,?,?,?,?,0,?,?)')
                ->execute([$transactionId,$product['id_produk'],$product['nama_produk'],$product['sku'],$quantity,$product['harga_jual'],$product['harga_beli'],$item['total']]);
            $db->prepare('UPDATE tbl_produk SET stok=? WHERE id_produk=?')->execute([$after,$product['id_produk']]);
            $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,id_transaksi,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,NULL,?,'penjualan',?,?,?,'Checkout online pelanggan')")
                ->execute([$product['id_produk'],$transactionId,-$quantity,$before,$after]);
        }

        $db->prepare("INSERT INTO tbl_pembayaran (id_transaksi,metode_pembayaran,kode_metode_gateway,jumlah_bayar,status_pembayaran) VALUES (?,'duitku',?,?,'menunggu')")
            ->execute([$transactionId,$paymentMethod,$subtotal]);
        $db->commit();
        log_activity($db,$userId,'checkout_online','tbl_transaksi',$transactionId,'Pesanan pelanggan '.$code);
        try {
            $invoice = Duitku::createInvoice($db, $transactionId, $code, (int)round($subtotal), $paymentMethod, $customer, $items);
        } catch (Throwable $error) {
            error_log('[Duitku invoice] ' . $error->getMessage());
            $db->prepare("UPDATE tbl_transaksi SET catatan='Gagal membuka invoice Duitku; perlu pemeriksaan Admin' WHERE id_transaksi=? AND status_pembayaran='menunggu'")
                ->execute([$transactionId]);
            self::back('portal','Pesanan sudah tercatat, tetapi halaman pembayaran belum dapat dibuka. Jangan checkout ulang; hubungi Admin untuk memeriksa status invoice.','warning');
        }
        header('Location: ' . $invoice['paymentUrl'], true, 303);
        exit;
    }

    private static function retryDuitkuInvoice(PDO $db, int $userId): void
    {
        if (!Auth::canUseOperations()) self::back('dashboard','Aksi ini hanya dapat dilakukan Admin/Kasir.','danger');
        $transactionId = (int)($_POST['id_transaksi'] ?? 0);
        if ($transactionId < 1) self::back('payments','Pesanan Duitku tidak valid.','warning');

        $stmt = $db->prepare("SELECT t.kode_transaksi,t.total_belanja,t.status_pembayaran,t.metode_pembayaran,p.status_pembayaran AS payment_status,p.kode_metode_gateway,p.url_pembayaran,p.referensi_simulasi,c.nama_pelanggan,c.no_hp,c.email FROM tbl_transaksi t JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi JOIN tbl_pelanggan c ON c.id_pelanggan=t.id_pelanggan WHERE t.id_transaksi=? AND t.jenis_transaksi='online'");
        $stmt->execute([$transactionId]);
        $order = $stmt->fetch();
        if (!$order || $order['metode_pembayaran'] !== 'duitku' || $order['status_pembayaran'] !== 'menunggu' || $order['payment_status'] !== 'menunggu') {
            self::back('payments','Pesanan tidak lagi menunggu pembayaran Duitku.','warning');
        }
        if (!empty($order['url_pembayaran']) || !empty($order['referensi_simulasi'])) {
            self::back('payments','Invoice sudah tercatat. Buka tautan invoice pada daftar pembayaran online.','info');
        }

        $paymentMethod = strtoupper((string)($order['kode_metode_gateway'] ?? ''));
        if (!preg_match('/\A[A-Z0-9]{2}\z/', $paymentMethod)) {
            $availableMethods = Duitku::getPaymentMethods((int)round((float)$order['total_belanja']));
            $paymentMethod = (string)($availableMethods[0]['paymentMethod'] ?? '');
        }
        if (!preg_match('/\A[A-Z0-9]{2}\z/', $paymentMethod)) {
            self::back('payments','Duitku belum menyediakan metode pembayaran untuk pesanan ini.','warning');
        }

        $detailStmt = $db->prepare('SELECT nama_produk,harga_satuan,jumlah FROM tbl_detail_transaksi WHERE id_transaksi=? ORDER BY id_detail');
        $detailStmt->execute([$transactionId]);
        $items = array_map(static function (array $item): array {
            return ['product'=>['nama_produk'=>$item['nama_produk'],'harga_jual'=>$item['harga_satuan']], 'quantity'=>(int)$item['jumlah']];
        }, $detailStmt->fetchAll());
        if (!$items) self::back('payments','Rincian produk pesanan tidak ditemukan.','danger');

        Duitku::createInvoice($db,$transactionId,(string)$order['kode_transaksi'],(int)round((float)$order['total_belanja']),$paymentMethod,$order,$items);
        $db->prepare("UPDATE tbl_transaksi SET catatan='Menunggu pembayaran Duitku' WHERE id_transaksi=? AND status_pembayaran='menunggu'")->execute([$transactionId]);
        log_activity($db,$userId,'buat_ulang_invoice_duitku','tbl_transaksi',$transactionId,'Admin/Kasir membuat invoice ulang untuk '.$order['kode_transaksi']);
        self::back('payments','Invoice berhasil dibuat ulang. Tautan sekarang tersedia untuk pelanggan dan Admin/Kasir.');
    }

    private static function checkDuitkuStatus(PDO $db, int $userId): void
    {
        if (!Auth::canUseOperations()) self::back('dashboard','Aksi ini hanya dapat dilakukan Admin/Kasir.','danger');
        $transactionId = (int)($_POST['id_transaksi'] ?? 0);
        $stmt = $db->prepare("SELECT kode_transaksi FROM tbl_transaksi WHERE id_transaksi=? AND metode_pembayaran='duitku' AND status_pembayaran='menunggu'");
        $stmt->execute([$transactionId]);
        $orderCode = (string)$stmt->fetchColumn();
        if ($orderCode === '') self::back('payments','Pesanan Duitku tidak ditemukan atau sudah selesai.','warning');
        $status = Duitku::checkTransactionStatus($db,$orderCode);
        if ($status === 'lunas') {
            log_activity($db,$userId,'cek_status_duitku','tbl_transaksi',$transactionId,'Duitku mengonfirmasi pembayaran '.$orderCode);
            self::back('payments','Duitku mengonfirmasi pembayaran berhasil. Pesanan sekarang diproses.');
        }
        if ($status === 'gagal') {
            log_activity($db,$userId,'cek_status_duitku','tbl_transaksi',$transactionId,'Duitku menyatakan pembayaran gagal/kedaluwarsa '.$orderCode);
            self::back('payments','Duitku menyatakan invoice gagal atau kedaluwarsa. Stok sudah dikembalikan.','warning');
        }
        self::back('payments','Status dari Duitku masih menunggu pembayaran.','info');
    }

    private static function uploadPaymentProof(PDO $db, int $userId): void
    {
        $transactionId = (int)($_POST['id_transaksi'] ?? 0);
        $file = $_FILES['bukti_pembayaran'] ?? null;
        if ($transactionId < 1 || !$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            self::back('portal','Pilih screenshot atau foto bukti pembayaran terlebih dahulu.','warning');
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            self::back('portal','Bukti pembayaran gagal diunggah. Pilih file lain dan coba kembali.','warning');
        }
        if ((int)($file['size'] ?? 0) > 5 * 1024 * 1024 || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            self::back('portal','Bukti pembayaran tidak valid atau melebihi batas 5 MB.','warning');
        }
        $imageInfo = getimagesize($file['tmp_name']);
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if (!$imageInfo || !isset($extensions[$mime]) || ($imageInfo['mime'] ?? '') !== $mime) {
            self::back('portal','Bukti pembayaran harus berupa gambar JPG, PNG, atau WebP.','warning');
        }

        $directory = __DIR__ . '/../uploads/payment-proofs';
        if (!is_dir($directory) && !mkdir($directory,0755,true) && !is_dir($directory)) {
            self::back('portal','Folder penyimpanan bukti pembayaran tidak dapat dibuat.','danger');
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $path = $directory . '/' . $filename;
        $relativePath = 'uploads/payment-proofs/' . $filename;
        $db->beginTransaction();
        try {
            $check = $db->prepare("SELECT t.id_transaksi FROM tbl_transaksi t JOIN tbl_pelanggan c ON c.id_pelanggan=t.id_pelanggan JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi WHERE t.id_transaksi=? AND c.id_pengguna=? AND t.jenis_transaksi='online' AND t.metode_pembayaran='qris_dana' AND t.status_pesanan='menunggu' AND t.status_pembayaran='menunggu' AND p.status_pembayaran='menunggu' AND p.bukti_pembayaran IS NULL FOR UPDATE");
            $check->execute([$transactionId,$userId]);
            if (!$check->fetchColumn()) {
                $db->rollBack();
                self::back('portal','Pesanan tidak ditemukan, sudah dibayar, atau bukti sebelumnya sudah dikirim.','warning');
            }
            if (!move_uploaded_file($file['tmp_name'],$path)) {
                $db->rollBack();
                self::back('portal','Bukti pembayaran tidak dapat disimpan. Periksa izin folder uploads/payment-proofs.','danger');
            }
            $db->prepare('UPDATE tbl_pembayaran SET bukti_pembayaran=? WHERE id_transaksi=?')->execute([$relativePath,$transactionId]);
            $db->commit();
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            if (is_file($path)) unlink($path);
            throw $error;
        }
        log_activity($db,$userId,'unggah_bukti_pembayaran','tbl_pembayaran',$transactionId,'Bukti QRIS diunggah untuk verifikasi');
        self::back('portal','Bukti pembayaran berhasil dikirim dan sedang menunggu verifikasi Admin/Kasir.');
    }

    private static function cancelCustomerOrder(PDO $db, int $userId): void
    {
        $transactionId = (int)($_POST['id_transaksi'] ?? 0);
        if ($transactionId < 1) self::back('portal','Pesanan tidak valid.','warning');
        $db->beginTransaction();
        $check = $db->prepare("SELECT t.id_transaksi FROM tbl_transaksi t JOIN tbl_pelanggan c ON c.id_pelanggan=t.id_pelanggan JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi WHERE t.id_transaksi=? AND c.id_pengguna=? AND t.jenis_transaksi='online' AND t.status_pesanan='menunggu' AND t.status_pembayaran='menunggu' AND p.status_pembayaran='menunggu' AND p.bukti_pembayaran IS NULL FOR UPDATE");
        $check->execute([$transactionId,$userId]);
        if (!$check->fetchColumn()) {
            $db->rollBack();
            self::back('portal','Pesanan yang sudah mengirim bukti tidak dapat dibatalkan dari portal. Hubungi Admin/Kasir untuk bantuan.','warning');
        }
        self::restoreReservedStock($db,$transactionId,$userId,'Pesanan dibatalkan pelanggan sebelum mengirim bukti QRIS');
        $db->prepare("UPDATE tbl_transaksi SET status_pesanan='dibatalkan',status_pembayaran='gagal' WHERE id_transaksi=?")->execute([$transactionId]);
        $db->prepare("UPDATE tbl_pembayaran SET status_pembayaran='gagal',diproses_oleh=?,waktu_pembayaran=NOW() WHERE id_transaksi=?")->execute([$userId,$transactionId]);
        $db->commit();
        log_activity($db,$userId,'batalkan_pesanan_online','tbl_transaksi',$transactionId,'Pelanggan membatalkan pesanan sebelum mengirim bukti pembayaran');
        self::back('portal','Pesanan dibatalkan dan stok sudah dikembalikan.');
    }

    private static function reviewCustomerPayment(PDO $db, int $userId, bool $approve): void
    {
        if (!Auth::canUseOperations()) self::back('dashboard','Akses tidak diizinkan.','danger');
        $transactionId = (int)($_POST['id_transaksi'] ?? 0);
        if ($transactionId < 1) self::back('payments','Pesanan tidak valid.','warning');
        $db->beginTransaction();
        $check = $db->prepare("SELECT t.id_transaksi,t.total_belanja FROM tbl_transaksi t JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi WHERE t.id_transaksi=? AND t.jenis_transaksi='online' AND t.metode_pembayaran='qris_dana' AND t.status_pesanan='menunggu' AND t.status_pembayaran='menunggu' AND p.status_pembayaran='menunggu' AND p.bukti_pembayaran IS NOT NULL FOR UPDATE");
        $check->execute([$transactionId]);
        $transaction = $check->fetch();
        if (!$transaction) {
            $db->rollBack();
            self::back('payments','Pesanan tidak lagi menunggu verifikasi atau bukti tidak ditemukan.','warning');
        }
        if ($approve) {
            $db->prepare("UPDATE tbl_transaksi SET status_pesanan='diproses',status_pembayaran='lunas',jumlah_bayar=total_belanja WHERE id_transaksi=?")->execute([$transactionId]);
            $db->prepare("UPDATE tbl_pembayaran SET status_pembayaran='berhasil',diproses_oleh=?,waktu_pembayaran=NOW() WHERE id_transaksi=?")->execute([$userId,$transactionId]);
        } else {
            self::restoreReservedStock($db,$transactionId,$userId,'Pembayaran QRIS ditolak dan pesanan dibatalkan');
            $db->prepare("UPDATE tbl_transaksi SET status_pesanan='dibatalkan',status_pembayaran='gagal' WHERE id_transaksi=?")->execute([$transactionId]);
            $db->prepare("UPDATE tbl_pembayaran SET status_pembayaran='gagal',diproses_oleh=?,waktu_pembayaran=NOW() WHERE id_transaksi=?")->execute([$userId,$transactionId]);
        }
        $db->commit();
        log_activity($db,$userId,$approve?'verifikasi_pembayaran_qris':'tolak_pembayaran_qris','tbl_transaksi',$transactionId,$approve?'Bukti QRIS disetujui':'Bukti QRIS ditolak; pesanan dibatalkan dan stok dikembalikan');
        self::back('payments',$approve?'Pembayaran disetujui. Pesanan sekarang diproses.':'Bukti ditolak, pesanan dibatalkan, dan stok dikembalikan.');
    }

    private static function restoreReservedStock(PDO $db, int $transactionId, int $userId, string $reason): void
    {
        $details = $db->prepare('SELECT id_produk,jumlah FROM tbl_detail_transaksi WHERE id_transaksi=? ORDER BY id_produk');
        $details->execute([$transactionId]);
        foreach ($details->fetchAll() as $detail) {
            $stockStmt = $db->prepare('SELECT stok FROM tbl_produk WHERE id_produk=? FOR UPDATE');
            $stockStmt->execute([(int)$detail['id_produk']]);
            $before = $stockStmt->fetchColumn();
            if ($before === false) throw new RuntimeException('Produk pesanan tidak ditemukan saat mengembalikan stok.');
            $after = (int)$before + (int)$detail['jumlah'];
            $db->prepare('UPDATE tbl_produk SET stok=? WHERE id_produk=?')->execute([$after,(int)$detail['id_produk']]);
            $db->prepare("INSERT INTO tbl_mutasi_stok (id_produk,id_pengguna,id_transaksi,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,keterangan) VALUES (?,?,?,'pembatalan',?,?,?,?)")
                ->execute([(int)$detail['id_produk'],$userId,$transactionId,(int)$detail['jumlah'],(int)$before,$after,$reason]);
        }
    }

    private static function updateOrderStatus(PDO $db, int $userId): void
    {
        $id = (int)($_POST['id_transaksi'] ?? 0);
        $status = (string)($_POST['status_pesanan'] ?? '');
        $allowed = ['menunggu','diproses','siap_diambil','selesai','dibatalkan'];
        if (!in_array($status,$allowed,true)) self::back('reports','Status pesanan tidak valid.','warning');
        if (!Auth::isAdmin() && !in_array($status,['diproses','siap_diambil','selesai'],true)) self::back('reports','Kasir hanya dapat memperbarui proses pesanan.','danger');
        $current = $db->prepare('SELECT status_pesanan,status_pembayaran,jenis_transaksi FROM tbl_transaksi WHERE id_transaksi=?');
        $current->execute([$id]); $currentRow = $current->fetch();
        if (!$currentRow) self::back('reports','Transaksi tidak ditemukan.','warning');
        if ($currentRow['jenis_transaksi'] === 'online' && $currentRow['status_pembayaran'] !== 'lunas') self::back('payments','Pesanan online hanya dapat diproses setelah pembayaran disetujui melalui menu Verifikasi QRIS.','warning');
        if ($status === 'dibatalkan' && $currentRow['status_pembayaran'] === 'lunas') self::back('reports','Transaksi lunas tidak dapat dibatalkan dari menu ini karena perlu proses pengembalian stok dan saldo secara terpisah.','warning');
        $db->prepare('UPDATE tbl_transaksi SET status_pesanan=? WHERE id_transaksi=?')->execute([$status,$id]);
        log_activity($db,$userId,'ubah_status_transaksi','tbl_transaksi',$id,'Status pesanan menjadi '.$status);
        self::back('reports','Status pesanan diperbarui.');
    }

    private static function saveExpense(PDO $db, int $userId): void
    {
        $id = (int)($_POST['id_pengeluaran'] ?? 0);
        $category = (string)($_POST['kategori_pengeluaran'] ?? 'lainnya');
        $description = trim((string)($_POST['deskripsi'] ?? ''));
        $amount = (float)($_POST['jumlah'] ?? 0);
        $date = trim((string)($_POST['tanggal_pengeluaran'] ?? ''));
        $allowedCategories = ['listrik','transportasi','perlengkapan','gaji','sewa','lainnya'];
        if (!in_array($category, $allowedCategories, true) || $description === '' || $amount <= 0 || $date === '') {
            self::back('reports', 'Kategori, deskripsi, tanggal, dan nominal pengeluaran wajib diisi.', 'warning');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $date) && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $date)) {
            self::back('reports', 'Tanggal pengeluaran tidak valid.', 'warning');
        }
        $date = str_replace('T', ' ', $date);
        if (strlen($date) === 16) $date .= ':00';
        if ($id > 0) {
            $ownershipSql = Auth::isAdmin() ? '' : ' AND id_pengguna=?';
            $ownershipParams = Auth::isAdmin() ? [] : [$userId];
            $check = $db->prepare('SELECT id_pengeluaran FROM tbl_pengeluaran WHERE id_pengeluaran=?' . $ownershipSql);
            $check->execute(array_merge([$id], $ownershipParams));
            if (!$check->fetchColumn()) self::back('reports', 'Kamu hanya dapat mengubah pengeluaran yang kamu catat.', 'danger');
            $db->prepare('UPDATE tbl_pengeluaran SET kategori_pengeluaran=?,deskripsi=?,jumlah=?,tanggal_pengeluaran=? WHERE id_pengeluaran=?' . $ownershipSql)
                ->execute(array_merge([$category, $description, $amount, $date, $id], $ownershipParams));
            $savedId = $id;
            log_activity($db, $userId, 'edit_pengeluaran', 'tbl_pengeluaran', $savedId, 'Mengubah pengeluaran '.$description);
        } else {
            $number = 'EXP-' . date('Ymd-His') . '-' . random_int(100, 999);
            $db->prepare('INSERT INTO tbl_pengeluaran (no_pengeluaran,kategori_pengeluaran,deskripsi,jumlah,tanggal_pengeluaran,id_pengguna) VALUES (?,?,?,?,?,?)')
                ->execute([$number, $category, $description, $amount, $date, $userId]);
            $savedId = (int)$db->lastInsertId();
            log_activity($db, $userId, 'tambah_pengeluaran', 'tbl_pengeluaran', $savedId, 'Menambah pengeluaran '.$description);
        }
        self::back('reports', 'Pengeluaran berhasil disimpan.');
    }

    private static function deleteExpense(PDO $db, int $userId): void
    {
        $id = (int)($_POST['id_pengeluaran'] ?? 0);
        if ($id < 1) self::back('reports', 'Pengeluaran tidak valid.', 'warning');
        $ownershipSql = Auth::isAdmin() ? '' : ' AND id_pengguna=?';
        $ownershipParams = Auth::isAdmin() ? [] : [$userId];
        $delete = $db->prepare('DELETE FROM tbl_pengeluaran WHERE id_pengeluaran=?' . $ownershipSql);
        $delete->execute(array_merge([$id], $ownershipParams));
        if ($delete->rowCount() === 0) self::back('reports', 'Kamu hanya dapat menghapus pengeluaran yang kamu catat.', 'danger');
        log_activity($db, $userId, 'hapus_pengeluaran', 'tbl_pengeluaran', $id, 'Menghapus pengeluaran operasional');
        self::back('reports', 'Pengeluaran berhasil dihapus.');
    }

    private static function topupBalance(PDO $db, int $userId): void
    {
        self::adminOnly();
        $customerId = (int)($_POST['id_pelanggan'] ?? 0);
        $type = (string)($_POST['jenis_saldo'] ?? 'tabungan');
        $amount = (float)($_POST['jumlah'] ?? 0);
        if ($customerId < 1 || !in_array($type,['tabungan','ewallet'],true) || $amount <= 0) self::back('customers','Data top-up tidak valid.','warning');
        $db->beginTransaction();
        $stmt = $db->prepare('SELECT id_saldo,saldo FROM tbl_saldo_pelanggan WHERE id_pelanggan=? AND jenis_saldo=? FOR UPDATE');
        $stmt->execute([$customerId,$type]); $balance = $stmt->fetch();
        if (!$balance) {
            $db->prepare('INSERT INTO tbl_saldo_pelanggan (id_pelanggan,jenis_saldo,saldo) VALUES (?,?,0)')->execute([$customerId,$type]);
            $balance = ['id_saldo'=>(int)$db->lastInsertId(),'saldo'=>0];
        }
        $before = (float)$balance['saldo']; $after = $before + $amount;
        $db->prepare('UPDATE tbl_saldo_pelanggan SET saldo=? WHERE id_saldo=?')->execute([$after,$balance['id_saldo']]);
        $db->prepare("INSERT INTO tbl_mutasi_saldo (id_saldo,id_pengguna,jenis_mutasi,arah,jumlah,saldo_sebelum,saldo_sesudah,keterangan) VALUES (?,?, 'topup_simulasi','masuk',?,?,?,'Top-up saldo simulasi oleh Admin')")
            ->execute([$balance['id_saldo'],$userId,$amount,$before,$after]);
        $db->commit();
        log_activity($db,$userId,'topup_saldo','tbl_saldo_pelanggan',(int)$balance['id_saldo'],'Top-up simulasi pelanggan '.$customerId);
        self::back('customers','Top-up saldo simulasi berhasil dicatat.');
    }
}

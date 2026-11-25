<?php
$transactionId = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT t.id_transaksi,t.id_pelanggan,p.bukti_pembayaran FROM tbl_transaksi t JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi WHERE t.id_transaksi=? LIMIT 1');
$stmt->execute([$transactionId]);
$payment = $stmt->fetch();
if (!$payment || !$payment['bukti_pembayaran']) {
    http_response_code(404);
    exit('Bukti pembayaran tidak ditemukan.');
}

if ((Auth::user()['role'] ?? '') === 'pelanggan') {
    $owner = $db->prepare('SELECT id_pelanggan FROM tbl_pelanggan WHERE id_pengguna=? LIMIT 1');
    $owner->execute([(int)Auth::user()['id_pengguna']]);
    if ((int)$owner->fetchColumn() !== (int)$payment['id_pelanggan']) {
        http_response_code(403);
        exit('Kamu tidak memiliki akses ke bukti pembayaran ini.');
    }
} elseif (!Auth::canUseOperations()) {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

$relativePath = (string)$payment['bukti_pembayaran'];
if (!preg_match('#\Auploads/payment-proofs/[a-f0-9]{32}\.(?:jpg|png|webp)\z#', $relativePath)) {
    http_response_code(404);
    exit('Bukti pembayaran tidak ditemukan.');
}
$directory = realpath(__DIR__ . '/../uploads/payment-proofs');
$filePath = realpath(__DIR__ . '/../' . $relativePath);
if (!$directory || !$filePath || !str_starts_with($filePath, $directory . DIRECTORY_SEPARATOR) || !is_file($filePath)) {
    http_response_code(404);
    exit('Bukti pembayaran tidak ditemukan.');
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($filePath);
if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
    http_response_code(404);
    exit('Format bukti pembayaran tidak didukung.');
}
header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($filePath));
header('Content-Disposition: inline; filename="bukti-pembayaran-' . $transactionId . '"');
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
readfile($filePath);

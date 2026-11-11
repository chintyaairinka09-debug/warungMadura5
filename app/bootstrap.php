<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Csrf.php';
require_once __DIR__ . '/Actions.php';

try {
    $db = Database::connection();
} catch (Throwable $error) {
    http_response_code(500);
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Koneksi Database</title><body style="font:16px Arial,sans-serif;background:#f5f7f5;padding:30px;color:#24342a"><main style="max-width:680px;margin:8vh auto;background:white;border:1px solid #e4ebe5;border-radius:16px;padding:28px"><h2>WarungMadura belum terhubung ke database</h2><p>Pastikan MySQL di XAMPP sudah aktif dan file <b>db_warungmadura.sql</b> sudah di-import melalui phpMyAdmin.</p><p>Periksa juga pengaturan host, nama database, username, dan password di <code>config/config.php</code>.</p><p style="color:#78867c;font-size:13px">Detail teknis: ' . htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8') . '</p></main></body></html>';
    exit;
}

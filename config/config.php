<?php
declare(strict_types=1);

define('APP_NAME', 'WarungMadura');
define('APP_TAGLINE', 'Belanja Praktis, Harga Bersahabat');
$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$baseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
define('BASE_URL', $baseUrl === '.' ? '' : $baseUrl);
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'db_warungmadura');
define('DB_USER', 'root');
define('DB_PASS', '');

// Rahasia Duitku dimuat dari config/duitku.local.php (tidak disertakan di Git).
$duitkuLocalConfig = __DIR__ . '/duitku.local.php';
if (is_file($duitkuLocalConfig)) {
    require_once $duitkuLocalConfig;
}

date_default_timezone_set('Asia/Jakarta');
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name('warungmadura_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BASE_URL . '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

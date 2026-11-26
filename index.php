<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

if (($_GET['page'] ?? '') === 'duitku_callback') {
    Duitku::handleCallback($db);
}
if (($_GET['page'] ?? '') === 'duitku_methods') {
    header('Content-Type: application/json; charset=utf-8');
    if (!Auth::check() || (!Auth::canUseOperations() && (Auth::user()['role'] ?? '') !== 'pelanggan')) {
        http_response_code(403);
        echo json_encode(['error'=>'Akses tidak diizinkan.']);
        exit;
    }
    try {
        $amount = max(0, min(100000000, (int)($_GET['amount'] ?? 0)));
        echo json_encode(['methods'=>Duitku::getPaymentMethods($amount)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch (Throwable $error) {
        error_log('[Duitku payment methods] ' . $error->getMessage());
        http_response_code(502);
        echo json_encode(['error'=>'Metode pembayaran sedang tidak dapat dimuat.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Actions::handle($db);
}

$page = (string)($_GET['page'] ?? (Auth::check() ? 'dashboard' : 'login'));
if ($page === 'logout') {
    Auth::logout($db);
    header('Location: ' . url('login'));
    exit;
}

if (!Auth::check() && $page !== 'login') redirect('login');
if (Auth::check() && $page === 'login') {
    redirect((Auth::user()['role'] ?? '') === 'pelanggan' ? 'portal' : 'dashboard');
}

if ($page === 'payment_proof') {
    require __DIR__ . '/pages/payment_proof.php';
    exit;
}

// Akun pelanggan diarahkan ke portal sederhana dan tidak dapat membuka panel admin/kasir.
if (Auth::check() && (Auth::user()['role'] ?? '') === 'pelanggan') {
    if ($page === 'receipt') {
        require __DIR__ . '/pages/receipt.php';
        exit;
    }
    if ($page !== 'portal') redirect('portal');
    require __DIR__ . '/pages/portal.php';
    exit;
}
if ($page === 'portal') redirect('dashboard');

$allowedPages = ['dashboard','products','categories','customers','users','stock','transactions','payments','reports','report_export','report_print','suppliers','receipt','login','portal'];
if (!in_array($page, $allowedPages, true)) $page = 'dashboard';
if (in_array($page, ['categories','users','suppliers'], true) && !Auth::isAdmin()) {
    flash('danger', 'Kamu tidak memiliki izin untuk membuka menu tersebut.');
    redirect('dashboard');
}
if (in_array($page, ['stock','transactions','payments'], true) && !Auth::canUseOperations()) redirect('dashboard');

if ($page === 'login') {
    require __DIR__ . '/pages/login.php';
    exit;
}
if ($page === 'report_export') {
    require __DIR__ . '/pages/report_export.php';
    exit;
}
if ($page === 'report_print') {
    $_GET['format'] = 'print';
    require __DIR__ . '/pages/report_export.php';
    exit;
}
if ($page === 'receipt') {
    require __DIR__ . '/pages/receipt.php';
    exit;
}

require __DIR__ . '/partials/header.php';
$path = __DIR__ . '/pages/' . $page . '.php';
if (is_file($path)) require $path;
else require __DIR__ . '/pages/dashboard.php';
require __DIR__ . '/partials/footer.php';

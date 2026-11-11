<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

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

$allowedPages = ['dashboard','products','categories','customers','users','stock','transactions','reports','suppliers','receipt','login','portal'];
if (!in_array($page, $allowedPages, true)) $page = 'dashboard';
if (in_array($page, ['categories','users','suppliers'], true) && !Auth::isAdmin()) {
    flash('danger', 'Kamu tidak memiliki izin untuk membuka menu tersebut.');
    redirect('dashboard');
}
if (in_array($page, ['stock','transactions'], true) && !Auth::canUseOperations()) redirect('dashboard');

if ($page === 'login') {
    require __DIR__ . '/pages/login.php';
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

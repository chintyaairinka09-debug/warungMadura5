<?php
$user = Auth::user();
$current = $page;
$menus = [
    ['title'=>'MENU UTAMA','items'=>[
        ['dashboard','Dashboard','bi-grid-1x2'],
        ['transactions','Kasir / Transaksi','bi-cart-check'],
        ['products','Produk','bi-box-seam'],
        ['categories','Kategori','bi-tags'],
        ['customers','Pelanggan / Member','bi-people'],
    ]],
    ['title'=>'OPERASIONAL','items'=>[
        ['stock','Stok Masuk','bi-box-arrow-in-down'],
        ['reports','Laporan Penjualan','bi-graph-up-arrow'],
        ['suppliers','Supplier','bi-truck'],
    ]],
    ['title'=>'PENGATURAN','items'=>[
        ['users','Pengguna & Hak Akses','bi-person-gear'],
    ]],
];
$flashMessage = take_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(ucwords(str_replace('_',' ',$current))) ?> · <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/app.css?v=7">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand-lockup" href="<?= e(url('dashboard')) ?>">
            <span class="brand-mark"><i class="bi bi-shop-window"></i></span>
            <span><strong>Warung<span>Madura</span></strong><small>ADMIN PANEL</small></span>
        </a>
        <div class="sidebar-store"><span class="store-dot"></span><span>Operasional Toko</span><span class="ms-auto small">LOCAL</span></div>
        <?php foreach ($menus as $group): ?>
            <div class="nav-section-label"><?= e($group['title']) ?></div>
            <nav class="nav flex-column side-nav">
                <?php foreach ($group['items'] as [$key,$label,$icon]): ?>
                    <?php if (in_array($key,['categories','users','suppliers'],true) && !Auth::isAdmin()) continue; ?>
                    <?php if ($key === 'transactions' && !Auth::canUseOperations()) continue; ?>
                    <a class="nav-link <?= $current === $key ? 'active' : '' ?>" href="<?= e(url($key)) ?>">
                        <i class="bi <?= e($icon) ?>"></i><span><?= e($label) ?></span>
                        <?php if ($key === 'products'): ?><i class="bi bi-chevron-right ms-auto nav-chevron"></i><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endforeach; ?>
        <div class="sidebar-bottom">
            <div class="sidebar-tip"><i class="bi bi-shield-check"></i><div><b>Data tersimpan lokal</b><small>Database MariaDB XAMPP</small></div></div>
            <a class="nav-link logout-link" href="<?= e(url('logout')) ?>"><i class="bi bi-box-arrow-left"></i><span>Keluar</span></a>
        </div>
    </aside>

    <main class="main-area">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-icon mobile-menu" id="sidebarToggle" type="button" aria-label="Buka menu"><i class="bi bi-list"></i></button>
                <div><div class="topbar-eyebrow">WARUNGMADURA / ADMINISTRASI</div><div class="topbar-title"><?= e(ucwords(str_replace('_',' ',$current))) ?></div></div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="topbar-date d-none d-md-block"><i class="bi bi-calendar3 me-1"></i><?= e(date('d M Y')) ?></div>
                <div class="user-menu"><div class="avatar"><?= e(strtoupper(substr($user['nama_lengkap'] ?? 'A',0,1))) ?></div><div class="user-meta"><b><?= e($user['nama_lengkap'] ?? 'Admin') ?></b><small><?= e(ucfirst($user['role'] ?? 'admin')) ?></small></div></div>
            </div>
        </header>
        <div class="page-content">
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?= e($flashMessage['type']) ?> alert-dismissible fade show app-alert" role="alert">
                    <?= e($flashMessage['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

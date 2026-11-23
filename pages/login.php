<?php
$flashMessage = take_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · WarungMadura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/app.css?v=6">
</head>
<body class="login-body">
<div class="login-layout">
    <section class="login-visual">
        <div class="login-logo"><span class="brand-mark"><i class="bi bi-shop-window"></i></span><span>Warung<span>Madura</span></span></div>
        <div class="login-visual-copy">
            <span class="eyebrow-pill"><span class="store-dot"></span> SISTEM MANAJEMEN TOKO</span>
            <h1>Kelola toko lebih mudah, <em>setiap hari.</em></h1>
            <p>Atur produk, persediaan, transaksi, dan laporan penjualan dalam satu dashboard.</p>
            <div class="login-feature-list"><span><i class="bi bi-check-circle-fill"></i> Manajemen produk & kategori</span><span><i class="bi bi-check-circle-fill"></i> Transaksi kasir terintegrasi</span><span><i class="bi bi-check-circle-fill"></i> Laporan penjualan real-time</span></div>
        </div>
        <div class="login-visual-footer"><span><?= e(APP_TAGLINE) ?></span><span>© <?= date('Y') ?> WarungMadura</span></div>
        <div class="login-orb orb-one"></div><div class="login-orb orb-two"></div>
    </section>
    <section class="login-form-panel">
        <div class="login-form-wrap">
            <div class="login-mobile-logo"><span class="brand-mark"><i class="bi bi-shop-window"></i></span><b>Warung<span>Madura</span></b></div>
            <div class="login-welcome"><span class="section-kicker">HALO, SELAMAT DATANG</span><h2>Masuk ke akunmu</h2><p>Login sebagai Admin, Kasir, atau Pelanggan menggunakan akun yang terdaftar.</p></div>
            <?php if ($flashMessage): ?><div class="alert alert-<?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div><?php endif; ?>
            <form method="post" action="<?= e(url('login')) ?>" class="login-form">
                <?= Csrf::field() ?><input type="hidden" name="action" value="login">
                <label class="form-label">Username</label><div class="input-icon-wrap"><i class="bi bi-person"></i><input class="form-control form-control-lg" type="text" name="username" placeholder="Masukkan username" autocomplete="username" required autofocus></div>
                <label class="form-label mt-3">Password</label><div class="input-icon-wrap"><i class="bi bi-lock"></i><input class="form-control form-control-lg" type="password" id="loginPassword" name="password" placeholder="Masukkan password" autocomplete="current-password" required><button class="password-toggle" type="button" data-toggle-password="loginPassword"><i class="bi bi-eye"></i></button></div>
                <button class="btn btn-primary btn-lg w-100 mt-4 login-submit" type="submit">Masuk Dashboard <i class="bi bi-arrow-right ms-2"></i></button>
            </form>
            <div class="login-help"><i class="bi bi-shield-lock"></i><span>Admin dan Kasir masuk ke panel operasional. Pelanggan masuk ke halaman akun pelanggan.</span></div>
            <div class="demo-login-box">
                <div class="demo-login-title"><i class="bi bi-key"></i> AKUN DEMO UNTUK PENGUJIAN</div>
                <div class="demo-login-row"><span><b>Admin</b><small>admin</small></span><code>AdminWM2026!</code></div>
                <div class="demo-login-row"><span><b>Kasir</b><small>kasir</small></span><code>KasirWM2026!</code></div>
                <div class="demo-login-row"><span><b>Pelanggan / User</b><small>userdemo</small></span><code>UserWM2026!</code></div>
                <small class="demo-login-note">Akun demo hanya untuk uji lokal. Ganti password sebelum aplikasi digunakan sungguhan.</small>
            </div>
            <div class="login-local-note"><i class="bi bi-pc-display"></i> Aplikasi lokal · XAMPP MariaDB</div>
        </div>
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script><script src="<?= e(BASE_URL) ?>/assets/js/app.js?v=5"></script>
</body></html>

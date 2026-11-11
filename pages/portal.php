<?php
$loggedUser = Auth::user();
$profileStmt = $db->prepare("SELECT p.* FROM tbl_pelanggan p WHERE p.id_pengguna=? LIMIT 1");
$profileStmt->execute([(int)$loggedUser['id_pengguna']]);
$profile = $profileStmt->fetch();
$balances = ['tabungan'=>0.0, 'ewallet'=>0.0];
$transactions = [];
if ($profile) {
    $balanceStmt = $db->prepare('SELECT jenis_saldo,saldo FROM tbl_saldo_pelanggan WHERE id_pelanggan=?');
    $balanceStmt->execute([(int)$profile['id_pelanggan']]);
    foreach ($balanceStmt->fetchAll() as $balance) $balances[$balance['jenis_saldo']] = (float)$balance['saldo'];
    $trxStmt = $db->prepare('SELECT id_transaksi,kode_transaksi,tanggal_transaksi,total_belanja,status_pembayaran,status_pesanan FROM tbl_transaksi WHERE id_pelanggan=? ORDER BY id_transaksi DESC LIMIT 8');
    $trxStmt->execute([(int)$profile['id_pelanggan']]);
    $transactions = $trxStmt->fetchAll();
}
function portal_money($value): string { return 'Rp ' . number_format((float)$value, 0, ',', '.'); }
?>
<!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Akun Pelanggan · WarungMadura</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap');
:root{--green:#176341;--deep:#123e2b;--muted:#718078;--line:#e5ece7;--bg:#f5f8f5}*{box-sizing:border-box}body{margin:0;background:var(--bg);font-family:Lexend,system-ui,-apple-system,"Segoe UI",sans-serif;color:#20342a}.top{background:#fff;border-bottom:1px solid var(--line);padding:16px max(22px,calc((100% - 1080px)/2));display:flex;align-items:center;justify-content:space-between;gap:18px}.logo{font-weight:850;font-size:25.2px;color:var(--deep);text-decoration:none}.logo span{color:#4e9b70}.pill{background:#eaf5ee;color:#176341;border-radius:30px;padding:7px 12px;font-size:14.4px;font-weight:700}.wrap{max-width:1080px;margin:34px auto;padding:0 20px}.welcome{margin-bottom:24px}.welcome small{color:var(--green);font-weight:800;letter-spacing:.12em}.welcome h1{font-size:clamp(31.2px,4.8vw,43.2px);font-weight:800;margin:8px 0}.welcome p{color:var(--muted);margin:0}.cardbox{background:white;border:1px solid var(--line);border-radius:18px;padding:22px;height:100%;box-shadow:0 8px 24px rgba(20,60,38,.035)}.balance-label{color:var(--muted);font-size:15.6px}.balance-value{font-size:30px;font-weight:850;color:var(--deep);margin-top:8px}.mini-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:13px;background:#eaf5ee;color:var(--green);font-size:22.8px}.profile-line{display:flex;justify-content:space-between;gap:16px;padding:11px 0;border-bottom:1px solid var(--line)}.profile-line:last-child{border:0}.profile-line span{color:var(--muted)}.table{font-size:16.8px}.table th{color:#68786e;font-size:13.2px;text-transform:uppercase;letter-spacing:.05em}.muted{color:var(--muted)}.btn-main{background:var(--green);border-color:var(--green);color:white}.btn-main:hover{background:var(--deep);color:white}.empty{padding:30px;text-align:center;color:var(--muted)}footer{margin:34px 0 12px;text-align:center;color:#87958c;font-size:14.4px}@media(max-width:560px){.top{padding:14px 16px}.wrap{margin:24px auto;padding:0 14px}.cardbox{padding:18px}.profile-line{font-size:15.6px}}
</style></head><body>
<header class="top"><a class="logo" href="<?=e(url('portal'))?>">Warung<span>Madura</span></a><div class="d-flex align-items-center gap-3"><span class="pill"><i class="bi bi-person-check me-1"></i> Akun Pelanggan</span><a class="btn btn-outline-secondary btn-sm" href="<?=e(url('logout'))?>"><i class="bi bi-box-arrow-right me-1"></i>Keluar</a></div></header>
<main class="wrap">
<div class="welcome"><small>PORTAL MEMBER</small><h1>Halo, <?=e($profile['nama_pelanggan'] ?? $loggedUser['nama_lengkap'])?>!</h1><p>Ini halaman akun pelanggan WarungMadura. Data akun dan saldo simulasi ditampilkan di sini.</p></div>
<?php if(!$profile):?><div class="alert alert-warning">Akun ini belum terhubung ke profil pelanggan. Silakan hubungi Admin WarungMadura untuk menghubungkan nomor member.</div><?php else:?>
<div class="row g-3 mb-3"><div class="col-md-6"><div class="cardbox"><div class="d-flex justify-content-between"><div><div class="balance-label">Saldo Tabungan (simulasi)</div><div class="balance-value"><?=portal_money($balances['tabungan'])?></div></div><div class="mini-icon"><i class="bi bi-piggy-bank"></i></div></div></div></div><div class="col-md-6"><div class="cardbox"><div class="d-flex justify-content-between"><div><div class="balance-label">Saldo E-Wallet (simulasi)</div><div class="balance-value"><?=portal_money($balances['ewallet'])?></div></div><div class="mini-icon"><i class="bi bi-wallet2"></i></div></div></div></div></div>
<div class="row g-3"><div class="col-lg-5"><div class="cardbox"><h5 class="fw-bold mb-3">Profil Member</h5><div class="profile-line"><span>Nomor Member</span><b><?=e($profile['no_member'])?></b></div><div class="profile-line"><span>Nama</span><b><?=e($profile['nama_pelanggan'])?></b></div><div class="profile-line"><span>Username</span><b><?=e($loggedUser['username'])?></b></div><div class="profile-line"><span>Nomor HP</span><b><?=e($profile['no_hp']?:'—')?></b></div><div class="profile-line"><span>Status</span><b><?=e(ucfirst($profile['status_member']))?></b></div><p class="muted small mt-3 mb-0">Untuk saat ini portal menampilkan profil, saldo, dan riwayat transaksi. Fitur belanja online akan disambungkan pada modul pelanggan berikutnya.</p></div></div>
<div class="col-lg-7"><div class="cardbox"><div class="d-flex align-items-center justify-content-between mb-3"><h5 class="fw-bold mb-0">Riwayat Transaksi</h5><span class="pill">8 transaksi terbaru</span></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nomor</th><th>Tanggal</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody><?php if(!$transactions):?><tr><td colspan="5" class="empty">Belum ada transaksi.</td></tr><?php endif;?><?php foreach($transactions as $trx):?><tr><td><b><?=e($trx['kode_transaksi'])?></b></td><td><?=e(date('d/m/Y',strtotime($trx['tanggal_transaksi'])))?></td><td><?=portal_money($trx['total_belanja'])?></td><td><?=e(ucwords(str_replace('_',' ',$trx['status_pembayaran'])))?></td><td><a class="btn btn-sm btn-light" href="<?=e(url('receipt',['id'=>$trx['id_transaksi']]))?>" title="Lihat nota"><i class="bi bi-receipt"></i></a></td></tr><?php endforeach;?></tbody></table></div></div></div></div>
<?php endif;?>
<footer>© <?=date('Y')?> WarungMadura · <?=e(APP_TAGLINE)?></footer>
</main></body></html>

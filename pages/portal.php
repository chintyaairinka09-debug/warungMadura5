<?php
$loggedUser = Auth::user();
$profileStmt = $db->prepare("SELECT p.* FROM tbl_pelanggan p WHERE p.id_pengguna=? LIMIT 1");
$profileStmt->execute([(int)$loggedUser['id_pengguna']]);
$profile = $profileStmt->fetch();
$balances = ['tabungan'=>0.0, 'ewallet'=>0.0];
$transactions = [];
$flashMessage = take_flash();
$products = $db->query("SELECT id_produk,nama_produk,sku,stok,satuan,harga_jual,gambar FROM tbl_produk WHERE status_produk='aktif' AND stok>0 ORDER BY nama_produk")->fetchAll();
$qrisPointer = __DIR__ . '/../uploads/qris/current.txt';
$qrisFilename = is_file($qrisPointer) ? trim((string)file_get_contents($qrisPointer)) : '';
$qrisDirectory = __DIR__ . '/../uploads/qris';
$qrisImage = preg_match('/\Adana-qris\.(?:jpg|png|webp)\z/i',$qrisFilename) && is_file($qrisDirectory . '/' . $qrisFilename)
    ? BASE_URL . '/uploads/qris/' . $qrisFilename
    : null;
if ($profile) {
    $balanceStmt = $db->prepare('SELECT jenis_saldo,saldo FROM tbl_saldo_pelanggan WHERE id_pelanggan=?');
    $balanceStmt->execute([(int)$profile['id_pelanggan']]);
    foreach ($balanceStmt->fetchAll() as $balance) $balances[$balance['jenis_saldo']] = (float)$balance['saldo'];
    $trxStmt = $db->prepare('SELECT t.id_transaksi,t.kode_transaksi,t.tanggal_transaksi,t.total_belanja,t.status_pembayaran,t.status_pesanan,t.metode_pembayaran,p.bukti_pembayaran FROM tbl_transaksi t LEFT JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi WHERE t.id_pelanggan=? ORDER BY t.id_transaksi DESC LIMIT 8');
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
<link rel="stylesheet" href="<?=e(BASE_URL)?>/assets/css/app.css?v=13">
<style>@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap');
:root{--green:#18784a;--deep:#105936;--orange:#ef8a24;--muted:#74766b;--line:#e7eadf;--bg:#f6f8ef}*{box-sizing:border-box}body{margin:0;background:var(--bg);font-family:Lexend,system-ui,-apple-system,"Segoe UI",sans-serif;color:#20342a}.top{background:#fff;border-bottom:1px solid var(--line);padding:16px max(22px,calc((100% - 1080px)/2));display:flex;align-items:center;justify-content:space-between;gap:18px}.logo{font-weight:850;font-size:25.2px;color:var(--deep);text-decoration:none}.logo span{color:#ef8a24}.pill{background:#def3e7;color:#105936;border-radius:30px;padding:7px 12px;font-size:14.4px;font-weight:700}.wrap{max-width:1080px;margin:34px auto;padding:0 20px}.welcome{margin-bottom:24px}.welcome small{color:var(--green);font-weight:800;letter-spacing:.12em}.welcome h1{font-size:clamp(31.2px,4.8vw,43.2px);font-weight:800;margin:8px 0}.welcome p{color:var(--muted);margin:0}.cardbox{background:white;border:1px solid var(--line);border-radius:18px;padding:22px;height:100%;box-shadow:0 8px 24px rgba(20,60,38,.035)}.balance-label{color:var(--muted);font-size:15.6px}.balance-value{font-size:30px;font-weight:850;color:var(--deep);margin-top:8px}.mini-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:13px;background:#fff1bb;color:var(--green);font-size:22.8px}.profile-line{display:flex;justify-content:space-between;gap:16px;padding:11px 0;border-bottom:1px solid var(--line)}.profile-line:last-child{border:0}.profile-line span{color:var(--muted)}.table{font-size:16.8px}.table th{color:#68786e;font-size:13.2px;text-transform:uppercase;letter-spacing:.05em}.muted{color:var(--muted)}.btn-main{background:var(--green);border-color:var(--green);color:white}.btn-main:hover{background:var(--deep);color:white}.empty{padding:30px;text-align:center;color:var(--muted)}footer{margin:34px 0 12px;text-align:center;color:#87958c;font-size:14.4px}@media(max-width:560px){.top{padding:14px 16px}.wrap{margin:24px auto;padding:0 14px}.cardbox{padding:18px}.profile-line{font-size:15.6px}}
</style></head><body>
<header class="top"><a class="logo" href="<?=e(url('portal'))?>"><img src="<?=e(BASE_URL)?>/assets/images/copy.png" alt="Logo WarungMadura" style="height:58px;width:58px;object-fit:cover;border-radius:14px;box-shadow:0 6px 14px rgba(207,104,18,.18)"><span class="ms-2">Warung<span>Madura</span></span></a><div class="d-flex align-items-center gap-3"><span class="pill"><i class="bi bi-person-check me-1"></i> Akun Pelanggan</span><a class="btn btn-outline-secondary btn-sm" href="<?=e(url('logout'))?>"><i class="bi bi-box-arrow-right me-1"></i>Keluar</a></div></header>
<main class="wrap">
<div class="welcome"><small>PORTAL MEMBER</small><h1>Halo, <?=e($profile['nama_pelanggan'] ?? $loggedUser['nama_lengkap'])?>!</h1><p>Belanja dengan QRIS dan pantau status pesanan serta bukti pembayaranmu di sini.</p></div>
<?php if($flashMessage): ?><div class="alert alert-<?=e($flashMessage['type'])?>"><?=e($flashMessage['message'])?></div><?php endif; ?>
<?php if(!$profile):?><div class="alert alert-warning">Akun ini belum terhubung ke profil pelanggan. Silakan hubungi Admin WarungMadura untuk menghubungkan nomor member.</div><?php else:?>
<div class="row g-3 mb-3"><div class="col-md-6"><div class="cardbox"><div class="d-flex justify-content-between"><div><div class="balance-label">Saldo Tabungan (simulasi)</div><div class="balance-value"><?=portal_money($balances['tabungan'])?></div></div><div class="mini-icon"><i class="bi bi-piggy-bank"></i></div></div></div></div><div class="col-md-6"><div class="cardbox"><div class="d-flex justify-content-between"><div><div class="balance-label">Saldo E-Wallet (simulasi)</div><div class="balance-value"><?=portal_money($balances['ewallet'])?></div></div><div class="mini-icon"><i class="bi bi-wallet2"></i></div></div></div></div></div>
<section class="shop-section mb-4">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3"><div><h2 class="h4 fw-bold mb-1">Belanja Produk</h2><p class="muted mb-0">Pilih produk yang tersedia, lalu checkout untuk ambil di toko.</p></div><span class="pill">Stok diperbarui langsung</span></div>
    <div class="row g-3">
        <div class="col-lg-8"><div class="shop-grid">
            <?php foreach($products as $product): $image = product_image_src($product['gambar']); ?>
            <article class="shop-card">
                <div class="shop-product-image"><?php if($image): ?><img src="<?=e($image)?>" alt="<?=e($product['nama_produk'])?>" loading="lazy"><?php else: ?><i class="bi bi-basket2"></i><?php endif; ?></div>
                <div class="shop-product-body"><b><?=e($product['nama_produk'])?></b><small><?=e($product['sku'])?> · Stok <?= (int)$product['stok']?> <?=e($product['satuan'])?></small><strong><?=portal_money($product['harga_jual'])?></strong><button class="btn btn-sm btn-main add-to-cart" type="button" data-id="<?= (int)$product['id_produk']?>" data-name="<?=e($product['nama_produk'])?>" data-price="<?= (float)$product['harga_jual']?>" data-stock="<?= (int)$product['stok']?>"><i class="bi bi-plus-lg me-1"></i>Tambah</button></div>
            </article>
            <?php endforeach; ?>
            <?php if(!$products): ?><div class="cardbox w-100 empty">Belum ada produk dengan stok tersedia.</div><?php endif; ?>
        </div></div>
        <div class="col-lg-4"><div class="cardbox cart-box"><h5 class="fw-bold">Keranjang</h5><div id="userCartItems" class="user-cart-items"><p class="empty mb-0">Keranjang masih kosong.</p></div><div class="cart-total-line"><span>Total</span><b id="userCartTotal"><?=portal_money(0)?></b></div>
        <?php if($qrisImage):?><div class="dana-qris-panel mb-3"><b><i class="bi bi-qr-code me-1"></i> QRIS Pembayaran</b><img class="dana-qris-image my-2" src="<?=e($qrisImage)?>" alt="QRIS WarungMadura"><small class="d-block">Bayar tepat sebesar total pesanan. Setelah checkout, unggah screenshot bukti pembayaran pada riwayat transaksi.</small></div><?php else:?><div class="alert alert-warning small">QRIS toko belum tersedia. Silakan hubungi Admin.</div><?php endif;?>
        <form method="post" id="userCheckoutForm"><?=Csrf::field()?><input type="hidden" name="action" value="customer_checkout"><input type="hidden" name="_return" value="portal"><input type="hidden" name="cart_json" id="userCartJson" value="[]"><p class="muted small">Pesanan menunggu verifikasi pembayaran Admin/Kasir. Stok ditahan saat checkout dan dikembalikan jika pesanan dibatalkan.</p><button class="btn btn-main w-100" id="userCheckoutButton" disabled><i class="bi bi-bag-check me-2"></i>Buat Pesanan QRIS</button></form></div></div>
    </div>
</section>
<div class="row g-3"><div class="col-lg-5"><div class="cardbox"><h5 class="fw-bold mb-3">Profil Member</h5><div class="profile-line"><span>Nomor Member</span><b><?=e($profile['no_member'])?></b></div><div class="profile-line"><span>Nama</span><b><?=e($profile['nama_pelanggan'])?></b></div><div class="profile-line"><span>Username</span><b><?=e($loggedUser['username'])?></b></div><div class="profile-line"><span>Nomor HP</span><b><?=e($profile['no_hp']?:'—')?></b></div><div class="profile-line"><span>Status</span><b><?=e(ucfirst($profile['status_member']))?></b></div><p class="muted small mt-3 mb-0">Saldo bersifat simulasi. Pembelian dari katalog akan dicatat sebagai pesanan online.</p></div></div>
<div class="col-lg-7"><div class="cardbox"><div class="d-flex align-items-center justify-content-between mb-3"><h5 class="fw-bold mb-0">Riwayat Transaksi</h5><span class="pill">8 transaksi terbaru</span></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nomor / tanggal</th><th>Total</th><th>Status &amp; bukti</th><th>Nota</th></tr></thead><tbody><?php if(!$transactions):?><tr><td colspan="4" class="empty">Belum ada transaksi.</td></tr><?php endif;?><?php foreach($transactions as $trx):?><tr><td><b><?=e($trx['kode_transaksi'])?></b><br><small class="muted"><?=e(date('d/m/Y H:i',strtotime($trx['tanggal_transaksi'])))?></small></td><td><?=portal_money($trx['total_belanja'])?></td><td>
<?php if($trx['metode_pembayaran']==='qris_dana' && $trx['status_pembayaran']==='menunggu' && !$trx['bukti_pembayaran']):?>
<span class="badge text-bg-warning">Menunggu pembayaran</span><form method="post" enctype="multipart/form-data" class="mt-2"><?=Csrf::field()?><input type="hidden" name="action" value="customer_upload_payment_proof"><input type="hidden" name="_return" value="portal"><input type="hidden" name="id_transaksi" value="<?=(int)$trx['id_transaksi']?>"><input class="form-control form-control-sm mb-2" type="file" name="bukti_pembayaran" accept="image/jpeg,image/png,image/webp" required><small class="muted d-block mb-2">Screenshot JPG, PNG, atau WebP (maks. 5 MB).</small><button class="btn btn-sm btn-main"><i class="bi bi-upload me-1"></i>Unggah bukti</button></form><form method="post" class="mt-2" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.');"><?=Csrf::field()?><input type="hidden" name="action" value="customer_cancel_order"><input type="hidden" name="_return" value="portal"><input type="hidden" name="id_transaksi" value="<?=(int)$trx['id_transaksi']?>"><button class="btn btn-sm btn-outline-danger">Batalkan pesanan</button></form>
<?php elseif($trx['metode_pembayaran']==='qris_dana' && $trx['status_pembayaran']==='menunggu' && $trx['bukti_pembayaran']):?><span class="badge text-bg-info">Menunggu verifikasi</span><br><a class="btn btn-sm btn-light mt-2" href="<?=e(url('payment_proof',['id'=>$trx['id_transaksi']]))?>" target="_blank" rel="noopener"><i class="bi bi-image me-1"></i>Lihat bukti</a>
<?php elseif($trx['status_pembayaran']==='gagal'):?><span class="badge text-bg-danger">Ditolak / dibatalkan</span><br><small class="muted">Silakan buat pesanan baru bila masih ingin berbelanja.</small>
<?php else:?><?=status_badge($trx['status_pembayaran'])?><?php endif;?>
</td><td><a class="btn btn-sm btn-light" href="<?=e(url('receipt',['id'=>$trx['id_transaksi']]))?>" title="Lihat nota"><i class="bi bi-receipt"></i></a></td></tr><?php endforeach;?></tbody></table></div></div></div></div>
<?php endif;?>
<footer>© <?=date('Y')?> WarungMadura · <?=e(APP_TAGLINE)?></footer>
<script>
(function () {
    var cart = {};
    var list = document.getElementById('userCartItems');
    var totalNode = document.getElementById('userCartTotal');
    var cartJson = document.getElementById('userCartJson');
    var checkoutButton = document.getElementById('userCheckoutButton');
    var form = document.getElementById('userCheckoutForm');
    var formatter = new Intl.NumberFormat('id-ID');
    var qrisReady = <?= $qrisImage ? 'true' : 'false' ?>;

    function renderCart() {
        list.replaceChildren();
        var items = Object.keys(cart).map(function (id) { return cart[id]; });
        var total = 0;
        if (!items.length) {
            var empty = document.createElement('p');
            empty.className = 'empty mb-0';
            empty.textContent = 'Keranjang masih kosong.';
            list.appendChild(empty);
        }
        items.forEach(function (item) {
            total += item.price * item.qty;
            var row = document.createElement('div');
            row.className = 'user-cart-row';
            var name = document.createElement('b');
            name.textContent = item.name;
            var detail = document.createElement('span');
            detail.textContent = item.qty + ' x Rp ' + formatter.format(item.price);
            var controls = document.createElement('div');
            controls.className = 'user-cart-controls';
            [['−',-1],['+',1]].forEach(function (control) {
                var button = document.createElement('button');
                button.type = 'button';
                button.textContent = control[0];
                button.disabled = control[1] > 0 && item.qty >= item.stock;
                button.addEventListener('click', function () {
                    item.qty += control[1];
                    if (item.qty < 1) delete cart[item.id];
                    renderCart();
                });
                controls.appendChild(button);
            });
            row.append(name, detail, controls);
            list.appendChild(row);
        });
        totalNode.textContent = 'Rp ' + formatter.format(total);
        cartJson.value = JSON.stringify(items.map(function (item) { return {id:item.id,qty:item.qty}; }));
        checkoutButton.disabled = !items.length || !qrisReady;
    }

    document.querySelectorAll('.add-to-cart').forEach(function (button) {
        button.addEventListener('click', function () {
            var id = button.dataset.id;
            if (!cart[id]) cart[id] = {id:id,name:button.dataset.name,price:Number(button.dataset.price),stock:Number(button.dataset.stock),qty:0};
            if (cart[id].qty < cart[id].stock) cart[id].qty++;
            renderCart();
        });
    });
    form.addEventListener('submit', function (event) {
        if (!Object.keys(cart).length) event.preventDefault();
    });
})();
</script>
</main></body></html>

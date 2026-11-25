<?php
$payments = $db->query("SELECT t.id_transaksi,t.kode_transaksi,t.tanggal_transaksi,t.total_belanja,p.bukti_pembayaran,c.nama_pelanggan,c.no_hp
    FROM tbl_transaksi t
    JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi
    JOIN tbl_pelanggan c ON c.id_pelanggan=t.id_pelanggan
    WHERE t.jenis_transaksi='online' AND t.metode_pembayaran='qris_dana'
      AND t.status_pesanan='menunggu' AND t.status_pembayaran='menunggu'
      AND p.status_pembayaran='menunggu' AND p.bukti_pembayaran IS NOT NULL
    ORDER BY t.tanggal_transaksi ASC")->fetchAll();
?>
<div class="page-heading">
    <div><span class="section-kicker">PEMBAYARAN ONLINE</span><h1>Verifikasi QRIS</h1><p>Periksa bukti transfer sebelum menyetujui pesanan. Bukti hanya dapat dilihat oleh Admin dan Kasir.</p></div>
    <span class="badge text-bg-warning"><?=count($payments)?> menunggu verifikasi</span>
</div>

<?php if (!$payments): ?>
<div class="panel-card empty-state"><i class="bi bi-check2-circle me-2"></i>Belum ada bukti pembayaran yang menunggu verifikasi.</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($payments as $payment): ?>
    <div class="col-xl-6">
        <article class="panel-card h-100">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div><span class="section-kicker"><?=e($payment['kode_transaksi'])?></span><h5 class="mb-1"><?=e($payment['nama_pelanggan'])?></h5><small class="text-muted"><?=e(date('d/m/Y H:i',strtotime($payment['tanggal_transaksi'])))?><?php if($payment['no_hp']):?> · <?=e($payment['no_hp'])?><?php endif;?></small></div>
                <b class="text-nowrap"><?=money($payment['total_belanja'])?></b>
            </div>
            <a href="<?=e(url('payment_proof',['id'=>$payment['id_transaksi']]))?>" target="_blank" rel="noopener" class="d-block text-center rounded mb-3 bg-light p-2" aria-label="Buka bukti pembayaran <?=e($payment['kode_transaksi'])?>">
                <img class="payment-proof-preview" src="<?=e(url('payment_proof',['id'=>$payment['id_transaksi']]))?>" alt="Bukti pembayaran <?=e($payment['kode_transaksi'])?>">
            </a>
            <div class="d-flex flex-wrap gap-2">
                <form method="post" class="flex-grow-1"><?=Csrf::field()?><input type="hidden" name="action" value="approve_customer_payment"><input type="hidden" name="_return" value="payments"><input type="hidden" name="id_transaksi" value="<?=(int)$payment['id_transaksi']?>"><button class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i>Setujui &amp; proses</button></form>
                <form method="post" class="flex-grow-1" onsubmit="return confirm('Tolak bukti ini, batalkan pesanan, dan kembalikan stok?');"><?=Csrf::field()?><input type="hidden" name="action" value="reject_customer_payment"><input type="hidden" name="_return" value="payments"><input type="hidden" name="id_transaksi" value="<?=(int)$payment['id_transaksi']?>"><button class="btn btn-outline-danger w-100"><i class="bi bi-x-lg me-1"></i>Tolak &amp; batalkan</button></form>
            </div>
        </article>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

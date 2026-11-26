<?php
$payments = $db->query("SELECT t.id_transaksi,t.kode_transaksi,t.tanggal_transaksi,t.total_belanja,p.bukti_pembayaran,c.nama_pelanggan,c.no_hp
    FROM tbl_transaksi t
    JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi
    JOIN tbl_pelanggan c ON c.id_pelanggan=t.id_pelanggan
    WHERE t.jenis_transaksi='online' AND t.metode_pembayaran='qris_dana'
      AND t.status_pesanan='menunggu' AND t.status_pembayaran='menunggu'
      AND p.status_pembayaran='menunggu' AND p.bukti_pembayaran IS NOT NULL
    ORDER BY t.tanggal_transaksi ASC")->fetchAll();
$duitkuPayments = $db->query("SELECT t.id_transaksi,t.kode_transaksi,t.tanggal_transaksi,t.total_belanja,t.catatan,c.nama_pelanggan,c.no_hp,p.referensi_simulasi,p.url_pembayaran,p.va_number,p.kode_metode_gateway
    FROM tbl_transaksi t
    JOIN tbl_pembayaran p ON p.id_transaksi=t.id_transaksi
    JOIN tbl_pelanggan c ON c.id_pelanggan=t.id_pelanggan
    WHERE t.jenis_transaksi='online' AND t.metode_pembayaran='duitku'
      AND t.status_pesanan='menunggu' AND t.status_pembayaran='menunggu' AND p.status_pembayaran='menunggu'
    ORDER BY t.tanggal_transaksi ASC")->fetchAll();
?>
<div class="page-heading">
    <div><span class="section-kicker">PEMBAYARAN ONLINE</span><h1>Pembayaran Online</h1><p>Periksa invoice Duitku yang bermasalah dan verifikasi bukti pembayaran QRIS pelanggan.</p></div>
    <span class="badge text-bg-warning"><?=count($payments)?> menunggu verifikasi</span>
</div>

<section class="mb-4" id="duitku">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <div><h2 class="h5 mb-1">Pembayaran Duitku</h2><p class="text-muted mb-0">Admin/Kasir dapat membuka invoice dan mengecek status langsung ke Duitku. Untuk sandbox VA, klik Simulasi VA, masukkan nomor VA, lalu tekan Cek status.</p></div>
        <span class="badge text-bg-warning"><?=count($duitkuPayments)?> menunggu</span>
    </div>
    <?php if (!$duitkuPayments): ?>
    <div class="panel-card empty-state">Tidak ada pembayaran Duitku yang menunggu.</div>
    <?php else: ?>
    <div class="panel-card table-responsive"><table class="table app-table align-middle mb-0"><thead><tr><th>Pesanan / waktu</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Tindakan</th></tr></thead><tbody>
    <?php foreach ($duitkuPayments as $payment): ?>
    <tr>
        <td><b><?=e($payment['kode_transaksi'])?></b><br><small class="text-muted"><?=e(date('d/m/Y H:i',strtotime($payment['tanggal_transaksi'])))?></small></td>
        <td><?=e($payment['nama_pelanggan'])?><?php if($payment['no_hp']):?><br><small class="text-muted"><?=e($payment['no_hp'])?></small><?php endif;?></td>
        <td><?=money($payment['total_belanja'])?></td>
        <td><?php if($payment['url_pembayaran']):?><span class="badge text-bg-info">Menunggu pelanggan</span><br><small><?=e($payment['kode_metode_gateway'] ?: 'Duitku')?></small><?php if($payment['va_number']):?><br><small>VA: <b class="user-select-all"><?=e($payment['va_number'])?></b></small><?php endif;?><?php elseif($payment['referensi_simulasi']):?><span class="badge text-bg-warning">Invoice terdeteksi</span><br><small>Ref: <?=e($payment['referensi_simulasi'])?></small><?php else:?><span class="badge text-bg-warning">Invoice belum terbentuk</span><?php endif;?></td>
        <td class="text-nowrap">
            <?php if($payment['url_pembayaran']):?><a class="btn btn-sm btn-primary" href="<?=e($payment['url_pembayaran'])?>" target="_blank" rel="noopener">Buka invoice</a><?php endif;?>
            <?php if(defined('DUITKU_SANDBOX') && DUITKU_SANDBOX && $payment['va_number']):?><a class="btn btn-sm btn-outline-warning" href="https://sandbox.duitku.com/payment/demo/demosuccesstransaction.aspx" target="_blank" rel="noopener">Simulasi VA</a><?php endif;?>
            <form method="post" class="d-inline"><?=Csrf::field()?><input type="hidden" name="action" value="check_duitku_status"><input type="hidden" name="_return" value="payments"><input type="hidden" name="id_transaksi" value="<?=(int)$payment['id_transaksi']?>"><button class="btn btn-sm btn-outline-primary">Cek status</button></form>
            <?php if(!$payment['url_pembayaran'] && !$payment['referensi_simulasi']):?><form method="post" class="d-inline" onsubmit="return confirm('Pastikan Cek status menunjukkan invoice tidak ditemukan, lalu buat invoice ulang?');"><?=Csrf::field()?><input type="hidden" name="action" value="retry_duitku_invoice"><input type="hidden" name="_return" value="payments"><input type="hidden" name="id_transaksi" value="<?=(int)$payment['id_transaksi']?>"><button class="btn btn-sm btn-warning">Buat ulang</button></form><?php endif;?>
            <a class="btn btn-sm btn-outline-secondary" href="<?=e(url('receipt',['id'=>$payment['id_transaksi']]))?>">Detail</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody></table></div>
    <?php endif; ?>
</section>

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

<?php
if (!Auth::check()) redirect('login');
$id=(int)($_GET['id']??0);
$stmt=$db->prepare("SELECT t.*,COALESCE(p.nama_pelanggan,'Pelanggan Umum') nama_pelanggan,p.no_member,p.no_hp,u.nama_lengkap nama_kasir FROM tbl_transaksi t LEFT JOIN tbl_pelanggan p ON p.id_pelanggan=t.id_pelanggan LEFT JOIN tbl_pengguna u ON u.id_pengguna=t.id_kasir WHERE t.id_transaksi=? LIMIT 1");$stmt->execute([$id]);$trx=$stmt->fetch();
if(!$trx){http_response_code(404);exit('Nota tidak ditemukan.');}
if ((Auth::user()['role'] ?? '') === 'pelanggan') {
    $owner = $db->prepare('SELECT id_pelanggan FROM tbl_pelanggan WHERE id_pengguna=? LIMIT 1');
    $owner->execute([(int)Auth::user()['id_pengguna']]);
    $ownerId = $owner->fetchColumn();
    if (!$ownerId || (int)$ownerId !== (int)$trx['id_pelanggan']) { http_response_code(403); exit('Kamu tidak memiliki akses ke nota ini.'); }
}
$backUrl = (Auth::user()['role'] ?? '') === 'pelanggan' ? url('portal') : url('reports');
$itemsStmt=$db->prepare('SELECT * FROM tbl_detail_transaksi WHERE id_transaksi=? ORDER BY id_detail');$itemsStmt->execute([$id]);$items=$itemsStmt->fetchAll();
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nota <?=e($trx['kode_transaksi'])?> · WarungMadura</title><style>@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap');
*{box-sizing:border-box}body{margin:0;background:#f7f3e8;color:#2c2e24;font-family:Lexend,sans-serif}.receipt-actions{max-width:390px;margin:18px auto;display:flex;gap:8px}.receipt-actions a,.receipt-actions button{border:0;border-radius:8px;padding:10px 14px;background:#414b2d;color:#fffdf7;text-decoration:none;font-size:15.6px;cursor:pointer}.receipt-actions a{background:#f1ead8;color:#2d3521;border:1px solid #e8e1cf}.receipt{background:#fffdf7;width:77mm;min-height:297mm;margin:0 auto 30px;padding:4mm 3mm;font-size:12px;line-height:1.4}.center{text-align:center}.brand{font-size:20.4px;font-weight:800;letter-spacing:.2px;color:#343d25}.tagline{font-size:10.8px;margin-top:2px}.rule{border:0;border-top:1px dashed #777766;margin:7px 0}.meta{display:flex;justify-content:space-between;gap:6px}.meta span:last-child{text-align:right}.item{margin:6px 0}.item-name{font-weight:700;overflow-wrap:anywhere}.item-line{display:flex;justify-content:space-between;gap:6px}.transaction-barcode{display:block;width:58mm;height:18mm;margin:5px auto 2px}.total-line{display:flex;justify-content:space-between;margin:3px 0}.grand{font-weight:800;font-size:14.4px}.thanks{margin-top:10px;font-size:12px}.muted{color:#555548}.receipt-footer{text-align:center;margin-top:10px;font-size:10.8px}@media print{@page{size:77mm 297mm;margin:0}body{background:#fff}.receipt-actions{display:none}.receipt{width:77mm;min-height:297mm;margin:0;padding:3mm;box-shadow:none;page-break-after:avoid}.transaction-barcode{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style><script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.12.1/dist/JsBarcode.all.min.js"></script></head><body>
<div class="receipt-actions"><a href="<?=e($backUrl)?>">← Kembali</a><button onclick="window.print()">Cetak Nota 77 × 297 mm</button></div>
<div class="receipt"><div class="center"><div class="brand">WarungMadura</div><div class="tagline"><?=e(APP_TAGLINE)?></div><div class="muted">Terima kasih sudah berbelanja</div></div><hr class="rule"><div class="meta"><span>No. Transaksi</span><span><?=e($trx['kode_transaksi'])?></span></div><div class="meta"><span>Tanggal</span><span><?=e(date('d/m/Y H:i',strtotime($trx['tanggal_transaksi'])))?></span></div><div class="meta"><span>Kasir</span><span><?=e($trx['nama_kasir']?:'-')?></span></div><div class="meta"><span>Pelanggan</span><span><?=e($trx['nama_pelanggan'])?></span></div><?php if($trx['no_member']):?><div class="meta"><span>Member</span><span><?=e($trx['no_member'])?></span></div><?php endif;?><hr class="rule">
<?php foreach($items as $item):?><div class="item"><div class="item-name"><?=e($item['nama_produk'])?></div><div class="item-line"><span><?= (int)$item['jumlah']?> x <?=number_format((float)$item['harga_satuan'],0,',','.')?></span><span><?=number_format((float)$item['subtotal_item'],0,',','.')?></span></div></div><?php endforeach;?>
<hr class="rule"><div class="total-line"><span>Subtotal</span><span><?=number_format((float)$trx['subtotal'],0,',','.')?></span></div><?php if((float)$trx['diskon']>0):?><div class="total-line"><span>Diskon</span><span>-<?=number_format((float)$trx['diskon'],0,',','.')?></span></div><?php endif;?><div class="total-line grand"><span>TOTAL</span><span><?=number_format((float)$trx['total_belanja'],0,',','.')?></span></div><div class="total-line"><span>Metode</span><span><?=e(($trx['metode_pembayaran'] ?? '') === 'qris_dana' ? 'QRIS DANA' : strtoupper($trx['metode_pembayaran']?:'-'))?></span></div><div class="total-line"><span>Bayar</span><span><?=number_format((float)$trx['jumlah_bayar'],0,',','.')?></span></div><?php if((float)$trx['kembalian']>0):?><div class="total-line"><span>Kembalian</span><span><?=number_format((float)$trx['kembalian'],0,',','.')?></span></div><?php endif;?><hr class="rule"><div class="center"><svg class="transaction-barcode" data-transaction-barcode="<?=e($trx['kode_transaksi'])?>" aria-label="Barcode transaksi <?=e($trx['kode_transaksi'])?>"></svg></div><div class="center thanks"><b>Terima kasih!</b><br>Semoga hari Anda menyenangkan.</div><div class="receipt-footer">Struk ini merupakan bukti transaksi<br>WarungMadura · Sistem Lokal</div></div>
<script>
function renderBarcode(element, code, height) {
  if (!window.JsBarcode || !code) return;
  JsBarcode(element, code, {format:'CODE128',width:1,height:height,displayValue:true,fontSize:9,margin:2,textMargin:2});
}
window.addEventListener('load', function () {
  document.querySelectorAll('[data-transaction-barcode]').forEach(function (element) {
    renderBarcode(element, element.dataset.transactionBarcode, 42);
  });
});
</script>
</body></html>

<?php
$kpi = [
    'products' => (int)$db->query("SELECT COUNT(*) FROM tbl_produk WHERE status_produk='aktif'")->fetchColumn(),
    'customers' => (int)$db->query("SELECT COUNT(*) FROM tbl_pelanggan WHERE status_member='aktif'")->fetchColumn(),
    'transactions' => (int)$db->query("SELECT COUNT(*) FROM tbl_transaksi WHERE DATE(tanggal_transaksi)=CURDATE() AND status_pesanan<>'dibatalkan'")->fetchColumn(),
    'sales' => (float)$db->query("SELECT COALESCE(SUM(total_belanja),0) FROM tbl_transaksi WHERE DATE(tanggal_transaksi)=CURDATE() AND status_pembayaran='lunas' AND status_pesanan<>'dibatalkan'")->fetchColumn(),
];
$days = $db->query("SELECT DATE(tanggal_transaksi) tanggal, COALESCE(SUM(CASE WHEN status_pembayaran='lunas' AND status_pesanan<>'dibatalkan' THEN total_belanja ELSE 0 END),0) total FROM tbl_transaksi WHERE tanggal_transaksi >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(tanggal_transaksi) ORDER BY tanggal")->fetchAll();
$chartMap = [];
foreach ($days as $d) $chartMap[$d['tanggal']] = (float)$d['total'];
$chartLabels = $chartValues = [];
for ($i=6; $i>=0; $i--) { $date = date('Y-m-d', strtotime("-$i days")); $chartLabels[] = date('d M', strtotime($date)); $chartValues[] = $chartMap[$date] ?? 0; }
$categoryData = $db->query("SELECT k.nama_kategori, COUNT(p.id_produk) jumlah FROM tbl_kategori k LEFT JOIN tbl_produk p ON p.id_kategori=k.id_kategori AND p.status_produk='aktif' WHERE k.status_kategori='aktif' GROUP BY k.id_kategori ORDER BY jumlah DESC LIMIT 7")->fetchAll();
$lowStock = $db->query("SELECT p.id_produk,p.nama_produk,p.sku,p.stok,p.stok_minimum,p.satuan,k.nama_kategori FROM tbl_produk p JOIN tbl_kategori k ON k.id_kategori=p.id_kategori WHERE p.status_produk='aktif' AND p.stok<=p.stok_minimum ORDER BY p.stok ASC LIMIT 7")->fetchAll();
$recent = $db->query("SELECT t.id_transaksi,t.kode_transaksi,t.tanggal_transaksi,t.total_belanja,t.status_pembayaran,COALESCE(pl.nama_pelanggan,'Pelanggan Umum') pelanggan FROM tbl_transaksi t LEFT JOIN tbl_pelanggan pl ON pl.id_pelanggan=t.id_pelanggan ORDER BY t.id_transaksi DESC LIMIT 6")->fetchAll();
$paidCount = (int)$db->query("SELECT COUNT(*) FROM tbl_transaksi WHERE status_pembayaran='lunas'")->fetchColumn();
$pendingCount = (int)$db->query("SELECT COUNT(*) FROM tbl_transaksi WHERE status_pembayaran IN ('belum_bayar','menunggu')")->fetchColumn();
$cancelCount = (int)$db->query("SELECT COUNT(*) FROM tbl_transaksi WHERE status_pesanan='dibatalkan'")->fetchColumn();
?>
<div class="page-heading"><div><span class="section-kicker">RINGKASAN TOKO</span><h1>Dashboard</h1><p>Selamat beraktivitas, <?= e(Auth::user()['nama_lengkap']) ?>. Ini ringkasan WarungMadura hari ini.</p></div><a class="btn btn-primary" href="<?= e(url('transactions')) ?>"><i class="bi bi-plus-lg me-2"></i>Transaksi Baru</a></div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="kpi-card"><div class="kpi-top"><span>Total Produk Aktif</span><span class="kpi-icon icon-violet"><i class="bi bi-box-seam"></i></span></div><div class="kpi-value"><?= number_format($kpi['products']) ?></div><div class="kpi-foot">Produk tersedia di katalog</div></div></div>
    <div class="col-6 col-xl-3"><div class="kpi-card"><div class="kpi-top"><span>Pelanggan Member</span><span class="kpi-icon icon-blue"><i class="bi bi-people"></i></span></div><div class="kpi-value"><?= number_format($kpi['customers']) ?></div><div class="kpi-foot">Member terdaftar aktif</div></div></div>
    <div class="col-6 col-xl-3"><div class="kpi-card"><div class="kpi-top"><span>Transaksi Hari Ini</span><span class="kpi-icon icon-orange"><i class="bi bi-receipt"></i></span></div><div class="kpi-value"><?= number_format($kpi['transactions']) ?></div><div class="kpi-foot">Transaksi yang tercatat hari ini</div></div></div>
    <div class="col-6 col-xl-3"><div class="kpi-card"><div class="kpi-top"><span>Penjualan Hari Ini</span><span class="kpi-icon icon-green"><i class="bi bi-wallet2"></i></span></div><div class="kpi-value kpi-currency"><?= money($kpi['sales']) ?></div><div class="kpi-foot">Pembayaran berstatus lunas</div></div></div>
</div>
<div class="row g-3 mb-4">
    <div class="col-xl-8"><div class="panel-card h-100"><div class="panel-header"><div><h5>Grafik Penjualan</h5><p>Pergerakan penjualan selama 7 hari terakhir</p></div><span class="panel-icon"><i class="bi bi-graph-up"></i></span></div><div class="chart-wrap"><canvas id="salesChart"></canvas></div></div></div>
    <div class="col-xl-4"><div class="panel-card h-100"><div class="panel-header"><div><h5>Produk per Kategori</h5><p>Komposisi katalog aktif</p></div><span class="panel-icon"><i class="bi bi-pie-chart"></i></span></div><div class="chart-wrap chart-doughnut"><canvas id="categoryChart"></canvas></div><div class="chart-legend-list" id="categoryLegend"></div></div></div>
</div>
<div class="row g-3">
    <div class="col-xl-7"><div class="panel-card h-100"><div class="panel-header"><div><h5>Transaksi Terbaru</h5><p>Aktivitas transaksi yang baru masuk</p></div><a class="text-link" href="<?= e(url('reports')) ?>">Lihat semua <i class="bi bi-arrow-right"></i></a></div>
        <div class="table-responsive"><table class="table app-table align-middle"><thead><tr><th>No. Transaksi</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead><tbody>
        <?php if (!$recent): ?><tr><td colspan="5" class="empty-state">Belum ada transaksi.</td></tr><?php endif; ?>
        <?php foreach ($recent as $r): ?><tr><td><a class="table-primary-link" href="<?= e(url('receipt',['id'=>$r['id_transaksi']])) ?>"><?= e($r['kode_transaksi']) ?></a></td><td><?= e($r['pelanggan']) ?></td><td><?= e(date('d/m/Y H:i',strtotime($r['tanggal_transaksi']))) ?></td><td class="fw-semibold"><?= money($r['total_belanja']) ?></td><td><?= status_badge($r['status_pembayaran']) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </div></div>
    <div class="col-xl-5"><div class="panel-card h-100"><div class="panel-header"><div><h5>Perlu Restock</h5><p>Produk yang mencapai batas stok minimum</p></div><a class="text-link" href="<?= e(url('stock')) ?>">Tambah stok <i class="bi bi-arrow-right"></i></a></div>
        <?php if (!$lowStock): ?><div class="empty-state py-5"><i class="bi bi-check2-circle"></i><b>Stok aman</b><span>Tidak ada produk yang perlu restock.</span></div><?php endif; ?>
        <?php foreach ($lowStock as $p): ?><div class="stock-alert-row"><div class="stock-product-icon"><i class="bi bi-box"></i></div><div class="stock-product-info"><b><?= e($p['nama_produk']) ?></b><small><?= e($p['sku']) ?> · <?= e($p['nama_kategori']) ?></small></div><div class="stock-amount"><b><?= (int)$p['stok'] ?> <?= e($p['satuan']) ?></b><small>Min. <?= (int)$p['stok_minimum'] ?></small></div></div><?php endforeach; ?>
    </div></div>
</div>
<script>
window.WM_DASHBOARD = <?= json_encode(['labels'=>$chartLabels,'sales'=>$chartValues,'categories'=>array_column($categoryData,'nama_kategori'),'counts'=>array_map('intval',array_column($categoryData,'jumlah'))], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php
$from = trim((string)($_GET['from'] ?? date('Y-m-d', strtotime('-7 days'))));
$to = trim((string)($_GET['to'] ?? date('Y-m-d')));
$report = trim((string)($_GET['report'] ?? 'overview'));
$validReports = ['overview','sales','purchases','expenses','cash','stock','profit'];
if (!in_array($report, $validReports, true)) $report = 'overview';
$periodFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from : date('Y-m-d', strtotime('-7 days'));
$periodTo = preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) ? $to : date('Y-m-d');
$params = ['from' => $periodFrom, 'to' => $periodTo];
$cashierOptions = $db->query("SELECT id_pengguna,nama_lengkap,username FROM tbl_pengguna WHERE role='kasir' ORDER BY nama_lengkap")->fetchAll();
$loggedUserId = (int)(Auth::user()['id_pengguna'] ?? 0);
$cashierId = Auth::isAdmin() ? max(0, (int)($_GET['cashier'] ?? 0)) : $loggedUserId;
if ($cashierId > 0 && !in_array($cashierId, array_map(static fn($row) => (int)$row['id_pengguna'], $cashierOptions), true)) {
    $cashierId = Auth::isAdmin() ? 0 : $loggedUserId;
}
$cashierName = 'Semua kasir';
foreach ($cashierOptions as $cashierOption) {
    if ((int)$cashierOption['id_pengguna'] === $cashierId) $cashierName = $cashierOption['nama_lengkap'] . ' (@' . $cashierOption['username'] . ')';
}
$salesCashierSql = $cashierId > 0
    ? ' AND t.id_kasir = ?'
    : " AND t.id_kasir IN (SELECT id_pengguna FROM tbl_pengguna WHERE role='kasir')";
$purchaseCashierSql = $cashierId > 0
    ? ' AND p.id_pengguna = ?'
    : " AND p.id_pengguna IN (SELECT id_pengguna FROM tbl_pengguna WHERE role='kasir')";
$expenseCashierSql = $cashierId > 0
    ? ' AND e.id_pengguna = ?'
    : " AND e.id_pengguna IN (SELECT id_pengguna FROM tbl_pengguna WHERE role='kasir')";
$cashierParams = $cashierId > 0 ? [$cashierId] : [];

$salesSummaryStmt = $db->prepare("SELECT COUNT(*) transaksi, COALESCE(SUM(total_belanja),0) total FROM tbl_transaksi t WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < DATE_ADD(?, INTERVAL 1 DAY) AND t.status_pembayaran='lunas' AND t.status_pesanan<> 'dibatalkan'{$salesCashierSql}");
$salesSummaryStmt->execute(array_merge([$periodFrom, $periodTo], $cashierParams));
$salesSummary = $salesSummaryStmt->fetch();

$bestProductStmt = $db->prepare("SELECT d.nama_produk, SUM(d.jumlah) jumlah, SUM(d.subtotal_item) total FROM tbl_detail_transaksi d JOIN tbl_transaksi t ON t.id_transaksi=d.id_transaksi WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < DATE_ADD(?, INTERVAL 1 DAY) AND t.status_pembayaran='lunas' AND t.status_pesanan<> 'dibatalkan'{$salesCashierSql} GROUP BY d.id_produk,d.nama_produk ORDER BY jumlah DESC LIMIT 10");
$bestProductStmt->execute(array_merge([$periodFrom, $periodTo], $cashierParams));
$bestProducts = $bestProductStmt->fetchAll();

$salesRowsStmt = $db->prepare("SELECT t.kode_transaksi,t.tanggal_transaksi,COALESCE(p.nama_pelanggan,'Pelanggan Umum') pelanggan,t.metode_pembayaran,t.total_belanja,u.nama_lengkap kasir FROM tbl_transaksi t LEFT JOIN tbl_pelanggan p ON p.id_pelanggan=t.id_pelanggan LEFT JOIN tbl_pengguna u ON u.id_pengguna=t.id_kasir WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < DATE_ADD(?, INTERVAL 1 DAY) AND t.status_pembayaran='lunas' AND t.status_pesanan<> 'dibatalkan'{$salesCashierSql} ORDER BY t.tanggal_transaksi DESC");
$salesRowsStmt->execute(array_merge([$periodFrom, $periodTo], $cashierParams));
$salesRows = $salesRowsStmt->fetchAll();

$purchaseStmt = $db->prepare("SELECT p.id_penerimaan,p.no_penerimaan,p.waktu_penerimaan,COALESCE(s.nama_supplier,'Supplier umum') supplier,p.status_penerimaan,u.nama_lengkap petugas,COALESCE(SUM(d.subtotal),0) total FROM tbl_penerimaan_barang p LEFT JOIN tbl_supplier s ON s.id_supplier=p.id_supplier LEFT JOIN tbl_pengguna u ON u.id_pengguna=p.id_pengguna LEFT JOIN tbl_detail_penerimaan_barang d ON d.id_penerimaan=p.id_penerimaan WHERE p.waktu_penerimaan >= ? AND p.waktu_penerimaan < DATE_ADD(?, INTERVAL 1 DAY) AND p.status_penerimaan='diterima'{$purchaseCashierSql} GROUP BY p.id_penerimaan,p.no_penerimaan,p.waktu_penerimaan,s.nama_supplier,p.status_penerimaan,u.nama_lengkap ORDER BY p.waktu_penerimaan DESC");
$purchaseStmt->execute(array_merge([$periodFrom, $periodTo], $cashierParams));
$purchaseRows = $purchaseStmt->fetchAll();
$purchaseTotal = array_sum(array_map(static fn($r) => (float)$r['total'], $purchaseRows));

$expenseStmt = $db->prepare("SELECT e.*,u.nama_lengkap petugas FROM tbl_pengeluaran e LEFT JOIN tbl_pengguna u ON u.id_pengguna=e.id_pengguna WHERE e.tanggal_pengeluaran >= ? AND e.tanggal_pengeluaran < DATE_ADD(?, INTERVAL 1 DAY){$expenseCashierSql} ORDER BY e.tanggal_pengeluaran DESC");
$expenseStmt->execute(array_merge([$periodFrom, $periodTo], $cashierParams));
$expenseRows = $expenseStmt->fetchAll();
$expenseTotal = array_sum(array_map(static fn($r) => (float)$r['jumlah'], $expenseRows));

$stockRows = $db->query("SELECT p.id_produk,p.nama_produk,p.sku,p.stok,p.stok_minimum,p.satuan,k.nama_kategori,p.harga_beli,p.harga_jual FROM tbl_produk p JOIN tbl_kategori k ON k.id_kategori=p.id_kategori WHERE p.status_produk='aktif' ORDER BY p.stok ASC,p.nama_produk")->fetchAll();
$stockValue = array_sum(array_map(static fn($r) => (float)$r['stok'] * (float)$r['harga_beli'], $stockRows));

$cogs = 0.0;
try {
    $cogsStmt = $db->prepare("SELECT COALESCE(SUM(d.jumlah*COALESCE(d.harga_modal,0)),0) FROM tbl_detail_transaksi d JOIN tbl_transaksi t ON t.id_transaksi=d.id_transaksi WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < DATE_ADD(?, INTERVAL 1 DAY) AND t.status_pembayaran='lunas' AND t.status_pesanan<> 'dibatalkan'{$salesCashierSql}");
    $cogsStmt->execute(array_merge([$periodFrom, $periodTo], $cashierParams));
    $cogs = (float)$cogsStmt->fetchColumn();
} catch (Throwable $error) {
    $cogsStmt = $db->prepare("SELECT COALESCE(SUM(d.jumlah*COALESCE(p.harga_beli,0)),0) FROM tbl_detail_transaksi d JOIN tbl_transaksi t ON t.id_transaksi=d.id_transaksi JOIN tbl_produk p ON p.id_produk=d.id_produk WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < DATE_ADD(?, INTERVAL 1 DAY) AND t.status_pembayaran='lunas' AND t.status_pesanan<> 'dibatalkan'{$salesCashierSql}");
    $cogsStmt->execute(array_merge([$periodFrom, $periodTo], $cashierParams));
    $cogs = (float)$cogsStmt->fetchColumn();
}
$grossProfit = (float)$salesSummary['total'] - $cogs;
$netProfit = $grossProfit - $expenseTotal;
$cashOut = $purchaseTotal + $expenseTotal;
$cashBalance = (float)$salesSummary['total'] - $cashOut;
?>

<div class="page-heading">
    <div><span class="section-kicker">REKAPITULASI USAHA</span><h1>Laporan &amp; Rekap</h1><p>Pilih laporan, tentukan periode, lalu cetak ke PDF atau Excel. Data kasir: <b><?= e($cashierName) ?></b></p></div>
    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="bi bi-plus-lg me-2"></i>Catat Pengeluaran</button>
</div>

<div class="panel-card mb-4">
    <form method="get" class="filter-toolbar report-filter">
        <input type="hidden" name="page" value="reports">
        <input type="hidden" name="report" value="<?= e($report) ?>">
        <?php if(Auth::isAdmin()): ?>
        <select class="form-select" name="cashier" aria-label="Filter kasir">
            <option value="0" <?= $cashierId === 0 ? 'selected' : '' ?>>Semua kasir</option>
            <?php foreach($cashierOptions as $cashierOption): ?>
            <option value="<?= (int)$cashierOption['id_pengguna'] ?>" <?= $cashierId === (int)$cashierOption['id_pengguna'] ? 'selected' : '' ?>><?= e($cashierOption['nama_lengkap']) ?> (@<?= e($cashierOption['username']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <?php else: ?>
        <input type="hidden" name="cashier" value="<?= $cashierId ?>">
        <?php endif; ?>
        <input class="form-control" type="date" name="from" value="<?= e($periodFrom) ?>" aria-label="Tanggal mulai">
        <input class="form-control" type="date" name="to" value="<?= e($periodTo) ?>" aria-label="Tanggal akhir">
        <button class="btn btn-dark-soft"><i class="bi bi-funnel me-1"></i>Terapkan</button>
        <a class="btn btn-light" href="<?= e(url('reports')) ?>">Reset</a>
    </form>
</div>

<?php if($report === 'overview'): ?>
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['sales','Laporan Penjualan','bi-receipt','Penjualan & barang terlaris','primary'],
        ['purchases','Laporan Pembelian','bi-basket','Pembelian stok dari supplier','warning'],
        ['expenses','Laporan Pengeluaran','bi-wallet','Biaya operasional toko','danger'],
        ['cash','Laporan Arus Kas','bi-arrow-left-right','Uang masuk dan keluar','info'],
        ['stock','Laporan Stok','bi-box-seam','Sisa dan nilai persediaan','success'],
        ['profit','Laporan Laba Rugi','bi-graph-up-arrow','Keuntungan bersih periode','dark'],
    ];
    foreach($cards as [$key,$title,$icon,$subtitle,$tone]):
    ?>
    <div class="col-md-6 col-xl-4">
        <a class="report-card report-card-<?= e($tone) ?>" href="<?= e(url('reports',['report'=>$key,'from'=>$periodFrom,'to'=>$periodTo,'cashier'=>$cashierId])) ?>">
            <span class="report-card-icon"><i class="bi <?= e($icon) ?>"></i></span>
            <span><b><?= e($title) ?></b><small><?= e($subtitle) ?></small></span>
            <i class="bi bi-chevron-right report-card-arrow"></i>
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div><b><?= date('d/m/Y', strtotime($periodFrom)) ?> - <?= date('d/m/Y', strtotime($periodTo)) ?></b>
        <?php if($report !== 'overview'): ?><a class="text-link ms-3" href="<?= e(url('reports',['from'=>$periodFrom,'to'=>$periodTo,'cashier'=>$cashierId])) ?>">Kembali ke semua laporan</a><?php endif; ?>
    </div>
    <?php if($report !== 'overview'): ?>
    <div class="heading-actions">
        <a class="btn btn-primary" href="<?= e(url('report_print',['report'=>$report,'from'=>$periodFrom,'to'=>$periodTo,'cashier'=>$cashierId])) ?>" target="_blank"><i class="bi bi-printer me-1"></i>Halaman Cetak</a>
        <a class="btn btn-outline-primary" href="<?= e(url('report_export',['report'=>$report,'format'=>'pdf','from'=>$periodFrom,'to'=>$periodTo,'cashier'=>$cashierId])) ?>" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
        <a class="btn btn-outline-success" href="<?= e(url('report_export',['report'=>$report,'format'=>'excel','from'=>$periodFrom,'to'=>$periodTo,'cashier'=>$cashierId])) ?>"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel</a>
    </div>
    <?php endif; ?>
</div>

<?php if($report === 'sales'): ?>
<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="kpi-card"><span>Jumlah Transaksi</span><div class="kpi-value"><?= number_format((int)$salesSummary['transaksi']) ?> kali</div></div></div>
    <div class="col-md-4"><div class="kpi-card"><span>Total Penjualan</span><div class="kpi-value kpi-currency"><?= money((float)$salesSummary['total']) ?></div></div></div>
    <div class="col-md-4"><div class="kpi-card"><span>Barang Terlaris</span><div class="kpi-value"><?= e($bestProducts[0]['nama_produk'] ?? '-') ?></div></div></div>
</div>
<div class="row g-3">
    <div class="col-xl-5"><div class="panel-card"><div class="panel-header"><div><h5>Produk Terlaris</h5></div></div><div class="table-responsive"><table class="table app-table"><thead><tr><th>Produk</th><th>Terjual</th><th>Total</th></tr></thead><tbody><?php foreach($bestProducts as $row): ?><tr><td><?= e($row['nama_produk']) ?></td><td><?= (int)$row['jumlah'] ?></td><td><?= money((float)$row['total']) ?></td></tr><?php endforeach; if(!$bestProducts): ?><tr><td colspan="3" class="empty-state">Belum ada penjualan.</td></tr><?php endif; ?></tbody></table></div></div></div>
    <div class="col-xl-7"><div class="panel-card"><div class="panel-header"><div><h5>Rincian Transaksi</h5></div></div><div class="table-responsive"><table class="table app-table"><thead><tr><th>Kode</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th></tr></thead><tbody><?php foreach($salesRows as $row): ?><tr><td><?= e($row['kode_transaksi']) ?></td><td><?= e($row['pelanggan']) ?></td><td><?= date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])) ?></td><td><?= money((float)$row['total_belanja']) ?></td></tr><?php endforeach; if(!$salesRows): ?><tr><td colspan="4" class="empty-state">Belum ada transaksi lunas.</td></tr><?php endif; ?></tbody></table></div></div></div>
</div>
<?php endif; ?>

<?php if($report === 'purchases'): ?>
<div class="row g-3 mb-3"><div class="col-md-6"><div class="kpi-card"><span>Jumlah Penerimaan / Pembelian</span><div class="kpi-value"><?= number_format(count($purchaseRows)) ?> kali</div></div></div><div class="col-md-6"><div class="kpi-card"><span>Total Pembelian Stok</span><div class="kpi-value kpi-currency"><?= money($purchaseTotal) ?></div></div></div></div>
<div class="panel-card"><div class="table-responsive"><table class="table app-table"><thead><tr><th>No. Penerimaan</th><th>Tanggal</th><th>Supplier</th><th>Petugas</th><th>Status</th><th>Total</th></tr></thead><tbody><?php foreach($purchaseRows as $row): ?><tr><td><?= e($row['no_penerimaan']) ?></td><td><?= date('d/m/Y H:i', strtotime($row['waktu_penerimaan'])) ?></td><td><?= e($row['supplier']) ?></td><td><?= e($row['petugas']) ?></td><td><?= e($row['status_penerimaan']) ?></td><td><?= money((float)$row['total']) ?></td></tr><?php endforeach; if(!$purchaseRows): ?><tr><td colspan="6" class="empty-state">Belum ada pembelian stok pada periode ini.</td></tr><?php endif; ?></tbody></table></div></div>
<?php endif; ?>

<?php if($report === 'expenses'): ?>
<div class="kpi-card mb-3"><span>Total Pengeluaran Operasional</span><div class="kpi-value kpi-currency"><?= money($expenseTotal) ?></div></div>
<div class="panel-card"><div class="table-responsive"><table class="table app-table"><thead><tr><th>Nomor</th><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Jumlah</th><th>Petugas</th><th></th></tr></thead><tbody><?php foreach($expenseRows as $row): ?><tr><td><?= e($row['no_pengeluaran']) ?></td><td><?= date('d/m/Y H:i', strtotime($row['tanggal_pengeluaran'])) ?></td><td><?= ucwords(str_replace('_',' ',e($row['kategori_pengeluaran']))) ?></td><td><?= e($row['deskripsi']) ?></td><td><?= money((float)$row['jumlah']) ?></td><td><?= e($row['petugas'] ?: '-') ?></td><td><button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#expenseEdit<?= (int)$row['id_pengeluaran'] ?>"><i class="bi bi-pencil"></i></button></td></tr><?php endforeach; if(!$expenseRows): ?><tr><td colspan="7" class="empty-state">Belum ada pengeluaran pada periode ini.</td></tr><?php endif; ?></tbody></table></div></div>
<?php endif; ?>

<?php if($report === 'cash'): ?>
<div class="row g-3">
    <div class="col-md-4"><div class="kpi-card"><span>Uang Masuk</span><div class="kpi-value kpi-currency"><?= money((float)$salesSummary['total']) ?></div></div></div>
    <div class="col-md-4"><div class="kpi-card"><span>Uang Keluar</span><div class="kpi-value kpi-currency"><?= money($cashOut) ?></div><small>Pembelian <?= money($purchaseTotal) ?> + operasional <?= money($expenseTotal) ?></small></div></div>
    <div class="col-md-4"><div class="kpi-card"><span>Selisih Arus Kas</span><div class="kpi-value kpi-currency <?= $cashBalance < 0 ? 'text-danger' : '' ?>"><?= money($cashBalance) ?></div></div></div>
</div>
<?php endif; ?>

<?php if($report === 'stock'): ?>
<div class="kpi-card mb-3"><span>Nilai Persediaan Aktif</span><div class="kpi-value kpi-currency"><?= money($stockValue) ?></div></div>
<div class="panel-card"><div class="table-responsive"><table class="table app-table"><thead><tr><th>Produk</th><th>Kategori</th><th>SKU</th><th>Sisa Stok</th><th>Batas Minimum</th><th>Status</th></tr></thead><tbody><?php foreach($stockRows as $row): ?><tr><td><?= e($row['nama_produk']) ?></td><td><?= e($row['nama_kategori']) ?></td><td><?= e($row['sku']) ?></td><td><?= (int)$row['stok'] ?> <?= e($row['satuan']) ?></td><td><?= (int)$row['stok_minimum'] ?> <?= e($row['satuan']) ?></td><td><?= (int)$row['stok'] <= (int)$row['stok_minimum'] ? '<span class="badge text-bg-danger">Perlu beli</span>' : '<span class="badge text-bg-success">Aman</span>' ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php endif; ?>

<?php if($report === 'profit'): ?>
<div class="row g-3">
    <div class="col-md-3"><div class="kpi-card"><span>Penjualan</span><div class="kpi-value kpi-currency"><?= money((float)$salesSummary['total']) ?></div></div></div>
    <div class="col-md-3"><div class="kpi-card"><span>Harga Modal Terjual</span><div class="kpi-value kpi-currency"><?= money($cogs) ?></div></div></div>
    <div class="col-md-3"><div class="kpi-card"><span>Biaya Operasional</span><div class="kpi-value kpi-currency"><?= money($expenseTotal) ?></div></div></div>
    <div class="col-md-3"><div class="kpi-card"><span>Laba Bersih</span><div class="kpi-value kpi-currency <?= $netProfit < 0 ? 'text-danger' : '' ?>"><?= money($netProfit) ?></div></div></div>
</div>
<div class="panel-card mt-3"><div class="panel-header"><div><h5>Perhitungan</h5><p>Penjualan - harga modal barang - pengeluaran operasional</p></div></div><div class="p-3"><?= money((float)$salesSummary['total']) ?> - <?= money($cogs) ?> - <?= money($expenseTotal) ?> = <b><?= money($netProfit) ?></b></div></div>
<?php endif; ?>

<div class="modal fade" id="expenseModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post"><?= Csrf::field() ?><input type="hidden" name="action" value="save_expense"><input type="hidden" name="_return" value="reports"><div class="modal-header"><h5 class="modal-title">Catat Pengeluaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3"><div class="col-md-6"><label class="form-label">Tanggal</label><input class="form-control" type="datetime-local" name="tanggal_pengeluaran" value="<?= date('Y-m-d\TH:i') ?>" required></div><div class="col-md-6"><label class="form-label">Kategori</label><select class="form-select" name="kategori_pengeluaran"><option value="listrik">Listrik</option><option value="transportasi">Transportasi</option><option value="perlengkapan">Perlengkapan toko</option><option value="gaji">Gaji</option><option value="sewa">Sewa</option><option value="lainnya">Lainnya</option></select></div><div class="col-12"><label class="form-label">Keterangan</label><input class="form-control" name="deskripsi" required></div><div class="col-12"><label class="form-label">Nominal</label><input class="form-control" type="number" min="1" step="100" name="jumlah" required></div></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Pengeluaran</button></div></form></div></div></div>

<?php foreach($expenseRows as $row): ?>
<div class="modal fade" id="expenseEdit<?= (int)$row['id_pengeluaran'] ?>" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post"><?= Csrf::field() ?><input type="hidden" name="action" value="save_expense"><input type="hidden" name="_return" value="reports"><input type="hidden" name="id_pengeluaran" value="<?= (int)$row['id_pengeluaran'] ?>"><div class="modal-header"><h5 class="modal-title">Edit Pengeluaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3"><div class="col-md-6"><label class="form-label">Tanggal</label><input class="form-control" type="datetime-local" name="tanggal_pengeluaran" value="<?= date('Y-m-d\TH:i', strtotime($row['tanggal_pengeluaran'])) ?>" required></div><div class="col-md-6"><label class="form-label">Kategori</label><select class="form-select" name="kategori_pengeluaran"><?php foreach(['listrik','transportasi','perlengkapan','gaji','sewa','lainnya'] as $cat): ?><option value="<?= e($cat) ?>" <?= $row['kategori_pengeluaran']===$cat?'selected':'' ?>><?= ucwords(str_replace('_',' ',e($cat))) ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Keterangan</label><input class="form-control" name="deskripsi" value="<?= e($row['deskripsi']) ?>" required></div><div class="col-12"><label class="form-label">Nominal</label><input class="form-control" type="number" min="1" step="100" name="jumlah" value="<?= (float)$row['jumlah'] ?>" required></div></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div></form></div></div></div>
<?php endforeach; ?>

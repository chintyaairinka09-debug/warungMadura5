<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

$from = (string)($_GET['from'] ?? date('Y-m-d', strtotime('-7 days')));
$to = (string)($_GET['to'] ?? date('Y-m-d'));
$report = (string)($_GET['report'] ?? 'sales');
$format = strtolower((string)($_GET['format'] ?? 'pdf'));
$validReports = ['sales','purchases','expenses','cash','stock','profit'];
if (!in_array($report, $validReports, true)) $report = 'sales';
if (!in_array($format, ['pdf','excel','print'], true)) $format = 'pdf';
$cashierOptions = $db->query("SELECT id_pengguna,nama_lengkap,username FROM tbl_pengguna WHERE role='kasir'")->fetchAll();
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

$titles = [
    'sales' => 'Laporan Penjualan',
    'purchases' => 'Laporan Pembelian Barang',
    'expenses' => 'Laporan Pengeluaran',
    'cash' => 'Laporan Arus Kas',
    'stock' => 'Laporan Stok Barang',
    'profit' => 'Laporan Laba Rugi',
];

$headers = [];
$rows = [];
$summary = [];

if ($report === 'sales') {
    $headers = ['Kode Transaksi','Pelanggan','Tanggal','Kasir','Metode','Total'];
    $stmt = $db->prepare("SELECT t.kode_transaksi,COALESCE(p.nama_pelanggan,'Pelanggan Umum') pelanggan,t.tanggal_transaksi,COALESCE(u.nama_lengkap,'-') kasir,t.metode_pembayaran,t.total_belanja FROM tbl_transaksi t LEFT JOIN tbl_pelanggan p ON p.id_pelanggan=t.id_pelanggan LEFT JOIN tbl_pengguna u ON u.id_pengguna=t.id_kasir WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < DATE_ADD(?, INTERVAL 1 DAY) AND t.status_pembayaran='lunas' AND t.status_pesanan<> 'dibatalkan'{$salesCashierSql} ORDER BY t.tanggal_transaksi DESC");
    $stmt->execute(array_merge([$from,$to],$cashierParams));
    foreach($stmt->fetchAll() as $r) $rows[] = [$r['kode_transaksi'],$r['pelanggan'],date('d/m/Y H:i',strtotime($r['tanggal_transaksi'])),$r['kasir'],ucwords(str_replace('_',' ',(string)$r['metode_pembayaran'])),(float)$r['total_belanja']];
    $count = $db->prepare("SELECT COUNT(*) AS jumlah_transaksi,COALESCE(SUM(t.total_belanja),0) AS total_penjualan FROM tbl_transaksi t WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < DATE_ADD(?, INTERVAL 1 DAY) AND t.status_pembayaran='lunas' AND t.status_pesanan<> 'dibatalkan'{$salesCashierSql}");
    $count->execute(array_merge([$from,$to],$cashierParams)); $sale = $count->fetch();
    $summary = ['Jumlah transaksi' => (int)$sale['jumlah_transaksi'], 'Total penjualan' => (float)$sale['total_penjualan']];
}

if ($report === 'purchases') {
    $headers = ['No. Penerimaan','Tanggal','Supplier','Petugas','Status','Total'];
    $stmt = $db->prepare("SELECT p.no_penerimaan,p.waktu_penerimaan,COALESCE(s.nama_supplier,'Supplier umum') supplier,COALESCE(u.nama_lengkap,'-') petugas,p.status_penerimaan,COALESCE(SUM(d.subtotal),0) total FROM tbl_penerimaan_barang p LEFT JOIN tbl_supplier s ON s.id_supplier=p.id_supplier LEFT JOIN tbl_pengguna u ON u.id_pengguna=p.id_pengguna LEFT JOIN tbl_detail_penerimaan_barang d ON d.id_penerimaan=p.id_penerimaan WHERE p.waktu_penerimaan >= ? AND p.waktu_penerimaan < DATE_ADD(?, INTERVAL 1 DAY) AND p.status_penerimaan='diterima'{$purchaseCashierSql} GROUP BY p.id_penerimaan,p.no_penerimaan,p.waktu_penerimaan,s.nama_supplier,u.nama_lengkap,p.status_penerimaan ORDER BY p.waktu_penerimaan DESC");
    $stmt->execute(array_merge([$from,$to],$cashierParams)); $total=0.0;
    foreach($stmt->fetchAll() as $r) { $value=(float)$r['total']; $total += $value; $rows[]=[$r['no_penerimaan'],date('d/m/Y H:i',strtotime($r['waktu_penerimaan'])),$r['supplier'],$r['petugas'],$r['status_penerimaan'],$value]; }
    $summary=['Total pembelian barang'=>$total];
}

if ($report === 'expenses') {
    $headers = ['Nomor','Tanggal','Kategori','Deskripsi','Jumlah','Petugas'];
    $stmt = $db->prepare("SELECT e.*,COALESCE(u.nama_lengkap,'-') petugas FROM tbl_pengeluaran e LEFT JOIN tbl_pengguna u ON u.id_pengguna=e.id_pengguna WHERE e.tanggal_pengeluaran >= ? AND e.tanggal_pengeluaran < DATE_ADD(?, INTERVAL 1 DAY){$expenseCashierSql} ORDER BY e.tanggal_pengeluaran DESC");
    $stmt->execute(array_merge([$from,$to],$cashierParams)); $total=0.0;
    foreach($stmt->fetchAll() as $r) { $value=(float)$r['jumlah']; $total += $value; $rows[]=[$r['no_pengeluaran'],date('d/m/Y H:i',strtotime($r['tanggal_pengeluaran'])),ucwords(str_replace('_',' ',(string)$r['kategori_pengeluaran'])),$r['deskripsi'],$value,$r['petugas']]; }
    $summary=['Total pengeluaran'=>$total];
}

if ($report === 'stock') {
    $headers = ['Produk','Kategori','SKU','Sisa Stok','Batas Minimum','Satuan','Status'];
    $stock = $db->query("SELECT p.nama_produk,p.sku,p.stok,p.stok_minimum,p.satuan,k.nama_kategori FROM tbl_produk p JOIN tbl_kategori k ON k.id_kategori=p.id_kategori WHERE p.status_produk='aktif' ORDER BY p.stok ASC,p.nama_produk")->fetchAll();
    foreach($stock as $r) $rows[]=[$r['nama_produk'],$r['nama_kategori'],$r['sku'],(int)$r['stok'],(int)$r['stok_minimum'],$r['satuan'],(int)$r['stok'] <= (int)$r['stok_minimum'] ? 'Perlu beli' : 'Aman'];
}

if ($report === 'cash' || $report === 'profit') {
    $salesStmt = $db->prepare("SELECT COALESCE(SUM(t.total_belanja),0) FROM tbl_transaksi t WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < DATE_ADD(?, INTERVAL 1 DAY) AND t.status_pembayaran='lunas' AND t.status_pesanan<> 'dibatalkan'{$salesCashierSql}");
    $salesStmt->execute(array_merge([$from,$to],$cashierParams)); $sales=(float)$salesStmt->fetchColumn();
    $purchaseStmt=$db->prepare("SELECT COALESCE(SUM(d.subtotal),0) FROM tbl_penerimaan_barang p JOIN tbl_detail_penerimaan_barang d ON d.id_penerimaan=p.id_penerimaan WHERE p.waktu_penerimaan >= ? AND p.waktu_penerimaan < DATE_ADD(?, INTERVAL 1 DAY) AND p.status_penerimaan='diterima'{$purchaseCashierSql}");
    $purchaseStmt->execute(array_merge([$from,$to],$cashierParams)); $purchases=(float)$purchaseStmt->fetchColumn();
    $expenseStmt=$db->prepare("SELECT COALESCE(SUM(e.jumlah),0) FROM tbl_pengeluaran e WHERE e.tanggal_pengeluaran >= ? AND e.tanggal_pengeluaran < DATE_ADD(?, INTERVAL 1 DAY){$expenseCashierSql}");
    $expenseStmt->execute(array_merge([$from,$to],$cashierParams)); $expense=(float)$expenseStmt->fetchColumn();
    if ($report === 'cash') {
        $headers=['Komponen','Nominal'];
        $rows=[['Uang masuk',$sales],['Pembelian barang',$purchases],['Pengeluaran operasional',$expense],['Uang keluar',$purchases+$expense],['Selisih arus kas',$sales-$purchases-$expense]];
        $summary=['Uang masuk'=>$sales,'Uang keluar'=>$purchases+$expense,'Selisih'=>$sales-$purchases-$expense];
    } else {
        $cogsStmt=$db->prepare("SELECT COALESCE(SUM(d.jumlah*COALESCE(d.harga_modal,0)),0) FROM tbl_detail_transaksi d JOIN tbl_transaksi t ON t.id_transaksi=d.id_transaksi WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < DATE_ADD(?, INTERVAL 1 DAY) AND t.status_pembayaran='lunas' AND t.status_pesanan<> 'dibatalkan'{$salesCashierSql}");
        try { $cogsStmt->execute(array_merge([$from,$to],$cashierParams)); $cogs=(float)$cogsStmt->fetchColumn(); } catch(Throwable $error) {
            $fallback=$db->prepare("SELECT COALESCE(SUM(d.jumlah*COALESCE(p.harga_beli,0)),0) FROM tbl_detail_transaksi d JOIN tbl_transaksi t ON t.id_transaksi=d.id_transaksi JOIN tbl_produk p ON p.id_produk=d.id_produk WHERE t.tanggal_transaksi >= ? AND t.tanggal_transaksi < DATE_ADD(?, INTERVAL 1 DAY) AND t.status_pembayaran='lunas' AND t.status_pesanan<> 'dibatalkan'{$salesCashierSql}");
            $fallback->execute(array_merge([$from,$to],$cashierParams)); $cogs=(float)$fallback->fetchColumn();
        }
        $net=$sales-$cogs-$expense;
        $headers=['Komponen','Nominal'];
        $rows=[['Penjualan',$sales],['Harga modal barang terjual',$cogs],['Pengeluaran operasional',$expense],['Laba bersih',$net]];
        $summary=['Penjualan'=>$sales,'Harga modal'=>$cogs,'Pengeluaran operasional'=>$expense,'Laba bersih'=>$net];
    }
}

if ($format === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle(substr($titles[$report],0,31));
    $sheet->setCellValue('A1',$titles[$report]);
    $sheet->setCellValue('A2','Periode: '.$from.' sampai '.$to.' | Kasir: '.$cashierName);
    $sheet->mergeCells('A1:'.chr(64+count($headers)).'1');
    $sheet->mergeCells('A2:'.chr(64+count($headers)).'2');
    $rowNo=4;
    foreach($headers as $i=>$header) $sheet->setCellValue(chr(65+$i).$rowNo,$header);
    $sheet->getStyle('A'.$rowNo.':'.chr(64+count($headers)).$rowNo)->getFont()->setBold(true);
    foreach($rows as $row) {
        $rowNo++;
        foreach($row as $i=>$value) $sheet->setCellValue(chr(65+$i).$rowNo,$value);
    }
    foreach($summary as $label=>$value) {
        $rowNo++;
        $sheet->setCellValue('A'.$rowNo,$label);
        $sheet->setCellValue('B'.$rowNo,$value);
    }
    foreach(range('A',chr(64+count($headers))) as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
    $filename='laporan-'.$report.'-'.$from.'-'.$to.'.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    (new Xlsx($spreadsheet))->save('php://output');
    exit;
}

$htmlRows = '';
foreach ($rows as $row) {
    $htmlRows .= '<tr>';
    foreach ($row as $value) {
        $formatted = is_float($value) || is_int($value) ? number_format((float)$value, 0, ',', '.') : (string)$value;
        $htmlRows .= '<td>'.htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8').'</td>';
    }
    $htmlRows .= '</tr>';
}
if (!$rows) {
    $htmlRows = '<tr><td colspan="'.count($headers).'" style="text-align:center;color:#718078">Tidak ada data pada periode ini.</td></tr>';
}
$summaryHtml = '';
foreach ($summary as $label => $value) {
    $formatted = is_numeric($value) ? number_format((float)$value, 0, ',', '.') : (string)$value;
    $summaryHtml .= '<div class="summary-item"><span>'.htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8').'</span><b>'.htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8').'</b></div>';
}

if ($format === 'print') {
    $tableHeaders = '';
    foreach ($headers as $header) $tableHeaders .= '<th>'.htmlspecialchars($header, ENT_QUOTES, 'UTF-8').'</th>';
    $pdfUrl = htmlspecialchars(url('report_export', ['report'=>$report,'format'=>'pdf','from'=>$from,'to'=>$to,'cashier'=>$cashierId]), ENT_QUOTES, 'UTF-8');
    $excelUrl = htmlspecialchars(url('report_export', ['report'=>$report,'format'=>'excel','from'=>$from,'to'=>$to,'cashier'=>$cashierId]), ENT_QUOTES, 'UTF-8');
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.htmlspecialchars($titles[$report], ENT_QUOTES, 'UTF-8').' · WarungMadura</title><style>
        *{box-sizing:border-box}body{margin:0;background:#f3f6f1;color:#26372d;font:14px Arial,sans-serif}.toolbar{background:#fff;border-bottom:1px solid #dfe8df;padding:14px max(20px,calc((100% - 1120px)/2));display:flex;justify-content:space-between;align-items:center;gap:12px;position:sticky;top:0}.toolbar b{font-size:15px}.actions{display:flex;gap:8px}.actions a{padding:10px 14px;border-radius:8px;text-decoration:none;font-weight:bold;color:white;background:#18784a}.actions a.secondary{background:#cf6812}.wrap{max-width:1120px;margin:28px auto;padding:0 20px}.paper{background:white;border:1px solid #e0e8df;border-radius:14px;padding:32px;box-shadow:0 12px 35px rgba(27,54,36,.07)}.brand{display:flex;align-items:center;gap:12px;border-bottom:3px solid #18784a;padding-bottom:18px}.brand img{width:64px;height:64px;object-fit:cover;border-radius:12px}.brand h1{margin:0;font-size:23px;color:#105936}.brand p{margin:5px 0 0;color:#6b7b70}.title{margin:24px 0 4px;font-size:22px}.period{color:#68786e;margin:0 0 20px}.summary{display:flex;flex-wrap:wrap;gap:12px;margin:20px 0}.summary-item{min-width:170px;flex:1;background:#f2f8f2;border:1px solid #dfebdf;border-radius:10px;padding:13px}.summary-item span{display:block;color:#6e7c71;font-size:12px}.summary-item b{display:block;margin-top:6px;color:#105936;font-size:18px}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #dce5dc;padding:9px;text-align:left}th{background:#e8f3e9;color:#244b34}tbody tr:nth-child(even){background:#fafcf9}.foot{margin-top:22px;color:#77857a;font-size:11px;border-top:1px solid #e4ebe4;padding-top:12px}@media(max-width:640px){.toolbar{align-items:flex-start;flex-direction:column}.paper{padding:18px}.brand img{width:50px;height:50px}.actions a{padding:8px 10px}.wrap{padding:0 10px}}@media print{body{background:#fff}.toolbar{position:static;border:0;padding:0 0 16px}.actions{display:none}.wrap{margin:0 auto;padding:0}.paper{border:0;box-shadow:none;padding:0}.summary-item{break-inside:avoid}thead{display:table-header-group}tr{break-inside:avoid}}
        </style></head><body><header class="toolbar"><b>Pratinjau cetak laporan · WarungMadura</b><nav class="actions"><a href="'.$pdfUrl.'">Unduh PDF</a><a class="secondary" href="'.$excelUrl.'">Unduh Excel</a></nav></header><main class="wrap"><article class="paper"><div class="brand"><img src="'.htmlspecialchars(BASE_URL.'/assets/images/copy.png', ENT_QUOTES, 'UTF-8').'" alt="Logo WarungMadura"><div><h1>WarungMadura</h1><p>Laporan operasional dan keuangan</p></div></div><h2 class="title">'.htmlspecialchars($titles[$report], ENT_QUOTES, 'UTF-8').'</h2><p class="period">Periode: '.htmlspecialchars($from, ENT_QUOTES, 'UTF-8').' s.d. '.htmlspecialchars($to, ENT_QUOTES, 'UTF-8').' · Kasir: '.htmlspecialchars($cashierName, ENT_QUOTES, 'UTF-8').'</p><section class="summary">'.$summaryHtml.'</section><div class="table-wrap"><table><thead><tr>'.$tableHeaders.'</tr></thead><tbody>'.$htmlRows.'</tbody></table></div><div class="foot">Dokumen dibuat dari sistem WarungMadura pada '.date('d/m/Y H:i').'.</div></article></main></body></html>';
    exit;
}

$html = '<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#20342a}h1{font-size:18px}table{width:100%;border-collapse:collapse;margin-top:14px}th,td{border:1px solid #ccd6cf;padding:6px;text-align:left}th{background:#eef5f1}.summary td{border:0;padding:3px}</style></head><body>';
$html .= '<h1>'.htmlspecialchars($titles[$report]).'</h1><p>Periode: '.htmlspecialchars($from).' sampai '.htmlspecialchars($to).' | Kasir: '.htmlspecialchars($cashierName).'</p>';
$html .= '<table><thead><tr>';
foreach($headers as $header) $html .= '<th>'.htmlspecialchars($header).'</th>';
$html .= '</tr></thead><tbody>';
foreach($rows as $row) { $html .= '<tr>'; foreach($row as $value) { $html .= '<td>'.htmlspecialchars(is_numeric($value) ? number_format((float)$value,0,',','.') : (string)$value).'</td>'; } $html .= '</tr>'; }
$html .= '</tbody></table>';
if($summary) { $html .= '<table class="summary"><tbody>'; foreach($summary as $label=>$value) { $html .= '<tr><td><b>'.htmlspecialchars($label).'</b></td><td>'.htmlspecialchars(is_numeric($value)?number_format((float)$value,0,',','.'):(string)$value).'</td></tr>'; } $html .= '</tbody></table>'; }
$html .= '</body></html>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream('laporan-'.$report.'-'.$from.'-'.$to.'.pdf',['Attachment'=>true]);

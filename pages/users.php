<?php
require_admin();
$q = trim((string)($_GET['q'] ?? ''));
$role = (string)($_GET['role'] ?? 'semua');
$pageNo = max(1, (int)($_GET['p'] ?? 1));
$perPage = 10;
$where = ["u.role IN ('admin','kasir','pelanggan')"];
$args = [];
if ($q !== '') {
    $where[] = '(u.nama_lengkap LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR p.no_member LIKE ?)';
    array_push($args, "%$q%", "%$q%", "%$q%", "%$q%");
}
if (in_array($role, ['admin', 'kasir', 'pelanggan'], true)) {
    $where[] = 'u.role=?';
    $args[] = $role;
}
$whereSql = 'WHERE ' . implode(' AND ', $where);
$count = $db->prepare("SELECT COUNT(*) FROM tbl_pengguna u LEFT JOIN tbl_pelanggan p ON p.id_pengguna=u.id_pengguna {$whereSql}");
$count->execute($args);
$total = (int)$count->fetchColumn();
$offset = ($pageNo - 1) * $perPage;
$stmt = $db->prepare("SELECT u.*,p.no_member FROM tbl_pengguna u LEFT JOIN tbl_pelanggan p ON p.id_pengguna=u.id_pengguna {$whereSql} ORDER BY FIELD(u.role,'admin','kasir','pelanggan'),u.id_pengguna DESC LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($args);
$rows = $stmt->fetchAll();
?>
<div class="page-heading"><div><span class="section-kicker">PENGATURAN AKUN</span><h1>Pengguna & Hak Akses</h1><p>Kelola akun Admin, Kasir, dan Pelanggan yang dapat login ke WarungMadura.</p></div><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal"><i class="bi bi-person-plus me-2"></i>Tambah Pengguna</button></div>
<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="kpi-card compact"><div class="kpi-top"><span>Admin</span><span class="kpi-icon icon-violet"><i class="bi bi-shield-lock"></i></span></div><div class="kpi-foot">Pengelola penuh sistem</div></div></div>
    <div class="col-md-4"><div class="kpi-card compact"><div class="kpi-top"><span>Kasir</span><span class="kpi-icon icon-blue"><i class="bi bi-shop"></i></span></div><div class="kpi-foot">Operasional toko dan transaksi</div></div></div>
    <div class="col-md-4"><div class="kpi-card compact"><div class="kpi-top"><span>Pelanggan / User</span><span class="kpi-icon icon-green"><i class="bi bi-person-heart"></i></span></div><div class="kpi-foot">Akun member dan portal pelanggan</div></div></div>
</div>
<div class="alert alert-info d-flex gap-2 align-items-start"><i class="bi bi-shield-lock fs-5"></i><div><b>Kontrol akses:</b> Admin memiliki akses penuh. Kasir dapat menjalankan operasional toko dan menambah produk, tetapi tidak dapat mengedit atau menonaktifkan produk. Pelanggan hanya dapat membuka portal akun miliknya.</div></div>
<div class="panel-card">
    <div class="filter-toolbar"><form method="get" class="filter-form"><input type="hidden" name="page" value="users"><div class="search-box"><i class="bi bi-search"></i><input name="q" value="<?=e($q)?>" placeholder="Cari nama, username, email, nomor member..."></div><select class="form-select" name="role"><option value="semua">Semua role</option><option value="admin" <?=$role==='admin'?'selected':''?>>Admin</option><option value="kasir" <?=$role==='kasir'?'selected':''?>>Kasir</option><option value="pelanggan" <?=$role==='pelanggan'?'selected':''?>>Pelanggan / User</option></select><button class="btn btn-dark-soft">Filter</button></form><span class="result-count"><?=$total?> akun</span></div>
    <div class="table-responsive"><table class="table app-table align-middle"><thead><tr><th>Pengguna</th><th>Username</th><th>Role</th><th>No. Member</th><th>Login Terakhir</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
    <?php if(!$rows):?><tr><td colspan="7" class="empty-state">Belum ada akun.</td></tr><?php endif;?>
    <?php foreach($rows as $r):?><tr>
        <td><div class="member-cell"><div class="member-avatar"><?=e(strtoupper(substr($r['nama_lengkap'],0,1)))?></div><div><b><?=e($r['nama_lengkap'])?></b><small><?=e($r['email']?:'Email belum diisi')?></small></div></div></td>
        <td><code><?=e($r['username'])?></code></td>
        <td><span class="role-chip <?=e($r['role'])?>"><?=e($r['role']==='pelanggan'?'Pelanggan':ucfirst($r['role']))?></span></td>
        <td><?=e($r['no_member']?:'—')?></td>
        <td><?=e($r['terakhir_login']?date('d/m/Y H:i',strtotime($r['terakhir_login'])):'Belum pernah')?></td>
        <td><?=status_badge($r['status_pengguna'])?></td>
        <td class="text-end"><div class="table-actions"><button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editUser<?=$r['id_pengguna']?>" title="Edit akun"><i class="bi bi-pencil"></i></button><?php if((int)$r['id_pengguna']!==(int)Auth::user()['id_pengguna']):?><form method="post"><?=Csrf::field()?><input type="hidden" name="action" value="toggle_user"><input type="hidden" name="_return" value="users"><input type="hidden" name="id_pengguna" value="<?=$r['id_pengguna']?>"><button class="btn btn-sm btn-light" title="Aktif/nonaktif"><i class="bi bi-toggle-on"></i></button></form><?php endif;?></div></td>
    </tr><?php endforeach;?>
    </tbody></table></div><div class="panel-footer"><span class="text-muted small">Halaman <?=$pageNo?></span><?php pagination($total,$pageNo,$perPage,'users',['q'=>$q,'role'=>$role]);?></div>
</div>
<div class="modal fade" id="userModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post"><?=Csrf::field()?><input type="hidden" name="action" value="save_user"><input type="hidden" name="_return" value="users"><div class="modal-header"><h5 class="modal-title">Tambah Pengguna</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div><div class="modal-body"><label class="form-label">Nama Lengkap *</label><input class="form-control mb-3" name="nama_lengkap" required><label class="form-label">Username *</label><input class="form-control mb-3" name="username" required><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email"><label class="form-label">Nomor HP</label><input class="form-control mb-3" name="no_hp"><label class="form-label">Role Akun</label><select class="form-select mb-3" name="role"><option value="kasir">Kasir</option><option value="pelanggan">Pelanggan / User</option><option value="admin">Admin</option></select><label class="form-label">Password *</label><input class="form-control" type="password" name="password" minlength="8" required><div class="form-hint mt-2">Untuk role Pelanggan, sistem otomatis membuat nomor member dan dua jenis saldo simulasi. Password minimal 8 karakter dan disimpan dalam bentuk hash.</div></div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Batal</button><button class="btn btn-primary">Buat Akun</button></div></form></div></div></div>
<?php foreach($rows as $r):?><div class="modal fade" id="editUser<?=$r['id_pengguna']?>" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post"><?=Csrf::field()?><input type="hidden" name="action" value="save_user"><input type="hidden" name="_return" value="users"><input type="hidden" name="id_pengguna" value="<?=$r['id_pengguna']?>"><div class="modal-header"><h5 class="modal-title">Edit Pengguna</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div><div class="modal-body"><label class="form-label">Nama Lengkap</label><input class="form-control mb-3" name="nama_lengkap" value="<?=e($r['nama_lengkap'])?>" required><label class="form-label">Username</label><input class="form-control mb-3" name="username" value="<?=e($r['username'])?>" required><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" value="<?=e($r['email'])?>"><label class="form-label">Nomor HP</label><input class="form-control mb-3" name="no_hp" value="<?=e($r['no_hp'])?>"><label class="form-label">Role</label><select class="form-select mb-3" name="role"><option value="admin" <?=$r['role']==='admin'?'selected':''?>>Admin</option><option value="kasir" <?=$r['role']==='kasir'?'selected':''?>>Kasir</option><option value="pelanggan" <?=$r['role']==='pelanggan'?'selected':''?>>Pelanggan / User</option></select><label class="form-label">Password Baru</label><input class="form-control" type="password" name="password" minlength="8" placeholder="Kosongkan jika tidak diubah"><div class="form-hint mt-2">Mengubah role menjadi Pelanggan akan membuat profil member. Mengubah pelanggan menjadi Admin/Kasir akan melepas tautan login dari profil member.</div></div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal" type="button">Batal</button><button class="btn btn-primary">Simpan</button></div></form></div></div></div><?php endforeach;?>

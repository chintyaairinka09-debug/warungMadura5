-- =============================================================
-- WARUNGMADURA - TAMBAH AKUN DEMO PELANGGAN / USER
-- Jalankan setelah db_warungmadura.sql sudah di-import.
-- Aman dijalankan ulang: memakai INSERT IGNORE.
-- =============================================================
USE db_warungmadura;

INSERT IGNORE INTO tbl_pengguna
  (nama_lengkap, username, email, password_hash, no_hp, role, status_pengguna)
VALUES
  ('User Demo WarungMadura', 'userdemo', 'userdemo@warungmadura.local',
   '$2y$12$l94eWsn80r5NdfMhB1Be..yVlEfuJSek2EeH.3A/mqR4ezWHNbXOa',
   '081234567890', 'pelanggan', 'aktif');

INSERT IGNORE INTO tbl_pelanggan
  (id_pengguna, no_member, nama_pelanggan, no_hp, email)
SELECT id_pengguna, 'WM-DEMO-0001', 'User Demo WarungMadura',
       '081234567890', 'userdemo@warungmadura.local'
FROM tbl_pengguna
WHERE username='userdemo' AND role='pelanggan';

INSERT IGNORE INTO tbl_saldo_pelanggan (id_pelanggan, jenis_saldo, saldo)
SELECT p.id_pelanggan, b.jenis_saldo, 0
FROM tbl_pelanggan p
JOIN tbl_pengguna u ON u.id_pengguna=p.id_pengguna
JOIN (SELECT 'tabungan' AS jenis_saldo UNION ALL SELECT 'ewallet') b
WHERE u.username='userdemo';

SELECT id_pengguna, nama_lengkap, username, role, status_pengguna
FROM tbl_pengguna
WHERE username IN ('admin','kasir','userdemo')
ORDER BY FIELD(role,'admin','kasir','pelanggan');

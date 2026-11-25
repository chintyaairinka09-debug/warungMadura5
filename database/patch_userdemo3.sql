INSERT IGNORE INTO tbl_pengguna
  (nama_lengkap,username,email,password_hash,role,status_pengguna)
VALUES
  ('Alya Putri','userdemo1','userdemo1@warungmadura.local','$2y$10$cJOU38mS2kbVMs92eVeF5.0/xJQv7SNzwmsdtLBlanP1RNCKkMZ3S','pelanggan','aktif'),
  ('Bima Pratama','userdemo2','userdemo2@warungmadura.local','$2y$10$cJOU38mS2kbVMs92eVeF5.0/xJQv7SNzwmsdtLBlanP1RNCKkMZ3S','pelanggan','aktif'),
  ('Nadia Safitri','userdemo3','userdemo3@warungmadura.local','$2y$10$cJOU38mS2kbVMs92eVeF5.0/xJQv7SNzwmsdtLBlanP1RNCKkMZ3S','pelanggan','aktif');

INSERT IGNORE INTO tbl_pelanggan
  (id_pengguna,no_member,nama_pelanggan,no_hp,email)
SELECT u.id_pengguna,d.no_member,d.nama_lengkap,d.no_hp,u.email
FROM tbl_pengguna u
JOIN (
  SELECT 'userdemo1' username,'WM-DEMO-0001' no_member,'Alya Putri' nama_lengkap,'081234567801' no_hp
  UNION ALL
  SELECT 'userdemo2','WM-DEMO-0002','Bima Pratama','081234567802'
  UNION ALL
  SELECT 'userdemo3','WM-DEMO-0003','Nadia Safitri','081234567803'
) d ON d.username=u.username
WHERE u.role='pelanggan';

INSERT IGNORE INTO tbl_saldo_pelanggan (id_pelanggan,jenis_saldo,saldo)
SELECT p.id_pelanggan,b.jenis_saldo,0
FROM tbl_pelanggan p
JOIN tbl_pengguna u ON u.id_pengguna=p.id_pengguna
JOIN (SELECT 'tabungan' jenis_saldo UNION ALL SELECT 'ewallet') b
WHERE u.username IN ('userdemo1','userdemo2','userdemo3');

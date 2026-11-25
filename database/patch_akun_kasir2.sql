INSERT IGNORE INTO tbl_pengguna
  (nama_lengkap, username, email, password_hash, role, status_pengguna)
VALUES
  (
    'Kasir Dua WarungMadura',
    'kasir2',
    'kasir2@warungmadura.local',
    '$2y$10$UUZfLUJPGB1iZpmaGjjz7efH/1g1a0RkWA1i3lTRreniUCVF7yIbq',
    'kasir',
    'aktif'
  );

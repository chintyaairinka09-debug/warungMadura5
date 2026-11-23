-- =============================================================
-- WARUNGMADURA - DATABASE BARU
-- Target: MariaDB 10.4+ / XAMPP
-- Database ini berdiri sendiri dan TIDAK mengubah db_tokoku.
-- =============================================================

CREATE DATABASE IF NOT EXISTS db_warungmadura
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE db_warungmadura;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. AKUN ADMIN, KASIR, DAN PELANGGAN
CREATE TABLE IF NOT EXISTS tbl_pengguna (
  id_pengguna INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_lengkap VARCHAR(120) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(120) NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  no_hp VARCHAR(25) NULL,
  role ENUM('admin','kasir','pelanggan') NOT NULL DEFAULT 'pelanggan',
  status_pengguna ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  terakhir_login DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pengguna_role_status (role, status_pengguna)
) ENGINE=InnoDB;

-- 2. DATA PELANGGAN / MEMBER
CREATE TABLE IF NOT EXISTS tbl_pelanggan (
  id_pelanggan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pengguna INT UNSIGNED NULL UNIQUE,
  no_member VARCHAR(30) NOT NULL UNIQUE,
  rfid_uid VARCHAR(80) NULL UNIQUE,
  nama_pelanggan VARCHAR(120) NOT NULL,
  no_hp VARCHAR(25) NULL,
  email VARCHAR(120) NULL,
  alamat TEXT NULL,
  kota VARCHAR(80) NULL,
  status_member ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pelanggan_pengguna FOREIGN KEY (id_pengguna)
    REFERENCES tbl_pengguna(id_pengguna) ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_pelanggan_nama (nama_pelanggan),
  INDEX idx_pelanggan_hp (no_hp)
) ENGINE=InnoDB;

-- 3. KATEGORI DAN MEREK
CREATE TABLE IF NOT EXISTS tbl_kategori (
  id_kategori INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL UNIQUE,
  deskripsi VARCHAR(255) NULL,
  status_kategori ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tbl_merk (
  id_merk INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_merk VARCHAR(100) NOT NULL UNIQUE,
  deskripsi VARCHAR(255) NULL,
  status_merk ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. SUPPLIER / PEMASOK
CREATE TABLE IF NOT EXISTS tbl_supplier (
  id_supplier INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_supplier VARCHAR(30) NOT NULL UNIQUE,
  nama_supplier VARCHAR(120) NOT NULL,
  nama_kontak VARCHAR(100) NULL,
  no_hp VARCHAR(25) NULL,
  email VARCHAR(120) NULL,
  alamat TEXT NULL,
  status_supplier ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_supplier_nama (nama_supplier)
) ENGINE=InnoDB;

-- 5. PRODUK
CREATE TABLE IF NOT EXISTS tbl_produk (
  id_produk INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_kategori INT UNSIGNED NOT NULL,
  id_merk INT UNSIGNED NULL,
  sku VARCHAR(50) NOT NULL UNIQUE,
  barcode VARCHAR(80) NULL UNIQUE,
  nama_produk VARCHAR(160) NOT NULL,
  deskripsi TEXT NULL,
  harga_beli DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  harga_jual DECIMAL(14,2) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  satuan VARCHAR(30) NOT NULL DEFAULT 'pcs',
  stok_minimum INT UNSIGNED NOT NULL DEFAULT 5,
  gambar VARCHAR(255) NULL,
  status_produk ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_produk_kategori FOREIGN KEY (id_kategori)
    REFERENCES tbl_kategori(id_kategori) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_produk_merk FOREIGN KEY (id_merk)
    REFERENCES tbl_merk(id_merk) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_produk_nama (nama_produk),
  INDEX idx_produk_status (status_produk),
  INDEX idx_produk_stok (stok)
) ENGINE=InnoDB;

-- 6. KERANJANG BELANJA ONLINE
CREATE TABLE IF NOT EXISTS tbl_keranjang (
  id_keranjang BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pelanggan INT UNSIGNED NOT NULL,
  status_keranjang ENUM('aktif','checkout','dibatalkan') NOT NULL DEFAULT 'aktif',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_keranjang_pelanggan FOREIGN KEY (id_pelanggan)
    REFERENCES tbl_pelanggan(id_pelanggan) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_keranjang_pelanggan_status (id_pelanggan, status_keranjang)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tbl_detail_keranjang (
  id_detail_keranjang BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_keranjang BIGINT UNSIGNED NOT NULL,
  id_produk INT UNSIGNED NOT NULL,
  jumlah INT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_detail_keranjang_keranjang FOREIGN KEY (id_keranjang)
    REFERENCES tbl_keranjang(id_keranjang) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_detail_keranjang_produk FOREIGN KEY (id_produk)
    REFERENCES tbl_produk(id_produk) ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uq_keranjang_produk (id_keranjang, id_produk)
) ENGINE=InnoDB;

-- 7. TRANSAKSI / ORDER (ONLINE MAUPUN KASIR TOKO)
CREATE TABLE IF NOT EXISTS tbl_transaksi (
  id_transaksi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_transaksi VARCHAR(40) NOT NULL UNIQUE,
  id_pelanggan INT UNSIGNED NULL,
  id_kasir INT UNSIGNED NULL,
  jenis_transaksi ENUM('online','toko') NOT NULL DEFAULT 'toko',
  status_pesanan ENUM('menunggu','diproses','siap_diambil','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  status_pembayaran ENUM('belum_bayar','menunggu','lunas','gagal','dikembalikan') NOT NULL DEFAULT 'belum_bayar',
  metode_pembayaran ENUM('tunai','tabungan','ewallet','qris_dana') NULL,
  metode_penerimaan ENUM('ambil_toko','diantar') NOT NULL DEFAULT 'ambil_toko',
  tanggal_transaksi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  diskon DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  biaya_pengiriman DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  total_belanja DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  jumlah_bayar DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  kembalian DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  nama_penerima VARCHAR(120) NULL,
  no_hp_penerima VARCHAR(25) NULL,
  alamat_pengiriman TEXT NULL,
  catatan TEXT NULL,
  CONSTRAINT fk_transaksi_pelanggan FOREIGN KEY (id_pelanggan)
    REFERENCES tbl_pelanggan(id_pelanggan) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_transaksi_kasir FOREIGN KEY (id_kasir)
    REFERENCES tbl_pengguna(id_pengguna) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_transaksi_tanggal (tanggal_transaksi),
  INDEX idx_transaksi_status (status_pesanan, status_pembayaran),
  INDEX idx_transaksi_pelanggan (id_pelanggan)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tbl_detail_transaksi (
  id_detail BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_transaksi BIGINT UNSIGNED NOT NULL,
  id_produk INT UNSIGNED NOT NULL,
  nama_produk VARCHAR(160) NOT NULL,
  sku_produk VARCHAR(50) NULL,
  jumlah INT UNSIGNED NOT NULL,
  harga_satuan DECIMAL(14,2) NOT NULL,
  diskon_item DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  subtotal_item DECIMAL(14,2) NOT NULL,
  CONSTRAINT fk_detail_transaksi_header FOREIGN KEY (id_transaksi)
    REFERENCES tbl_transaksi(id_transaksi) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_detail_transaksi_produk FOREIGN KEY (id_produk)
    REFERENCES tbl_produk(id_produk) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_detail_produk (id_produk)
) ENGINE=InnoDB;

-- 8. PEMBAYARAN
CREATE TABLE IF NOT EXISTS tbl_pembayaran (
  id_pembayaran BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_transaksi BIGINT UNSIGNED NOT NULL UNIQUE,
  metode_pembayaran ENUM('tunai','tabungan','ewallet','qris_dana') NOT NULL,
  jumlah_bayar DECIMAL(14,2) NOT NULL,
  status_pembayaran ENUM('menunggu','berhasil','gagal','dikembalikan') NOT NULL DEFAULT 'menunggu',
  referensi_simulasi VARCHAR(80) NULL,
  diproses_oleh INT UNSIGNED NULL,
  waktu_pembayaran DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pembayaran_transaksi FOREIGN KEY (id_transaksi)
    REFERENCES tbl_transaksi(id_transaksi) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_pembayaran_pengguna FOREIGN KEY (diproses_oleh)
    REFERENCES tbl_pengguna(id_pengguna) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 9. SALDO SIMULASI PELANGGAN: TABUNGAN DAN EWALLET
CREATE TABLE IF NOT EXISTS tbl_saldo_pelanggan (
  id_saldo BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pelanggan INT UNSIGNED NOT NULL,
  jenis_saldo ENUM('tabungan','ewallet') NOT NULL,
  saldo DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  status_saldo ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_saldo_pelanggan FOREIGN KEY (id_pelanggan)
    REFERENCES tbl_pelanggan(id_pelanggan) ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uq_pelanggan_jenis_saldo (id_pelanggan, jenis_saldo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tbl_mutasi_saldo (
  id_mutasi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_saldo BIGINT UNSIGNED NOT NULL,
  id_transaksi BIGINT UNSIGNED NULL,
  id_pengguna INT UNSIGNED NULL,
  jenis_mutasi ENUM('topup_simulasi','pembayaran','refund','penyesuaian') NOT NULL,
  arah ENUM('masuk','keluar') NOT NULL,
  jumlah DECIMAL(14,2) NOT NULL,
  saldo_sebelum DECIMAL(14,2) NOT NULL,
  saldo_sesudah DECIMAL(14,2) NOT NULL,
  keterangan VARCHAR(255) NULL,
  waktu_mutasi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mutasi_saldo_saldo FOREIGN KEY (id_saldo)
    REFERENCES tbl_saldo_pelanggan(id_saldo) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_mutasi_saldo_transaksi FOREIGN KEY (id_transaksi)
    REFERENCES tbl_transaksi(id_transaksi) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_mutasi_saldo_pengguna FOREIGN KEY (id_pengguna)
    REFERENCES tbl_pengguna(id_pengguna) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_mutasi_saldo_waktu (waktu_mutasi)
) ENGINE=InnoDB;

-- 10. PENERIMAAN BARANG DARI SUPPLIER / PENAMBAHAN STOK
CREATE TABLE IF NOT EXISTS tbl_penerimaan_barang (
  id_penerimaan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  no_penerimaan VARCHAR(50) NOT NULL UNIQUE,
  id_supplier INT UNSIGNED NULL,
  id_pengguna INT UNSIGNED NOT NULL,
  status_penerimaan ENUM('draft','diterima','dibatalkan') NOT NULL DEFAULT 'draft',
  waktu_penerimaan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  catatan TEXT NULL,
  CONSTRAINT fk_penerimaan_supplier FOREIGN KEY (id_supplier)
    REFERENCES tbl_supplier(id_supplier) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_penerimaan_pengguna FOREIGN KEY (id_pengguna)
    REFERENCES tbl_pengguna(id_pengguna) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_penerimaan_waktu (waktu_penerimaan)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tbl_detail_penerimaan_barang (
  id_detail_penerimaan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_penerimaan BIGINT UNSIGNED NOT NULL,
  id_produk INT UNSIGNED NOT NULL,
  jumlah INT UNSIGNED NOT NULL,
  harga_beli DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT fk_detail_penerimaan_header FOREIGN KEY (id_penerimaan)
    REFERENCES tbl_penerimaan_barang(id_penerimaan) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_detail_penerimaan_produk FOREIGN KEY (id_produk)
    REFERENCES tbl_produk(id_produk) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_detail_penerimaan_produk (id_produk)
) ENGINE=InnoDB;

-- 11. RIWAYAT PERUBAHAN STOK
CREATE TABLE IF NOT EXISTS tbl_mutasi_stok (
  id_mutasi_stok BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_produk INT UNSIGNED NOT NULL,
  id_pengguna INT UNSIGNED NULL,
  id_transaksi BIGINT UNSIGNED NULL,
  id_penerimaan BIGINT UNSIGNED NULL,
  jenis_mutasi ENUM('stok_masuk','penjualan','penyesuaian','retur','pembatalan') NOT NULL,
  jumlah INT NOT NULL,
  stok_sebelum INT NOT NULL,
  stok_sesudah INT NOT NULL,
  keterangan VARCHAR(255) NULL,
  waktu_mutasi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mutasi_stok_produk FOREIGN KEY (id_produk)
    REFERENCES tbl_produk(id_produk) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_mutasi_stok_pengguna FOREIGN KEY (id_pengguna)
    REFERENCES tbl_pengguna(id_pengguna) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_mutasi_stok_transaksi FOREIGN KEY (id_transaksi)
    REFERENCES tbl_transaksi(id_transaksi) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_mutasi_stok_penerimaan FOREIGN KEY (id_penerimaan)
    REFERENCES tbl_penerimaan_barang(id_penerimaan) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_mutasi_stok_waktu (waktu_mutasi),
  INDEX idx_mutasi_stok_produk (id_produk)
) ENGINE=InnoDB;

-- 12. LOG AKTIVITAS PENGGUNA
CREATE TABLE IF NOT EXISTS tbl_log_aktivitas (
  id_log BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pengguna INT UNSIGNED NULL,
  aktivitas VARCHAR(100) NOT NULL,
  nama_tabel VARCHAR(80) NULL,
  id_data BIGINT NULL,
  keterangan TEXT NULL,
  alamat_ip VARCHAR(45) NULL,
  waktu_aktivitas DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_pengguna FOREIGN KEY (id_pengguna)
    REFERENCES tbl_pengguna(id_pengguna) ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_log_waktu (waktu_aktivitas),
  INDEX idx_log_aktivitas (aktivitas)
) ENGINE=InnoDB;

-- =============================================================
-- DATA AWAL / DEMO
-- Akun demo di bawah memakai password hash bcrypt.
-- Ganti password setelah login pertama kali.
-- =============================================================
INSERT IGNORE INTO tbl_pengguna
  (nama_lengkap, username, email, password_hash, role, status_pengguna)
VALUES
  ('Administrator WarungMadura', 'admin', 'admin@warungmadura.local', '$2y$12$kr6NHk5sw24.FAQdtj0Dyuyd2s1kFOVtCKAZsZNV1cUEw9soZMOAm', 'admin', 'aktif'),
  ('Kasir WarungMadura', 'kasir', 'kasir@warungmadura.local', '$2y$12$0gthfnBMc3oiShtTQv36N.o/hCmjH5e26nG5AFVpjSg4YLgsa03T6', 'kasir', 'aktif'),
  ('User Demo WarungMadura', 'userdemo', 'userdemo@warungmadura.local', '$2y$12$l94eWsn80r5NdfMhB1Be..yVlEfuJSek2EeH.3A/mqR4ezWHNbXOa', 'pelanggan', 'aktif');

-- Akun userdemo otomatis mendapatkan nomor member dan dua saldo virtual.
INSERT IGNORE INTO tbl_pelanggan (id_pengguna,no_member,nama_pelanggan,no_hp,email)
SELECT id_pengguna,'WM-DEMO-0001','User Demo WarungMadura','081234567890','userdemo@warungmadura.local'
FROM tbl_pengguna WHERE username='userdemo' AND role='pelanggan';

INSERT IGNORE INTO tbl_saldo_pelanggan (id_pelanggan,jenis_saldo,saldo)
SELECT p.id_pelanggan,b.jenis_saldo,0
FROM tbl_pelanggan p
JOIN tbl_pengguna u ON u.id_pengguna=p.id_pengguna
JOIN (SELECT 'tabungan' AS jenis_saldo UNION ALL SELECT 'ewallet') b
WHERE u.username='userdemo';

INSERT IGNORE INTO tbl_kategori (nama_kategori, deskripsi) VALUES
 ('Sembako', 'Beras, minyak, gula, tepung, dan kebutuhan pokok'),
 ('Makanan & Mi Instan', 'Makanan ringan, mi instan, dan makanan kemasan'),
 ('Minuman', 'Air mineral, teh, kopi, susu, dan minuman kemasan'),
 ('Perawatan Diri', 'Sabun, sampo, pasta gigi, dan kebutuhan pribadi'),
 ('Kebersihan Rumah', 'Detergen, cairan pencuci, dan perlengkapan kebersihan'),
 ('Perlengkapan Bayi', 'Popok, tisu bayi, dan kebutuhan bayi'),
 ('Alat Tulis', 'Buku, pulpen, pensil, dan perlengkapan sekolah'),
 ('Frozen Food', 'Makanan beku dan produk siap masak');

INSERT IGNORE INTO tbl_merk (nama_merk, deskripsi) VALUES
 ('Tanpa Merek', 'Produk curah atau produk tanpa merek khusus'),
 ('Indomie', 'Merek makanan dan mi instan'),
 ('Bimoli', 'Merek minyak goreng'),
 ('Gulaku', 'Merek gula'),
 ('Segitiga Biru', 'Merek tepung terigu'),
 ('SariWangi', 'Merek teh'),
 ('Kapal Api', 'Merek kopi'),
 ('Ultra Milk', 'Merek susu UHT'),
 ('Lifebuoy', 'Merek perawatan diri'),
 ('Sunlight', 'Merek produk pencuci');

INSERT IGNORE INTO tbl_supplier
 (kode_supplier, nama_supplier, nama_kontak, no_hp, alamat)
VALUES
 ('SUP-001', 'Supplier Umum WarungMadura', NULL, NULL, NULL);

-- Contoh produk untuk uji coba. Harga/stok ini hanya data DEMO,
-- silakan sesuaikan dengan harga dan stok toko sebenarnya.
INSERT IGNORE INTO tbl_produk
 (id_kategori, id_merk, sku, nama_produk, harga_beli, harga_jual, stok, satuan, stok_minimum)
SELECT k.id_kategori, m.id_merk, 'WM-SMB-001', 'Beras Premium 5 Kg', 68000, 75000, 20, 'pack', 5
FROM tbl_kategori k JOIN tbl_merk m ON m.nama_merk='Tanpa Merek'
WHERE k.nama_kategori='Sembako';

INSERT IGNORE INTO tbl_produk
 (id_kategori, id_merk, sku, nama_produk, harga_beli, harga_jual, stok, satuan, stok_minimum)
SELECT k.id_kategori, m.id_merk, 'WM-SMB-002', 'Minyak Goreng 1 Liter', 19000, 23000, 20, 'botol', 5
FROM tbl_kategori k JOIN tbl_merk m ON m.nama_merk='Bimoli'
WHERE k.nama_kategori='Sembako';

INSERT IGNORE INTO tbl_produk
 (id_kategori, id_merk, sku, nama_produk, harga_beli, harga_jual, stok, satuan, stok_minimum)
SELECT k.id_kategori, m.id_merk, 'WM-SMB-003', 'Gula Pasir 1 Kg', 15000, 18000, 20, 'pack', 5
FROM tbl_kategori k JOIN tbl_merk m ON m.nama_merk='Gulaku'
WHERE k.nama_kategori='Sembako';

INSERT IGNORE INTO tbl_produk
 (id_kategori, id_merk, sku, nama_produk, harga_beli, harga_jual, stok, satuan, stok_minimum)
SELECT k.id_kategori, m.id_merk, 'WM-SMB-004', 'Tepung Terigu 1 Kg', 11000, 13000, 20, 'pack', 5
FROM tbl_kategori k JOIN tbl_merk m ON m.nama_merk='Segitiga Biru'
WHERE k.nama_kategori='Sembako';

INSERT IGNORE INTO tbl_produk
 (id_kategori, id_merk, sku, barcode, nama_produk, harga_beli, harga_jual, stok, satuan, stok_minimum)
SELECT k.id_kategori, m.id_merk, 'WM-MKN-001', '5285000390602', 'IndoMie Goreng', 2700, 3500, 50, 'pcs', 10
FROM tbl_kategori k JOIN tbl_merk m ON m.nama_merk='Indomie'
WHERE k.nama_kategori='Makanan & Mi Instan';

INSERT IGNORE INTO tbl_produk
 (id_kategori, id_merk, sku, barcode, nama_produk, harga_beli, harga_jual, stok, satuan, stok_minimum)
SELECT k.id_kategori, m.id_merk, 'WM-MNM-001', '8886007811113', 'Teh Poci Celup Isi 25', 9000, 12000, 20, 'box', 5
FROM tbl_kategori k JOIN tbl_merk m ON m.nama_merk='SariWangi'
WHERE k.nama_kategori='Minuman';

INSERT IGNORE INTO tbl_produk
 (id_kategori, id_merk, sku, barcode, nama_produk, harga_beli, harga_jual, stok, satuan, stok_minimum)
SELECT k.id_kategori, m.id_merk, 'WM-MNM-002', '8991002102132', 'Kopi Bubuk 180 g', 14000, 18000, 20, 'pack', 5
FROM tbl_kategori k JOIN tbl_merk m ON m.nama_merk='Kapal Api'
WHERE k.nama_kategori='Minuman';

INSERT IGNORE INTO tbl_produk
 (id_kategori, id_merk, sku, barcode, nama_produk, harga_beli, harga_jual, stok, satuan, stok_minimum)
SELECT k.id_kategori, m.id_merk, 'WM-MNM-003', '8998009010613', 'Ultra Milk Fullcream 1000ml', 17000, 21000, 20, 'kotak', 5
FROM tbl_kategori k JOIN tbl_merk m ON m.nama_merk='Ultra Milk'
WHERE k.nama_kategori='Minuman';

INSERT IGNORE INTO tbl_produk
 (id_kategori, id_merk, sku, barcode, nama_produk, harga_beli, harga_jual, stok, satuan, stok_minimum)
SELECT k.id_kategori, m.id_merk, 'WM-PRW-001', '8998866618243', 'Giv White - Mulberry & Collagen 400ml', 3500, 5000, 20, 'pcs', 5
FROM tbl_kategori k JOIN tbl_merk m ON m.nama_merk='Lifebuoy'
WHERE k.nama_kategori='Perawatan Diri';

INSERT IGNORE INTO tbl_produk
 (id_kategori, id_merk, sku, barcode, nama_produk, harga_beli, harga_jual, stok, satuan, stok_minimum)
SELECT k.id_kategori, m.id_merk, 'WM-KBR-001', '8999999615482', 'Sunlight Pencuci Piring 700 ml', 14000, 17000, 20, 'botol', 5
FROM tbl_kategori k JOIN tbl_merk m ON m.nama_merk='Sunlight'
WHERE k.nama_kategori='Kebersihan Rumah';

-- Dashboard: katalog produk aktif, stok menipis, dan rekap penjualan harian.
CREATE OR REPLACE VIEW view_katalog_produk AS
SELECT p.id_produk, p.sku, p.barcode, p.nama_produk,
       k.nama_kategori, m.nama_merk, p.harga_jual, p.stok,
       p.satuan, p.stok_minimum, p.gambar, p.status_produk
FROM tbl_produk p
JOIN tbl_kategori k ON k.id_kategori = p.id_kategori
LEFT JOIN tbl_merk m ON m.id_merk = p.id_merk;

CREATE OR REPLACE VIEW view_stok_menipis AS
SELECT p.id_produk, p.sku, p.nama_produk, k.nama_kategori,
       p.stok, p.stok_minimum, p.satuan
FROM tbl_produk p
JOIN tbl_kategori k ON k.id_kategori = p.id_kategori
WHERE p.status_produk = 'aktif' AND p.stok <= p.stok_minimum;

CREATE OR REPLACE VIEW view_laporan_penjualan_harian AS
SELECT DATE(tanggal_transaksi) AS tanggal,
       COUNT(*) AS jumlah_transaksi,
       SUM(CASE WHEN status_pembayaran='lunas' THEN total_belanja ELSE 0 END) AS total_penjualan
FROM tbl_transaksi
WHERE status_pesanan <> 'dibatalkan'
GROUP BY DATE(tanggal_transaksi);

-- Pemeriksaan setelah import:
-- SHOW TABLES;
-- SELECT * FROM tbl_pengguna;
-- SELECT * FROM tbl_kategori;
-- SELECT * FROM tbl_merk;
-- SELECT * FROM view_katalog_produk;

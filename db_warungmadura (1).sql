-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2026 at 05:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_warungmadura`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_detail_keranjang`
--

CREATE TABLE `tbl_detail_keranjang` (
  `id_detail_keranjang` bigint(20) UNSIGNED NOT NULL,
  `id_keranjang` bigint(20) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_detail_penerimaan_barang`
--

CREATE TABLE `tbl_detail_penerimaan_barang` (
  `id_detail_penerimaan` bigint(20) UNSIGNED NOT NULL,
  `id_penerimaan` bigint(20) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `harga_beli` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_detail_transaksi`
--

CREATE TABLE `tbl_detail_transaksi` (
  `id_detail` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `nama_produk` varchar(160) NOT NULL,
  `sku_produk` varchar(50) DEFAULT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `harga_satuan` decimal(14,2) NOT NULL,
  `diskon_item` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal_item` decimal(14,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_detail_transaksi`
--

INSERT INTO `tbl_detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `nama_produk`, `sku_produk`, `jumlah`, `harga_satuan`, `diskon_item`, `subtotal_item`) VALUES
(1, 1, 1, 'Beras Premium 5 Kg', 'WM-SMB-001', 1, 75000.00, 0.00, 75000.00),
(2, 1, 3, 'Gula Pasir 1 Kg', 'WM-SMB-003', 1, 18000.00, 0.00, 18000.00),
(3, 1, 9, 'Sabun Mandi Cair 250 ml', 'WM-PRW-001', 1, 5000.00, 0.00, 5000.00),
(4, 1, 2, 'Minyak Goreng 1 Liter', 'WM-SMB-002', 2, 23000.00, 0.00, 46000.00),
(5, 1, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 1, 18000.00, 0.00, 18000.00),
(6, 2, 9, 'Sabun Mandi Cair 250 ml', 'WM-PRW-001', 1, 5000.00, 0.00, 5000.00),
(7, 2, 3, 'Gula Pasir 1 Kg', 'WM-SMB-003', 1, 18000.00, 0.00, 18000.00),
(8, 2, 2, 'Minyak Goreng 1 Liter', 'WM-SMB-002', 1, 23000.00, 0.00, 23000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kategori`
--

CREATE TABLE `tbl_kategori` (
  `id_kategori` int(10) UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `status_kategori` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_kategori`
--

INSERT INTO `tbl_kategori` (`id_kategori`, `nama_kategori`, `deskripsi`, `status_kategori`, `created_at`) VALUES
(1, 'Sembako', 'Beras, minyak, gula, tepung, dan kebutuhan pokok', 'aktif', '2026-11-11 08:39:50'),
(2, 'Makanan & Mi Instan', 'Makanan ringan, mi instan, dan makanan kemasan', 'aktif', '2026-11-11 08:39:50'),
(3, 'Minuman', 'Air mineral, teh, kopi, susu, dan minuman kemasan', 'aktif', '2026-11-11 08:39:50'),
(4, 'Perawatan Diri', 'Sabun, sampo, pasta gigi, dan kebutuhan pribadi', 'aktif', '2026-11-11 08:39:50'),
(5, 'Kebersihan Rumah', 'Detergen, cairan pencuci, dan perlengkapan kebersihan', 'aktif', '2026-11-11 08:39:50'),
(6, 'Perlengkapan Bayi', 'Popok, tisu bayi, dan kebutuhan bayi', 'aktif', '2026-11-11 08:39:50'),
(7, 'Alat Tulis', 'Buku, pulpen, pensil, dan perlengkapan sekolah', 'aktif', '2026-11-11 08:39:50'),
(8, 'Frozen Food', 'Makanan beku dan produk siap masak', 'aktif', '2026-11-11 08:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_keranjang`
--

CREATE TABLE `tbl_keranjang` (
  `id_keranjang` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` int(10) UNSIGNED NOT NULL,
  `status_keranjang` enum('aktif','checkout','dibatalkan') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_log_aktivitas`
--

CREATE TABLE `tbl_log_aktivitas` (
  `id_log` bigint(20) UNSIGNED NOT NULL,
  `id_pengguna` int(10) UNSIGNED DEFAULT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `nama_tabel` varchar(80) DEFAULT NULL,
  `id_data` bigint(20) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `alamat_ip` varchar(45) DEFAULT NULL,
  `waktu_aktivitas` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_log_aktivitas`
--

INSERT INTO `tbl_log_aktivitas` (`id_log`, `id_pengguna`, `aktivitas`, `nama_tabel`, `id_data`, `keterangan`, `alamat_ip`, `waktu_aktivitas`) VALUES
(1, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-11 09:03:03'),
(2, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-11 09:33:58'),
(3, 1, 'logout', 'tbl_pengguna', 1, 'Pengguna logout', '::1', '2026-11-11 09:38:02'),
(4, 2, 'login', 'tbl_pengguna', 2, 'Pengguna berhasil login', '::1', '2026-11-11 09:38:17'),
(5, 2, 'logout', 'tbl_pengguna', 2, 'Pengguna logout', '::1', '2026-11-11 09:38:31'),
(6, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-11 09:42:18'),
(7, 1, 'edit_produk', 'tbl_produk', 10, 'Admin mengubah produk Sunlight Botol 675gr', '::1', '2026-11-11 10:54:16'),
(8, 1, 'edit_produk', 'tbl_produk', 10, 'Admin mengubah produk Sunlight Botol 675gr', '::1', '2026-11-11 11:02:10'),
(9, 1, 'checkout', 'tbl_transaksi', 1, 'Transaksi WM-261111-110517-554', '::1', '2026-11-11 11:05:17'),
(10, 1, 'checkout', 'tbl_transaksi', 2, 'Transaksi WM-261111-113015-328', '::1', '2026-11-11 11:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_merk`
--

CREATE TABLE `tbl_merk` (
  `id_merk` int(10) UNSIGNED NOT NULL,
  `nama_merk` varchar(100) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `status_merk` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_merk`
--

INSERT INTO `tbl_merk` (`id_merk`, `nama_merk`, `deskripsi`, `status_merk`, `created_at`) VALUES
(1, 'Tanpa Merek', 'Produk curah atau produk tanpa merek khusus', 'aktif', '2026-11-11 08:39:50'),
(2, 'Indomie', 'Merek makanan dan mi instan', 'aktif', '2026-11-11 08:39:50'),
(3, 'Bimoli', 'Merek minyak goreng', 'aktif', '2026-11-11 08:39:50'),
(4, 'Gulaku', 'Merek gula', 'aktif', '2026-11-11 08:39:50'),
(5, 'Segitiga Biru', 'Merek tepung terigu', 'aktif', '2026-11-11 08:39:50'),
(6, 'SariWangi', 'Merek teh', 'aktif', '2026-11-11 08:39:50'),
(7, 'Kapal Api', 'Merek kopi', 'aktif', '2026-11-11 08:39:50'),
(8, 'Ultra Milk', 'Merek susu UHT', 'aktif', '2026-11-11 08:39:50'),
(9, 'Lifebuoy', 'Merek perawatan diri', 'aktif', '2026-11-11 08:39:50'),
(10, 'Sunlight', 'Merek produk pencuci', 'aktif', '2026-11-11 08:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mutasi_saldo`
--

CREATE TABLE `tbl_mutasi_saldo` (
  `id_mutasi` bigint(20) UNSIGNED NOT NULL,
  `id_saldo` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `id_pengguna` int(10) UNSIGNED DEFAULT NULL,
  `jenis_mutasi` enum('topup_simulasi','pembayaran','refund','penyesuaian') NOT NULL,
  `arah` enum('masuk','keluar') NOT NULL,
  `jumlah` decimal(14,2) NOT NULL,
  `saldo_sebelum` decimal(14,2) NOT NULL,
  `saldo_sesudah` decimal(14,2) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `waktu_mutasi` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mutasi_stok`
--

CREATE TABLE `tbl_mutasi_stok` (
  `id_mutasi_stok` bigint(20) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `id_pengguna` int(10) UNSIGNED DEFAULT NULL,
  `id_transaksi` bigint(20) UNSIGNED DEFAULT NULL,
  `id_penerimaan` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis_mutasi` enum('stok_masuk','penjualan','penyesuaian','retur','pembatalan') NOT NULL,
  `jumlah` int(11) NOT NULL,
  `stok_sebelum` int(11) NOT NULL,
  `stok_sesudah` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `waktu_mutasi` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_mutasi_stok`
--

INSERT INTO `tbl_mutasi_stok` (`id_mutasi_stok`, `id_produk`, `id_pengguna`, `id_transaksi`, `id_penerimaan`, `jenis_mutasi`, `jumlah`, `stok_sebelum`, `stok_sesudah`, `keterangan`, `waktu_mutasi`) VALUES
(1, 1, 1, 1, NULL, 'penjualan', -1, 20, 19, 'Penjualan kasir', '2026-11-11 11:05:17'),
(2, 3, 1, 1, NULL, 'penjualan', -1, 20, 19, 'Penjualan kasir', '2026-11-11 11:05:17'),
(3, 9, 1, 1, NULL, 'penjualan', -1, 20, 19, 'Penjualan kasir', '2026-11-11 11:05:17'),
(4, 2, 1, 1, NULL, 'penjualan', -2, 20, 18, 'Penjualan kasir', '2026-11-11 11:05:17'),
(5, 7, 1, 1, NULL, 'penjualan', -1, 20, 19, 'Penjualan kasir', '2026-11-11 11:05:17'),
(6, 9, 1, 2, NULL, 'penjualan', -1, 19, 18, 'Penjualan kasir', '2026-11-11 11:30:15'),
(7, 3, 1, 2, NULL, 'penjualan', -1, 19, 18, 'Penjualan kasir', '2026-11-11 11:30:15'),
(8, 2, 1, 2, NULL, 'penjualan', -1, 18, 17, 'Penjualan kasir', '2026-11-11 11:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pelanggan`
--

CREATE TABLE `tbl_pelanggan` (
  `id_pelanggan` int(10) UNSIGNED NOT NULL,
  `id_pengguna` int(10) UNSIGNED DEFAULT NULL,
  `no_member` varchar(30) NOT NULL,
  `rfid_uid` varchar(80) DEFAULT NULL,
  `nama_pelanggan` varchar(120) NOT NULL,
  `no_hp` varchar(25) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(80) DEFAULT NULL,
  `status_member` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pembayaran`
--

CREATE TABLE `tbl_pembayaran` (
  `id_pembayaran` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `metode_pembayaran` enum('tunai','tabungan','ewallet','qris_dana','duitku') NOT NULL,
  `kode_metode_gateway` varchar(10) DEFAULT NULL,
  `jumlah_bayar` decimal(14,2) NOT NULL,
  `status_pembayaran` enum('menunggu','berhasil','gagal','dikembalikan') NOT NULL DEFAULT 'menunggu',
  `referensi_simulasi` varchar(80) DEFAULT NULL,
  `url_pembayaran` varchar(500) DEFAULT NULL,
  `va_number` varchar(80) DEFAULT NULL,
  `diproses_oleh` int(10) UNSIGNED DEFAULT NULL,
  `waktu_pembayaran` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pembayaran`
--

INSERT INTO `tbl_pembayaran` (`id_pembayaran`, `id_transaksi`, `metode_pembayaran`, `jumlah_bayar`, `status_pembayaran`, `referensi_simulasi`, `diproses_oleh`, `waktu_pembayaran`, `created_at`) VALUES
(1, 1, 'tunai', 200000.00, 'berhasil', 'SIM-A069F2BA', 1, '2026-11-11 11:05:17', '2026-11-11 11:05:17'),
(2, 2, 'tunai', 50000.00, 'berhasil', 'SIM-944BBC88', 1, '2026-11-11 11:30:15', '2026-11-11 11:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penerimaan_barang`
--

CREATE TABLE `tbl_penerimaan_barang` (
  `id_penerimaan` bigint(20) UNSIGNED NOT NULL,
  `no_penerimaan` varchar(50) NOT NULL,
  `id_supplier` int(10) UNSIGNED DEFAULT NULL,
  `id_pengguna` int(10) UNSIGNED NOT NULL,
  `status_penerimaan` enum('draft','diterima','dibatalkan') NOT NULL DEFAULT 'draft',
  `waktu_penerimaan` datetime NOT NULL DEFAULT current_timestamp(),
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengguna`
--

CREATE TABLE `tbl_pengguna` (
  `id_pengguna` int(10) UNSIGNED NOT NULL,
  `nama_lengkap` varchar(120) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `no_hp` varchar(25) DEFAULT NULL,
  `role` enum('admin','kasir','pelanggan') NOT NULL DEFAULT 'pelanggan',
  `status_pengguna` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `terakhir_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pengguna`
--

INSERT INTO `tbl_pengguna` (`id_pengguna`, `nama_lengkap`, `username`, `email`, `password_hash`, `no_hp`, `role`, `status_pengguna`, `terakhir_login`, `created_at`, `updated_at`) VALUES
(1, 'Administrator WarungMadura', 'admin', 'admin@warungmadura.local', '$2y$12$kr6NHk5sw24.FAQdtj0Dyuyd2s1kFOVtCKAZsZNV1cUEw9soZMOAm', NULL, 'admin', 'aktif', '2026-11-11 09:42:18', '2026-11-11 08:39:50', '2026-11-11 09:42:18'),
(2, 'Kasir WarungMadura', 'kasir', 'kasir@warungmadura.local', '$2y$12$0gthfnBMc3oiShtTQv36N.o/hCmjH5e26nG5AFVpjSg4YLgsa03T6', NULL, 'kasir', 'aktif', '2026-11-11 09:38:17', '2026-11-11 08:39:50', '2026-11-11 09:38:17');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_produk`
--

CREATE TABLE `tbl_produk` (
  `id_produk` int(10) UNSIGNED NOT NULL,
  `id_kategori` int(10) UNSIGNED NOT NULL,
  `id_merk` int(10) UNSIGNED DEFAULT NULL,
  `sku` varchar(50) NOT NULL,
  `barcode` varchar(80) DEFAULT NULL,
  `nama_produk` varchar(160) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga_beli` decimal(14,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(14,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `satuan` varchar(30) NOT NULL DEFAULT 'pcs',
  `stok_minimum` int(10) UNSIGNED NOT NULL DEFAULT 5,
  `gambar` varchar(255) DEFAULT NULL,
  `status_produk` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_produk`
--

INSERT INTO `tbl_produk` (`id_produk`, `id_kategori`, `id_merk`, `sku`, `barcode`, `nama_produk`, `deskripsi`, `harga_beli`, `harga_jual`, `stok`, `satuan`, `stok_minimum`, `gambar`, `status_produk`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'WM-SMB-001', NULL, 'Beras Premium 5 Kg', NULL, 68000.00, 75000.00, 19, 'pack', 5, NULL, 'aktif', '2026-11-11 08:39:50', '2026-11-11 11:05:17'),
(2, 1, 3, 'WM-SMB-002', NULL, 'Minyak Goreng 1 Liter', NULL, 19000.00, 23000.00, 17, 'botol', 5, NULL, 'aktif', '2026-11-11 08:39:50', '2026-11-11 11:30:15'),
(3, 1, 4, 'WM-SMB-003', NULL, 'Gula Pasir 1 Kg', NULL, 15000.00, 18000.00, 18, 'pack', 5, NULL, 'aktif', '2026-11-11 08:39:50', '2026-11-11 11:30:15'),
(4, 1, 5, 'WM-SMB-004', NULL, 'Tepung Terigu 1 Kg', NULL, 11000.00, 13000.00, 20, 'pack', 5, NULL, 'aktif', '2026-11-11 08:39:50', NULL),
(5, 2, 2, 'WM-MKN-001', '5285000390602', 'IndoMie Goreng', NULL, 2700.00, 3500.00, 50, 'pcs', 10, NULL, 'aktif', '2026-11-11 08:39:50', NULL),
(6, 3, 6, 'WM-MNM-001', '8886007811113', 'Teh Poci Celup Isi 25', NULL, 9000.00, 12000.00, 20, 'box', 5, NULL, 'aktif', '2026-11-11 08:39:50', NULL),
(7, 3, 7, 'WM-MNM-002', '8991002102132', 'Kopi Bubuk 180 g', NULL, 14000.00, 18000.00, 19, 'pack', 5, NULL, 'aktif', '2026-11-11 08:39:50', '2026-11-11 11:05:17'),
(8, 3, 8, 'WM-MNM-003', '8998009010613', 'Ultra Milk Fullcream 1000ml', NULL, 17000.00, 21000.00, 20, 'kotak', 5, NULL, 'aktif', '2026-11-11 08:39:50', NULL),
(9, 4, 9, 'WM-PRW-001', '8998866618243', 'Giv White - Mulberry & Collagen 400ml', NULL, 3500.00, 5000.00, 18, 'pcs', 5, NULL, 'aktif', '2026-11-11 08:39:50', '2026-11-11 11:30:15'),
(10, 5, NULL, 'WM-KBR-001', '8999999615482', 'Sunlight Pencuci Piring 700 ml', NULL, 14000.00, 17501.00, 20, 'botol', 5, 'uploads/products/7cd1a2592baf1b5f4473d327ab693beb.jpg', 'aktif', '2026-11-11 08:39:50', '2026-11-11 10:54:16');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_saldo_pelanggan`
--

CREATE TABLE `tbl_saldo_pelanggan` (
  `id_saldo` bigint(20) UNSIGNED NOT NULL,
  `id_pelanggan` int(10) UNSIGNED NOT NULL,
  `jenis_saldo` enum('tabungan','ewallet') NOT NULL,
  `saldo` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status_saldo` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_supplier`
--

CREATE TABLE `tbl_supplier` (
  `id_supplier` int(10) UNSIGNED NOT NULL,
  `kode_supplier` varchar(30) NOT NULL,
  `nama_supplier` varchar(120) NOT NULL,
  `nama_kontak` varchar(100) DEFAULT NULL,
  `no_hp` varchar(25) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status_supplier` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_supplier`
--

INSERT INTO `tbl_supplier` (`id_supplier`, `kode_supplier`, `nama_supplier`, `nama_kontak`, `no_hp`, `email`, `alamat`, `status_supplier`, `created_at`) VALUES
(1, 'SUP-001', 'Supplier Umum WarungMadura', NULL, NULL, NULL, NULL, 'aktif', '2026-11-11 08:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transaksi`
--

CREATE TABLE `tbl_transaksi` (
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `kode_transaksi` varchar(40) NOT NULL,
  `id_pelanggan` int(10) UNSIGNED DEFAULT NULL,
  `id_kasir` int(10) UNSIGNED DEFAULT NULL,
  `jenis_transaksi` enum('online','toko') NOT NULL DEFAULT 'toko',
  `status_pesanan` enum('menunggu','diproses','siap_diambil','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  `status_pembayaran` enum('belum_bayar','menunggu','lunas','gagal','dikembalikan') NOT NULL DEFAULT 'belum_bayar',
  `metode_pembayaran` enum('tunai','tabungan','ewallet','qris_dana','duitku') DEFAULT NULL,
  `metode_penerimaan` enum('ambil_toko','diantar') NOT NULL DEFAULT 'ambil_toko',
  `tanggal_transaksi` datetime NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `diskon` decimal(14,2) NOT NULL DEFAULT 0.00,
  `biaya_pengiriman` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_belanja` decimal(14,2) NOT NULL DEFAULT 0.00,
  `jumlah_bayar` decimal(14,2) NOT NULL DEFAULT 0.00,
  `kembalian` decimal(14,2) NOT NULL DEFAULT 0.00,
  `nama_penerima` varchar(120) DEFAULT NULL,
  `no_hp_penerima` varchar(25) DEFAULT NULL,
  `alamat_pengiriman` text DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_transaksi`
--

INSERT INTO `tbl_transaksi` (`id_transaksi`, `kode_transaksi`, `id_pelanggan`, `id_kasir`, `jenis_transaksi`, `status_pesanan`, `status_pembayaran`, `metode_pembayaran`, `metode_penerimaan`, `tanggal_transaksi`, `subtotal`, `diskon`, `biaya_pengiriman`, `total_belanja`, `jumlah_bayar`, `kembalian`, `nama_penerima`, `no_hp_penerima`, `alamat_pengiriman`, `catatan`) VALUES
(1, 'WM-261111-110517-554', NULL, 1, 'toko', 'selesai', 'lunas', 'tunai', 'ambil_toko', '2026-11-11 11:05:17', 162000.00, 0.00, 0.00, 162000.00, 200000.00, 38000.00, NULL, NULL, NULL, NULL),
(2, 'WM-261111-113015-328', NULL, 1, 'toko', 'selesai', 'lunas', 'tunai', 'ambil_toko', '2026-11-11 11:30:15', 46000.00, 0.00, 0.00, 46000.00, 50000.00, 4000.00, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_katalog_produk`
-- (See below for the actual view)
--
CREATE TABLE `view_katalog_produk` (
`id_produk` int(10) unsigned
,`sku` varchar(50)
,`barcode` varchar(80)
,`nama_produk` varchar(160)
,`nama_kategori` varchar(100)
,`nama_merk` varchar(100)
,`harga_jual` decimal(14,2)
,`stok` int(11)
,`satuan` varchar(30)
,`stok_minimum` int(10) unsigned
,`gambar` varchar(255)
,`status_produk` enum('aktif','nonaktif')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_laporan_penjualan_harian`
-- (See below for the actual view)
--
CREATE TABLE `view_laporan_penjualan_harian` (
`tanggal` date
,`jumlah_transaksi` bigint(21)
,`total_penjualan` decimal(36,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_stok_menipis`
-- (See below for the actual view)
--
CREATE TABLE `view_stok_menipis` (
`id_produk` int(10) unsigned
,`sku` varchar(50)
,`nama_produk` varchar(160)
,`nama_kategori` varchar(100)
,`stok` int(11)
,`stok_minimum` int(10) unsigned
,`satuan` varchar(30)
);

-- --------------------------------------------------------

--
-- Structure for view `view_katalog_produk`
--
DROP TABLE IF EXISTS `view_katalog_produk`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_katalog_produk`  AS SELECT `p`.`id_produk` AS `id_produk`, `p`.`sku` AS `sku`, `p`.`barcode` AS `barcode`, `p`.`nama_produk` AS `nama_produk`, `k`.`nama_kategori` AS `nama_kategori`, `m`.`nama_merk` AS `nama_merk`, `p`.`harga_jual` AS `harga_jual`, `p`.`stok` AS `stok`, `p`.`satuan` AS `satuan`, `p`.`stok_minimum` AS `stok_minimum`, `p`.`gambar` AS `gambar`, `p`.`status_produk` AS `status_produk` FROM ((`tbl_produk` `p` join `tbl_kategori` `k` on(`k`.`id_kategori` = `p`.`id_kategori`)) left join `tbl_merk` `m` on(`m`.`id_merk` = `p`.`id_merk`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_laporan_penjualan_harian`
--
DROP TABLE IF EXISTS `view_laporan_penjualan_harian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_laporan_penjualan_harian`  AS SELECT cast(`tbl_transaksi`.`tanggal_transaksi` as date) AS `tanggal`, count(0) AS `jumlah_transaksi`, sum(case when `tbl_transaksi`.`status_pembayaran` = 'lunas' then `tbl_transaksi`.`total_belanja` else 0 end) AS `total_penjualan` FROM `tbl_transaksi` WHERE `tbl_transaksi`.`status_pesanan` <> 'dibatalkan' GROUP BY cast(`tbl_transaksi`.`tanggal_transaksi` as date) ;

-- --------------------------------------------------------

--
-- Structure for view `view_stok_menipis`
--
DROP TABLE IF EXISTS `view_stok_menipis`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_stok_menipis`  AS SELECT `p`.`id_produk` AS `id_produk`, `p`.`sku` AS `sku`, `p`.`nama_produk` AS `nama_produk`, `k`.`nama_kategori` AS `nama_kategori`, `p`.`stok` AS `stok`, `p`.`stok_minimum` AS `stok_minimum`, `p`.`satuan` AS `satuan` FROM (`tbl_produk` `p` join `tbl_kategori` `k` on(`k`.`id_kategori` = `p`.`id_kategori`)) WHERE `p`.`status_produk` = 'aktif' AND `p`.`stok` <= `p`.`stok_minimum` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_detail_keranjang`
--
ALTER TABLE `tbl_detail_keranjang`
  ADD PRIMARY KEY (`id_detail_keranjang`),
  ADD UNIQUE KEY `uq_keranjang_produk` (`id_keranjang`,`id_produk`),
  ADD KEY `fk_detail_keranjang_produk` (`id_produk`);

--
-- Indexes for table `tbl_detail_penerimaan_barang`
--
ALTER TABLE `tbl_detail_penerimaan_barang`
  ADD PRIMARY KEY (`id_detail_penerimaan`),
  ADD KEY `fk_detail_penerimaan_header` (`id_penerimaan`),
  ADD KEY `idx_detail_penerimaan_produk` (`id_produk`);

--
-- Indexes for table `tbl_detail_transaksi`
--
ALTER TABLE `tbl_detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_detail_transaksi_header` (`id_transaksi`),
  ADD KEY `idx_detail_produk` (`id_produk`);

--
-- Indexes for table `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `tbl_keranjang`
--
ALTER TABLE `tbl_keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `idx_keranjang_pelanggan_status` (`id_pelanggan`,`status_keranjang`);

--
-- Indexes for table `tbl_log_aktivitas`
--
ALTER TABLE `tbl_log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_log_pengguna` (`id_pengguna`),
  ADD KEY `idx_log_waktu` (`waktu_aktivitas`),
  ADD KEY `idx_log_aktivitas` (`aktivitas`);

--
-- Indexes for table `tbl_merk`
--
ALTER TABLE `tbl_merk`
  ADD PRIMARY KEY (`id_merk`),
  ADD UNIQUE KEY `nama_merk` (`nama_merk`);

--
-- Indexes for table `tbl_mutasi_saldo`
--
ALTER TABLE `tbl_mutasi_saldo`
  ADD PRIMARY KEY (`id_mutasi`),
  ADD KEY `fk_mutasi_saldo_saldo` (`id_saldo`),
  ADD KEY `fk_mutasi_saldo_transaksi` (`id_transaksi`),
  ADD KEY `fk_mutasi_saldo_pengguna` (`id_pengguna`),
  ADD KEY `idx_mutasi_saldo_waktu` (`waktu_mutasi`);

--
-- Indexes for table `tbl_mutasi_stok`
--
ALTER TABLE `tbl_mutasi_stok`
  ADD PRIMARY KEY (`id_mutasi_stok`),
  ADD KEY `fk_mutasi_stok_pengguna` (`id_pengguna`),
  ADD KEY `fk_mutasi_stok_transaksi` (`id_transaksi`),
  ADD KEY `fk_mutasi_stok_penerimaan` (`id_penerimaan`),
  ADD KEY `idx_mutasi_stok_waktu` (`waktu_mutasi`),
  ADD KEY `idx_mutasi_stok_produk` (`id_produk`);

--
-- Indexes for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `no_member` (`no_member`),
  ADD UNIQUE KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `idx_pelanggan_nama` (`nama_pelanggan`),
  ADD KEY `idx_pelanggan_hp` (`no_hp`);

--
-- Indexes for table `tbl_pembayaran`
--
ALTER TABLE `tbl_pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `fk_pembayaran_pengguna` (`diproses_oleh`);

--
-- Indexes for table `tbl_penerimaan_barang`
--
ALTER TABLE `tbl_penerimaan_barang`
  ADD PRIMARY KEY (`id_penerimaan`),
  ADD UNIQUE KEY `no_penerimaan` (`no_penerimaan`),
  ADD KEY `fk_penerimaan_supplier` (`id_supplier`),
  ADD KEY `fk_penerimaan_pengguna` (`id_pengguna`),
  ADD KEY `idx_penerimaan_waktu` (`waktu_penerimaan`);

--
-- Indexes for table `tbl_pengguna`
--
ALTER TABLE `tbl_pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_pengguna_role_status` (`role`,`status_pengguna`);

--
-- Indexes for table `tbl_produk`
--
ALTER TABLE `tbl_produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `fk_produk_kategori` (`id_kategori`),
  ADD KEY `fk_produk_merk` (`id_merk`),
  ADD KEY `idx_produk_nama` (`nama_produk`),
  ADD KEY `idx_produk_status` (`status_produk`),
  ADD KEY `idx_produk_stok` (`stok`);

--
-- Indexes for table `tbl_saldo_pelanggan`
--
ALTER TABLE `tbl_saldo_pelanggan`
  ADD PRIMARY KEY (`id_saldo`),
  ADD UNIQUE KEY `uq_pelanggan_jenis_saldo` (`id_pelanggan`,`jenis_saldo`);

--
-- Indexes for table `tbl_supplier`
--
ALTER TABLE `tbl_supplier`
  ADD PRIMARY KEY (`id_supplier`),
  ADD UNIQUE KEY `kode_supplier` (`kode_supplier`),
  ADD KEY `idx_supplier_nama` (`nama_supplier`);

--
-- Indexes for table `tbl_transaksi`
--
ALTER TABLE `tbl_transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `fk_transaksi_kasir` (`id_kasir`),
  ADD KEY `idx_transaksi_tanggal` (`tanggal_transaksi`),
  ADD KEY `idx_transaksi_status` (`status_pesanan`,`status_pembayaran`),
  ADD KEY `idx_transaksi_pelanggan` (`id_pelanggan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_detail_keranjang`
--
ALTER TABLE `tbl_detail_keranjang`
  MODIFY `id_detail_keranjang` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_detail_penerimaan_barang`
--
ALTER TABLE `tbl_detail_penerimaan_barang`
  MODIFY `id_detail_penerimaan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_detail_transaksi`
--
ALTER TABLE `tbl_detail_transaksi`
  MODIFY `id_detail` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  MODIFY `id_kategori` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_keranjang`
--
ALTER TABLE `tbl_keranjang`
  MODIFY `id_keranjang` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_log_aktivitas`
--
ALTER TABLE `tbl_log_aktivitas`
  MODIFY `id_log` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_merk`
--
ALTER TABLE `tbl_merk`
  MODIFY `id_merk` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_mutasi_saldo`
--
ALTER TABLE `tbl_mutasi_saldo`
  MODIFY `id_mutasi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_mutasi_stok`
--
ALTER TABLE `tbl_mutasi_stok`
  MODIFY `id_mutasi_stok` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  MODIFY `id_pelanggan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_pembayaran`
--
ALTER TABLE `tbl_pembayaran`
  MODIFY `id_pembayaran` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_penerimaan_barang`
--
ALTER TABLE `tbl_penerimaan_barang`
  MODIFY `id_penerimaan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_pengguna`
--
ALTER TABLE `tbl_pengguna`
  MODIFY `id_pengguna` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_produk`
--
ALTER TABLE `tbl_produk`
  MODIFY `id_produk` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_saldo_pelanggan`
--
ALTER TABLE `tbl_saldo_pelanggan`
  MODIFY `id_saldo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_supplier`
--
ALTER TABLE `tbl_supplier`
  MODIFY `id_supplier` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_transaksi`
--
ALTER TABLE `tbl_transaksi`
  MODIFY `id_transaksi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_detail_keranjang`
--
ALTER TABLE `tbl_detail_keranjang`
  ADD CONSTRAINT `fk_detail_keranjang_keranjang` FOREIGN KEY (`id_keranjang`) REFERENCES `tbl_keranjang` (`id_keranjang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_keranjang_produk` FOREIGN KEY (`id_produk`) REFERENCES `tbl_produk` (`id_produk`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_detail_penerimaan_barang`
--
ALTER TABLE `tbl_detail_penerimaan_barang`
  ADD CONSTRAINT `fk_detail_penerimaan_header` FOREIGN KEY (`id_penerimaan`) REFERENCES `tbl_penerimaan_barang` (`id_penerimaan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_penerimaan_produk` FOREIGN KEY (`id_produk`) REFERENCES `tbl_produk` (`id_produk`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_detail_transaksi`
--
ALTER TABLE `tbl_detail_transaksi`
  ADD CONSTRAINT `fk_detail_transaksi_header` FOREIGN KEY (`id_transaksi`) REFERENCES `tbl_transaksi` (`id_transaksi`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_transaksi_produk` FOREIGN KEY (`id_produk`) REFERENCES `tbl_produk` (`id_produk`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_keranjang`
--
ALTER TABLE `tbl_keranjang`
  ADD CONSTRAINT `fk_keranjang_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_log_aktivitas`
--
ALTER TABLE `tbl_log_aktivitas`
  ADD CONSTRAINT `fk_log_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tbl_pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tbl_mutasi_saldo`
--
ALTER TABLE `tbl_mutasi_saldo`
  ADD CONSTRAINT `fk_mutasi_saldo_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tbl_pengguna` (`id_pengguna`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mutasi_saldo_saldo` FOREIGN KEY (`id_saldo`) REFERENCES `tbl_saldo_pelanggan` (`id_saldo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mutasi_saldo_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `tbl_transaksi` (`id_transaksi`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_mutasi_stok`
--
ALTER TABLE `tbl_mutasi_stok`
  ADD CONSTRAINT `fk_mutasi_stok_penerimaan` FOREIGN KEY (`id_penerimaan`) REFERENCES `tbl_penerimaan_barang` (`id_penerimaan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mutasi_stok_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tbl_pengguna` (`id_pengguna`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mutasi_stok_produk` FOREIGN KEY (`id_produk`) REFERENCES `tbl_produk` (`id_produk`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mutasi_stok_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `tbl_transaksi` (`id_transaksi`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  ADD CONSTRAINT `fk_pelanggan_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tbl_pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tbl_pembayaran`
--
ALTER TABLE `tbl_pembayaran`
  ADD CONSTRAINT `fk_pembayaran_pengguna` FOREIGN KEY (`diproses_oleh`) REFERENCES `tbl_pengguna` (`id_pengguna`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pembayaran_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `tbl_transaksi` (`id_transaksi`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_penerimaan_barang`
--
ALTER TABLE `tbl_penerimaan_barang`
  ADD CONSTRAINT `fk_penerimaan_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tbl_pengguna` (`id_pengguna`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penerimaan_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `tbl_supplier` (`id_supplier`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_produk`
--
ALTER TABLE `tbl_produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `tbl_kategori` (`id_kategori`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produk_merk` FOREIGN KEY (`id_merk`) REFERENCES `tbl_merk` (`id_merk`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_saldo_pelanggan`
--
ALTER TABLE `tbl_saldo_pelanggan`
  ADD CONSTRAINT `fk_saldo_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_transaksi`
--
ALTER TABLE `tbl_transaksi`
  ADD CONSTRAINT `fk_transaksi_kasir` FOREIGN KEY (`id_kasir`) REFERENCES `tbl_pengguna` (`id_pengguna`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

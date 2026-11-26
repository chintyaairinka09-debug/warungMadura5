-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2026 at 04:19 AM
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

--
-- Dumping data for table `tbl_detail_penerimaan_barang`
--

INSERT INTO `tbl_detail_penerimaan_barang` (`id_detail_penerimaan`, `id_penerimaan`, `id_produk`, `jumlah`, `harga_beli`, `subtotal`) VALUES
(1, 1, 14, 1, 0.00, 0.00),
(2, 2, 14, 10, 35000.00, 350000.00);

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
  `harga_modal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal_item` decimal(14,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_detail_transaksi`
--

INSERT INTO `tbl_detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `nama_produk`, `sku_produk`, `jumlah`, `harga_satuan`, `diskon_item`, `harga_modal`, `subtotal_item`) VALUES
(1, 1, 1, 'Beras Premium 5 Kg', 'WM-SMB-001', 1, 75000.00, 0.00, 0.00, 75000.00),
(2, 1, 3, 'Gula Pasir 1 Kg', 'WM-SMB-003', 1, 18000.00, 0.00, 0.00, 18000.00),
(3, 1, 9, 'Sabun Mandi Cair 250 ml', 'WM-PRW-001', 1, 5000.00, 0.00, 0.00, 5000.00),
(4, 1, 2, 'Minyak Goreng 1 Liter', 'WM-SMB-002', 2, 23000.00, 0.00, 0.00, 46000.00),
(5, 1, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 1, 18000.00, 0.00, 0.00, 18000.00),
(6, 2, 9, 'Sabun Mandi Cair 250 ml', 'WM-PRW-001', 1, 5000.00, 0.00, 0.00, 5000.00),
(7, 2, 3, 'Gula Pasir 1 Kg', 'WM-SMB-003', 1, 18000.00, 0.00, 0.00, 18000.00),
(8, 2, 2, 'Minyak Goreng 1 Liter', 'WM-SMB-002', 1, 23000.00, 0.00, 0.00, 23000.00),
(9, 3, 1, 'Beras Premium 5 Kg', 'WM-SMB-001', 2, 75000.00, 0.00, 0.00, 150000.00),
(10, 3, 3, 'Gula Pasir 1 Kg', 'WM-SMB-003', 5, 18000.00, 0.00, 0.00, 90000.00),
(11, 3, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 2, 18000.00, 0.00, 0.00, 36000.00),
(12, 3, 5, 'Mi Instan Goreng', 'WM-MKN-001', 5, 3500.00, 0.00, 0.00, 17500.00),
(13, 3, 2, 'Minyak Goreng 1 Liter', 'WM-SMB-002', 5, 23000.00, 0.00, 0.00, 115000.00),
(14, 3, 9, 'Sabun Mandi Cair 250 ml', 'WM-PRW-001', 5, 5000.00, 0.00, 0.00, 25000.00),
(15, 3, 10, 'Sunlight Botol 675gr', 'WM-KBR-001', 2, 17501.00, 0.00, 0.00, 35002.00),
(16, 3, 8, 'Susu UHT 1 Liter', 'WM-MNM-003', 1, 21000.00, 0.00, 0.00, 21000.00),
(17, 3, 6, 'Teh Celup Isi 25', 'WM-MNM-001', 1, 12000.00, 0.00, 0.00, 12000.00),
(18, 3, 4, 'Tepung Terigu 1 Kg', 'WM-SMB-004', 4, 13000.00, 0.00, 0.00, 52000.00),
(19, 4, 1, 'Beras Premium 5 Kg', 'WM-SMB-001', 4, 75000.00, 0.00, 0.00, 300000.00),
(20, 4, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 3, 18000.00, 0.00, 0.00, 54000.00),
(21, 4, 3, 'Gula Pasir 1 Kg', 'WM-SMB-003', 5, 18000.00, 0.00, 0.00, 90000.00),
(22, 4, 5, 'Mi Instan Goreng', 'WM-MKN-001', 7, 3500.00, 0.00, 0.00, 24500.00),
(23, 4, 2, 'Minyak Goreng 1 Liter', 'WM-SMB-002', 4, 23000.00, 0.00, 0.00, 92000.00),
(24, 4, 9, 'Sabun Mandi Cair 250 ml', 'WM-PRW-001', 3, 5000.00, 0.00, 0.00, 15000.00),
(25, 4, 10, 'Sunlight Botol 675gr', 'WM-KBR-001', 2, 17501.00, 0.00, 0.00, 35002.00),
(26, 4, 8, 'Susu UHT 1 Liter', 'WM-MNM-003', 1, 21000.00, 0.00, 0.00, 21000.00),
(27, 4, 6, 'Teh Celup Isi 25', 'WM-MNM-001', 1, 12000.00, 0.00, 0.00, 12000.00),
(28, 4, 4, 'Tepung Terigu 1 Kg', 'WM-SMB-004', 4, 13000.00, 0.00, 0.00, 52000.00),
(29, 5, 14, 'Es Krim', 'WM-MKN-002', 4, 3101.00, 0.00, 0.00, 12404.00),
(30, 5, 5, 'IndoMie Goreng', 'WM-MKN-001', 1, 3501.00, 0.00, 0.00, 3501.00),
(31, 5, 10, 'Sunlight Pencuci Piring 700 ml', 'WM-KBR-001', 1, 17501.00, 0.00, 0.00, 17501.00),
(32, 5, 9, 'Giv White - Mulberry & Collagen 400ml', 'WM-PRW-001', 1, 20901.00, 0.00, 0.00, 20901.00),
(33, 5, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 1, 16101.00, 0.00, 0.00, 16101.00),
(34, 6, 1, 'Beras Premium Sania 2,5 Kg', 'WM-SMB-001', 3, 49101.00, 0.00, 0.00, 147303.00),
(35, 6, 14, 'Es Krim', 'WM-MKN-002', 2, 3101.00, 0.00, 0.00, 6202.00),
(36, 6, 9, 'Giv White - Mulberry & Collagen 400ml', 'WM-PRW-001', 1, 20901.00, 0.00, 0.00, 20901.00),
(37, 6, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 1, 16101.00, 0.00, 0.00, 16101.00),
(38, 6, 5, 'IndoMie Goreng', 'WM-MKN-001', 1, 3501.00, 0.00, 0.00, 3501.00),
(39, 6, 10, 'Sunlight Pencuci Piring 700 ml', 'WM-KBR-001', 1, 17501.00, 0.00, 0.00, 17501.00),
(40, 6, 2, 'Minyak Sanco 2lt', 'WM-SMB-002', 1, 55101.00, 0.00, 0.00, 55101.00),
(41, 6, 3, 'Gulaku 1 Kg', 'WM-SMB-003', 1, 12001.00, 0.00, 0.00, 12001.00),
(42, 6, 6, 'Teh Poci Celup Isi 25', 'WM-MNM-001', 1, 7001.00, 0.00, 0.00, 7001.00),
(43, 7, 5, 'IndoMie Goreng', 'WM-MKN-001', 1, 101.00, 0.00, 0.00, 101.00),
(44, 8, 9, 'Giv White - Mulberry & Collagen 400ml', 'WM-PRW-001', 1, 20901.00, 0.00, 0.00, 20901.00),
(45, 8, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 2, 16101.00, 0.00, 0.00, 32202.00),
(46, 8, 3, 'Gulaku 1 Kg', 'WM-SMB-003', 1, 12001.00, 0.00, 0.00, 12001.00),
(47, 8, 2, 'Minyak Sanco 2lt', 'WM-SMB-002', 1, 55101.00, 0.00, 0.00, 55101.00),
(48, 8, 10, 'Sunlight Pencuci Piring 700 ml', 'WM-KBR-001', 2, 17501.00, 0.00, 0.00, 35002.00),
(49, 8, 6, 'Teh Poci Celup Isi 25', 'WM-MNM-001', 1, 7001.00, 0.00, 0.00, 7001.00),
(50, 9, 1, 'Beras Premium Sania 2,5 Kg', 'WM-SMB-001', 3, 49101.00, 0.00, 0.00, 147303.00),
(51, 9, 3, 'Gulaku 1 Kg', 'WM-SMB-003', 1, 12001.00, 0.00, 0.00, 12001.00),
(52, 9, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 1, 16101.00, 0.00, 0.00, 16101.00),
(53, 9, 9, 'Giv White - Mulberry & Collagen 400ml', 'WM-PRW-001', 1, 20901.00, 0.00, 0.00, 20901.00),
(54, 9, 10, 'Sunlight Pencuci Piring 700 ml', 'WM-KBR-001', 1, 17501.00, 0.00, 0.00, 17501.00),
(55, 10, 1, 'Beras Premium Sania 2,5 Kg', 'WM-SMB-001', 1, 49101.00, 0.00, 0.00, 49101.00),
(56, 10, 9, 'Giv White - Mulberry & Collagen 400ml', 'WM-PRW-001', 1, 20901.00, 0.00, 0.00, 20901.00),
(57, 10, 6, 'Teh Poci Celup Isi 25', 'WM-MNM-001', 1, 7001.00, 0.00, 0.00, 7001.00),
(58, 11, 1, 'Beras Premium Sania 2,5 Kg', 'WM-SMB-001', 1, 49101.00, 0.00, 0.00, 49101.00),
(59, 11, 9, 'Giv White - Mulberry & Collagen 400ml', 'WM-PRW-001', 1, 20901.00, 0.00, 0.00, 20901.00),
(60, 11, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 1, 16101.00, 0.00, 0.00, 16101.00),
(61, 11, 14, 'Es Krim', 'WM-MKN-002', 1, 3101.00, 0.00, 0.00, 3101.00),
(62, 11, 5, 'IndoMie Goreng', 'WM-MKN-001', 1, 101.00, 0.00, 0.00, 101.00),
(63, 11, 3, 'Gulaku 1 Kg', 'WM-SMB-003', 2, 12001.00, 0.00, 0.00, 24002.00),
(64, 11, 2, 'Minyak Sanco 2lt', 'WM-SMB-002', 1, 55101.00, 0.00, 0.00, 55101.00),
(65, 11, 10, 'Sunlight Pencuci Piring 700 ml', 'WM-KBR-001', 1, 17501.00, 0.00, 0.00, 17501.00),
(66, 11, 6, 'Teh Poci Celup Isi 25', 'WM-MNM-001', 1, 7001.00, 0.00, 0.00, 7001.00),
(67, 11, 8, 'Ultra Milk Fullcream 1000ml', 'WM-MNM-003', 1, 25501.00, 0.00, 0.00, 25501.00),
(68, 11, 4, 'Tepung Terigu Segitiga Biru 1 Kg', 'WM-SMB-004', 1, 13001.00, 0.00, 0.00, 13001.00),
(69, 12, 1, 'Beras Premium Sania 2,5 Kg', 'WM-SMB-001', 5, 49101.00, 0.00, 0.00, 245505.00),
(70, 13, 3, 'Gulaku 1 Kg', 'WM-SMB-003', 3, 12001.00, 0.00, 0.00, 36003.00),
(71, 13, 2, 'Minyak Sanco 2lt', 'WM-SMB-002', 5, 55101.00, 0.00, 0.00, 275505.00),
(72, 13, 9, 'Giv White - Mulberry & Collagen 400ml', 'WM-PRW-001', 4, 20901.00, 0.00, 0.00, 83604.00),
(73, 13, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 4, 16101.00, 0.00, 0.00, 64404.00),
(79, 19, 7, 'Kopi Bubuk 180 g', 'WM-MNM-002', 4, 16101.00, 0.00, 15900.00, 64404.00);

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
(10, 1, 'checkout', 'tbl_transaksi', 2, 'Transaksi WM-261111-113015-328', '::1', '2026-11-11 11:30:15'),
(11, 1, 'checkout', 'tbl_transaksi', 3, 'Transaksi WM-261111-124936-539', '::1', '2026-11-11 12:49:36'),
(12, 1, 'checkout', 'tbl_transaksi', 4, 'Transaksi WM-261111-125149-193', '::1', '2026-11-11 12:51:49'),
(13, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-23 08:28:21'),
(14, 1, 'edit_produk', 'tbl_produk', 9, 'Admin mengubah produk Giv White - Mulberry & Collagen 400ml', '::1', '2026-11-23 08:34:24'),
(15, 1, 'edit_produk', 'tbl_produk', 8, 'Admin mengubah produk Ultra Milk Fullcream 1000ml', '::1', '2026-11-23 08:45:16'),
(16, 1, 'edit_produk', 'tbl_produk', 7, 'Admin mengubah produk Kopi Bubuk Rpbusta 200g', '::1', '2026-11-23 08:46:56'),
(17, 1, 'edit_produk', 'tbl_produk', 6, 'Admin mengubah produk Teh Poci Celup Isi 25', '::1', '2026-11-23 08:52:44'),
(18, 1, 'edit_produk', 'tbl_produk', 5, 'Admin mengubah produk IndoMie Goreng', '::1', '2026-11-23 09:05:31'),
(19, 1, 'edit_produk', 'tbl_produk', 4, 'Admin mengubah produk Tepung Terigu Segitiga Biru 1 Kg', '::1', '2026-11-23 09:16:50'),
(20, 1, 'edit_produk', 'tbl_produk', 3, 'Admin mengubah produk Gulaku 1 Kg', '::1', '2026-11-23 09:18:36'),
(21, 1, 'edit_produk', 'tbl_produk', 2, 'Admin mengubah produk Minyak Sanco 2lt', '::1', '2026-11-23 09:19:40'),
(22, 1, 'edit_produk', 'tbl_produk', 2, 'Admin mengubah produk Minyak Sanco 2lt', '::1', '2026-11-23 09:19:48'),
(23, 1, 'edit_produk', 'tbl_produk', 1, 'Admin mengubah produk Beras Premium Sania 2,5 Kg', '::1', '2026-11-23 09:21:25'),
(24, 1, 'ubah_status_kategori', 'tbl_kategori', 7, 'Mengubah status kategori', '::1', '2026-11-23 10:58:31'),
(25, 1, 'ubah_status_kategori', 'tbl_kategori', 7, 'Mengubah status kategori', '::1', '2026-11-23 10:58:35'),
(26, 1, 'tambah_produk', 'tbl_produk', 14, 'Menambahkan produk Es Krim', '::1', '2026-11-23 11:40:35'),
(27, 1, 'penerimaan_barang', 'tbl_penerimaan_barang', 1, 'Penerimaan TRM-20261123-114123-361', '::1', '2026-11-23 11:41:23'),
(28, 1, 'penerimaan_barang', 'tbl_penerimaan_barang', 2, 'Penerimaan TRM-20261123-114203-671', '::1', '2026-11-23 11:42:03'),
(29, 1, 'checkout', 'tbl_transaksi', 5, 'Transaksi WM-261123-114259-559', '::1', '2026-11-23 11:42:59'),
(30, 1, 'checkout', 'tbl_transaksi', 6, 'Transaksi WM-261123-125809-440', '::1', '2026-11-23 12:58:09'),
(31, 1, 'edit_produk', 'tbl_produk', 5, 'Admin mengubah produk IndoMie Goreng', '::1', '2026-11-23 13:25:06'),
(32, 1, 'checkout', 'tbl_transaksi', 7, 'Transaksi WM-261123-132843-386', '::1', '2026-11-23 13:28:43'),
(33, 1, 'ubah_status_transaksi', 'tbl_transaksi', 7, 'Status pesanan menjadi selesai', '::1', '2026-11-23 13:35:28'),
(34, 2, 'login', 'tbl_pengguna', 2, 'Pengguna berhasil login', '::1', '2026-11-25 08:23:44'),
(35, 2, 'logout', 'tbl_pengguna', 2, 'Pengguna logout', '::1', '2026-11-25 09:45:02'),
(36, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-25 09:45:17'),
(37, 1, 'tambah_pengguna', 'tbl_pengguna', 3, 'Akun Riska (kasir) disimpan', '::1', '2026-11-25 09:46:00'),
(38, 1, 'logout', 'tbl_pengguna', 1, 'Pengguna logout', '::1', '2026-11-25 09:46:08'),
(39, 3, 'login', 'tbl_pengguna', 3, 'Pengguna berhasil login', '::1', '2026-11-25 09:46:20'),
(40, 3, 'logout', 'tbl_pengguna', 3, 'Pengguna logout', '::1', '2026-11-25 10:28:22'),
(41, 3, 'login', 'tbl_pengguna', 3, 'Pengguna berhasil login', '::1', '2026-11-25 10:28:47'),
(42, 3, 'logout', 'tbl_pengguna', 3, 'Pengguna logout', '::1', '2026-11-25 10:34:27'),
(43, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-25 10:34:39'),
(44, 1, 'checkout', 'tbl_transaksi', 8, 'Transaksi WM-261125-105124-246', '::1', '2026-11-25 10:51:24'),
(45, 1, 'logout', 'tbl_pengguna', 1, 'Pengguna logout', '::1', '2026-11-25 10:51:32'),
(46, 2, 'login', 'tbl_pengguna', 2, 'Pengguna berhasil login', '::1', '2026-11-25 10:51:43'),
(47, 2, 'checkout', 'tbl_transaksi', 9, 'Transaksi WM-261125-105200-436', '::1', '2026-11-25 10:52:00'),
(48, 2, 'checkout', 'tbl_transaksi', 10, 'Transaksi WM-261125-105254-935', '::1', '2026-11-25 10:52:54'),
(49, 4, 'login', 'tbl_pengguna', 4, 'Pengguna berhasil login', '::1', '2026-11-25 12:49:13'),
(50, 4, 'checkout', 'tbl_transaksi', 11, 'Transaksi WM-261125-124931-503', '::1', '2026-11-25 12:49:31'),
(51, 4, 'checkout', 'tbl_transaksi', 12, 'Transaksi WM-261125-124945-989', '::1', '2026-11-25 12:49:45'),
(52, 4, 'checkout', 'tbl_transaksi', 13, 'Transaksi WM-261125-125006-298', '::1', '2026-11-25 12:50:06'),
(53, 4, 'logout', 'tbl_pengguna', 4, 'Pengguna logout', '::1', '2026-11-25 12:50:18'),
(54, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-25 12:50:27'),
(55, 1, 'logout', 'tbl_pengguna', 1, 'Pengguna logout', '::1', '2026-11-25 12:51:00'),
(56, 5, 'login', 'tbl_pengguna', 5, 'Pengguna berhasil login', NULL, '2026-11-25 13:06:06'),
(57, 6, 'login', 'tbl_pengguna', 6, 'Pengguna berhasil login', NULL, '2026-11-25 13:06:06'),
(58, 7, 'login', 'tbl_pengguna', 7, 'Pengguna berhasil login', NULL, '2026-11-25 13:06:06'),
(59, 5, 'login', 'tbl_pengguna', 5, 'Pengguna berhasil login', '::1', '2026-11-25 13:18:15'),
(60, 5, 'logout', 'tbl_pengguna', 5, 'Pengguna logout', '::1', '2026-11-25 13:18:34'),
(61, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-25 13:18:34'),
(62, 1, 'logout', 'tbl_pengguna', 1, 'Pengguna logout', '::1', '2026-11-25 13:20:45'),
(63, 5, 'login', 'tbl_pengguna', 5, 'Pengguna berhasil login', '::1', '2026-11-25 13:20:45'),
(65, 5, 'logout', 'tbl_pengguna', 5, 'Pengguna logout', '::1', '2026-11-25 13:20:53'),
(66, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-25 13:20:54'),
(68, 5, 'login', 'tbl_pengguna', 5, 'Pengguna berhasil login', '::1', '2026-11-25 13:21:46'),
(69, 1, 'logout', 'tbl_pengguna', 1, 'Pengguna logout', '::1', '2026-11-25 13:22:20'),
(70, 5, 'login', 'tbl_pengguna', 5, 'Pengguna berhasil login', '::1', '2026-11-25 13:22:21'),
(73, 5, 'logout', 'tbl_pengguna', 5, 'Pengguna logout', '::1', '2026-11-25 13:23:12'),
(74, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-25 13:23:23'),
(75, 1, 'edit_produk', 'tbl_produk', 14, 'Admin mengubah produk Oreo', '::1', '2026-11-25 13:24:19'),
(76, 1, 'edit_produk', 'tbl_produk', 14, 'Admin mengubah produk Oreo', '::1', '2026-11-25 13:24:36'),
(77, 5, 'logout', 'tbl_pengguna', 5, 'Pengguna logout', '::1', '2026-11-25 13:44:33'),
(78, 5, 'login', 'tbl_pengguna', 5, 'Pengguna berhasil login', '::1', '2026-11-25 13:44:59'),
(79, 5, 'checkout_online', 'tbl_transaksi', 19, 'Pesanan pelanggan WM-ON-261125-134512-781', '::1', '2026-11-25 13:45:12'),
(80, 5, 'unggah_bukti_pembayaran', 'tbl_pembayaran', 19, 'Bukti QRIS diunggah untuk verifikasi', '::1', '2026-11-25 13:45:31'),
(81, 5, 'logout', 'tbl_pengguna', 5, 'Pengguna logout', '::1', '2026-11-25 13:45:38'),
(82, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-25 13:45:47'),
(83, 1, 'verifikasi_pembayaran_qris', 'tbl_transaksi', 19, 'Bukti QRIS disetujui', '::1', '2026-11-25 13:45:54'),
(84, 1, 'login', 'tbl_pengguna', 1, 'Pengguna berhasil login', '::1', '2026-11-26 08:25:11');

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
(8, 2, 1, 2, NULL, 'penjualan', -1, 18, 17, 'Penjualan kasir', '2026-11-11 11:30:15'),
(9, 1, 1, 3, NULL, 'penjualan', -2, 19, 17, 'Penjualan kasir', '2026-11-11 12:49:36'),
(10, 3, 1, 3, NULL, 'penjualan', -5, 18, 13, 'Penjualan kasir', '2026-11-11 12:49:36'),
(11, 7, 1, 3, NULL, 'penjualan', -2, 19, 17, 'Penjualan kasir', '2026-11-11 12:49:36'),
(12, 5, 1, 3, NULL, 'penjualan', -5, 50, 45, 'Penjualan kasir', '2026-11-11 12:49:36'),
(13, 2, 1, 3, NULL, 'penjualan', -5, 17, 12, 'Penjualan kasir', '2026-11-11 12:49:36'),
(14, 9, 1, 3, NULL, 'penjualan', -5, 18, 13, 'Penjualan kasir', '2026-11-11 12:49:36'),
(15, 10, 1, 3, NULL, 'penjualan', -2, 20, 18, 'Penjualan kasir', '2026-11-11 12:49:36'),
(16, 8, 1, 3, NULL, 'penjualan', -1, 20, 19, 'Penjualan kasir', '2026-11-11 12:49:36'),
(17, 6, 1, 3, NULL, 'penjualan', -1, 20, 19, 'Penjualan kasir', '2026-11-11 12:49:36'),
(18, 4, 1, 3, NULL, 'penjualan', -4, 20, 16, 'Penjualan kasir', '2026-11-11 12:49:36'),
(19, 1, 1, 4, NULL, 'penjualan', -4, 17, 13, 'Penjualan kasir', '2026-11-11 12:51:49'),
(20, 7, 1, 4, NULL, 'penjualan', -3, 17, 14, 'Penjualan kasir', '2026-11-11 12:51:49'),
(21, 3, 1, 4, NULL, 'penjualan', -5, 13, 8, 'Penjualan kasir', '2026-11-11 12:51:49'),
(22, 5, 1, 4, NULL, 'penjualan', -7, 45, 38, 'Penjualan kasir', '2026-11-11 12:51:49'),
(23, 2, 1, 4, NULL, 'penjualan', -4, 12, 8, 'Penjualan kasir', '2026-11-11 12:51:49'),
(24, 9, 1, 4, NULL, 'penjualan', -3, 13, 10, 'Penjualan kasir', '2026-11-11 12:51:49'),
(25, 10, 1, 4, NULL, 'penjualan', -2, 18, 16, 'Penjualan kasir', '2026-11-11 12:51:49'),
(26, 8, 1, 4, NULL, 'penjualan', -1, 19, 18, 'Penjualan kasir', '2026-11-11 12:51:49'),
(27, 6, 1, 4, NULL, 'penjualan', -1, 19, 18, 'Penjualan kasir', '2026-11-11 12:51:49'),
(28, 4, 1, 4, NULL, 'penjualan', -4, 16, 12, 'Penjualan kasir', '2026-11-11 12:51:49'),
(29, 14, 1, NULL, 1, 'stok_masuk', 1, 0, 1, 'Penerimaan TRM-20261123-114123-361', '2026-11-23 11:41:23'),
(30, 14, 1, NULL, 2, 'stok_masuk', 10, 1, 11, 'Penerimaan TRM-20261123-114203-671', '2026-11-23 11:42:03'),
(31, 14, 1, 5, NULL, 'penjualan', -4, 11, 7, 'Penjualan kasir', '2026-11-23 11:42:59'),
(32, 5, 1, 5, NULL, 'penjualan', -1, 38, 37, 'Penjualan kasir', '2026-11-23 11:42:59'),
(33, 10, 1, 5, NULL, 'penjualan', -1, 16, 15, 'Penjualan kasir', '2026-11-23 11:42:59'),
(34, 9, 1, 5, NULL, 'penjualan', -1, 10, 9, 'Penjualan kasir', '2026-11-23 11:42:59'),
(35, 7, 1, 5, NULL, 'penjualan', -1, 14, 13, 'Penjualan kasir', '2026-11-23 11:42:59'),
(36, 1, 1, 6, NULL, 'penjualan', -3, 13, 10, 'Penjualan kasir', '2026-11-23 12:58:09'),
(37, 14, 1, 6, NULL, 'penjualan', -2, 7, 5, 'Penjualan kasir', '2026-11-23 12:58:09'),
(38, 9, 1, 6, NULL, 'penjualan', -1, 9, 8, 'Penjualan kasir', '2026-11-23 12:58:09'),
(39, 7, 1, 6, NULL, 'penjualan', -1, 13, 12, 'Penjualan kasir', '2026-11-23 12:58:09'),
(40, 5, 1, 6, NULL, 'penjualan', -1, 37, 36, 'Penjualan kasir', '2026-11-23 12:58:09'),
(41, 10, 1, 6, NULL, 'penjualan', -1, 15, 14, 'Penjualan kasir', '2026-11-23 12:58:09'),
(42, 2, 1, 6, NULL, 'penjualan', -1, 8, 7, 'Penjualan kasir', '2026-11-23 12:58:09'),
(43, 3, 1, 6, NULL, 'penjualan', -1, 8, 7, 'Penjualan kasir', '2026-11-23 12:58:09'),
(44, 6, 1, 6, NULL, 'penjualan', -1, 18, 17, 'Penjualan kasir', '2026-11-23 12:58:09'),
(45, 5, 1, 7, NULL, 'penjualan', -1, 36, 35, 'Penjualan kasir', '2026-11-23 13:28:43'),
(46, 9, 1, 8, NULL, 'penjualan', -1, 8, 7, 'Penjualan kasir', '2026-11-25 10:51:24'),
(47, 7, 1, 8, NULL, 'penjualan', -2, 12, 10, 'Penjualan kasir', '2026-11-25 10:51:24'),
(48, 3, 1, 8, NULL, 'penjualan', -1, 7, 6, 'Penjualan kasir', '2026-11-25 10:51:24'),
(49, 2, 1, 8, NULL, 'penjualan', -1, 7, 6, 'Penjualan kasir', '2026-11-25 10:51:24'),
(50, 10, 1, 8, NULL, 'penjualan', -2, 14, 12, 'Penjualan kasir', '2026-11-25 10:51:24'),
(51, 6, 1, 8, NULL, 'penjualan', -1, 17, 16, 'Penjualan kasir', '2026-11-25 10:51:24'),
(52, 1, 2, 9, NULL, 'penjualan', -3, 10, 7, 'Penjualan kasir', '2026-11-25 10:52:00'),
(53, 3, 2, 9, NULL, 'penjualan', -1, 6, 5, 'Penjualan kasir', '2026-11-25 10:52:00'),
(54, 7, 2, 9, NULL, 'penjualan', -1, 10, 9, 'Penjualan kasir', '2026-11-25 10:52:00'),
(55, 9, 2, 9, NULL, 'penjualan', -1, 7, 6, 'Penjualan kasir', '2026-11-25 10:52:00'),
(56, 10, 2, 9, NULL, 'penjualan', -1, 12, 11, 'Penjualan kasir', '2026-11-25 10:52:00'),
(57, 1, 2, 10, NULL, 'penjualan', -1, 7, 6, 'Penjualan kasir', '2026-11-25 10:52:54'),
(58, 9, 2, 10, NULL, 'penjualan', -1, 6, 5, 'Penjualan kasir', '2026-11-25 10:52:54'),
(59, 6, 2, 10, NULL, 'penjualan', -1, 16, 15, 'Penjualan kasir', '2026-11-25 10:52:54'),
(60, 1, 4, 11, NULL, 'penjualan', -1, 6, 5, 'Penjualan kasir', '2026-11-25 12:49:31'),
(61, 9, 4, 11, NULL, 'penjualan', -1, 5, 4, 'Penjualan kasir', '2026-11-25 12:49:31'),
(62, 7, 4, 11, NULL, 'penjualan', -1, 9, 8, 'Penjualan kasir', '2026-11-25 12:49:31'),
(63, 14, 4, 11, NULL, 'penjualan', -1, 5, 4, 'Penjualan kasir', '2026-11-25 12:49:31'),
(64, 5, 4, 11, NULL, 'penjualan', -1, 35, 34, 'Penjualan kasir', '2026-11-25 12:49:31'),
(65, 3, 4, 11, NULL, 'penjualan', -2, 5, 3, 'Penjualan kasir', '2026-11-25 12:49:31'),
(66, 2, 4, 11, NULL, 'penjualan', -1, 6, 5, 'Penjualan kasir', '2026-11-25 12:49:31'),
(67, 10, 4, 11, NULL, 'penjualan', -1, 11, 10, 'Penjualan kasir', '2026-11-25 12:49:31'),
(68, 6, 4, 11, NULL, 'penjualan', -1, 15, 14, 'Penjualan kasir', '2026-11-25 12:49:31'),
(69, 8, 4, 11, NULL, 'penjualan', -1, 18, 17, 'Penjualan kasir', '2026-11-25 12:49:31'),
(70, 4, 4, 11, NULL, 'penjualan', -1, 12, 11, 'Penjualan kasir', '2026-11-25 12:49:31'),
(71, 1, 4, 12, NULL, 'penjualan', -5, 5, 0, 'Penjualan kasir', '2026-11-25 12:49:45'),
(72, 3, 4, 13, NULL, 'penjualan', -3, 3, 0, 'Penjualan kasir', '2026-11-25 12:50:06'),
(73, 2, 4, 13, NULL, 'penjualan', -5, 5, 0, 'Penjualan kasir', '2026-11-25 12:50:06'),
(74, 9, 4, 13, NULL, 'penjualan', -4, 4, 0, 'Penjualan kasir', '2026-11-25 12:50:06'),
(75, 7, 4, 13, NULL, 'penjualan', -4, 8, 4, 'Penjualan kasir', '2026-11-25 12:50:06'),
(83, 7, NULL, 19, NULL, 'penjualan', -4, 4, 0, 'Checkout online pelanggan', '2026-11-25 13:45:12');

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
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `uid_rfid` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pelanggan`
--

INSERT INTO `tbl_pelanggan` (`id_pelanggan`, `id_pengguna`, `no_member`, `rfid_uid`, `nama_pelanggan`, `no_hp`, `email`, `alamat`, `kota`, `status_member`, `created_at`, `uid_rfid`) VALUES
(1, NULL, 'WM261123D9D60C', '0008493873 12939729', 'Chintya Airin', '085737428117', NULL, NULL, NULL, 'aktif', '2026-11-23 11:55:56', NULL),
(2, 5, 'WM-DEMO-0001', NULL, 'Alya Putri', '081234567801', 'userdemo1@warungmadura.local', NULL, NULL, 'aktif', '2026-11-25 13:05:51', NULL),
(3, 6, 'WM-DEMO-0002', NULL, 'Bima Pratama', '081234567802', 'userdemo2@warungmadura.local', NULL, NULL, 'aktif', '2026-11-25 13:05:51', NULL),
(4, 7, 'WM-DEMO-0003', NULL, 'Nadia Safitri', '081234567803', 'userdemo3@warungmadura.local', NULL, NULL, 'aktif', '2026-11-25 13:05:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pembayaran`
--

CREATE TABLE `tbl_pembayaran` (
  `id_pembayaran` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` bigint(20) UNSIGNED NOT NULL,
  `metode_pembayaran` enum('tunai','tabungan','ewallet','qris_dana') NOT NULL,
  `jumlah_bayar` decimal(14,2) NOT NULL,
  `status_pembayaran` enum('menunggu','berhasil','gagal','dikembalikan') NOT NULL DEFAULT 'menunggu',
  `referensi_simulasi` varchar(80) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `diproses_oleh` int(10) UNSIGNED DEFAULT NULL,
  `waktu_pembayaran` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pembayaran`
--

INSERT INTO `tbl_pembayaran` (`id_pembayaran`, `id_transaksi`, `metode_pembayaran`, `jumlah_bayar`, `status_pembayaran`, `referensi_simulasi`, `bukti_pembayaran`, `diproses_oleh`, `waktu_pembayaran`, `created_at`) VALUES
(1, 1, 'tunai', 200000.00, 'berhasil', 'SIM-A069F2BA', NULL, 1, '2026-11-11 11:05:17', '2026-11-11 11:05:17'),
(2, 2, 'tunai', 50000.00, 'berhasil', 'SIM-944BBC88', NULL, 1, '2026-11-11 11:30:15', '2026-11-11 11:30:15'),
(3, 3, 'tunai', 560000.00, 'berhasil', 'SIM-49A4CCCA', NULL, 1, '2026-11-11 12:49:36', '2026-11-11 12:49:36'),
(4, 4, 'tunai', 710000.00, 'berhasil', 'SIM-DCB8D859', NULL, 1, '2026-11-11 12:51:49', '2026-11-11 12:51:49'),
(5, 5, 'tunai', 70500.00, 'berhasil', 'SIM-8AEBCEE3', NULL, 1, '2026-11-23 11:42:59', '2026-11-23 11:42:59'),
(6, 6, 'tunai', 290000.00, 'berhasil', 'SIM-20388A33', NULL, 1, '2026-11-23 12:58:09', '2026-11-23 12:58:09'),
(7, 7, 'qris_dana', 101.00, 'berhasil', NULL, NULL, 1, '2026-11-23 13:28:43', '2026-11-23 13:28:43'),
(8, 8, 'qris_dana', 162208.00, 'berhasil', NULL, NULL, 1, '2026-11-25 10:51:24', '2026-11-25 10:51:24'),
(9, 9, 'qris_dana', 213807.00, 'berhasil', NULL, NULL, 2, '2026-11-25 10:52:00', '2026-11-25 10:52:00'),
(10, 10, 'tunai', 77500.00, 'berhasil', 'SIM-9D7EE3F9', NULL, 2, '2026-11-25 10:52:54', '2026-11-25 10:52:54'),
(11, 11, 'tunai', 232000.00, 'berhasil', 'SIM-42F7B3F2', NULL, 4, '2026-11-25 12:49:31', '2026-11-25 12:49:31'),
(12, 12, 'qris_dana', 245505.00, 'berhasil', NULL, NULL, 4, '2026-11-25 12:49:45', '2026-11-25 12:49:45'),
(13, 13, 'qris_dana', 459516.00, 'berhasil', NULL, NULL, 4, '2026-11-25 12:50:06', '2026-11-25 12:50:06'),
(19, 19, 'qris_dana', 64404.00, 'berhasil', NULL, 'uploads/payment-proofs/f15465e760029da1ec47cfd4c2acba1e.jpg', 1, '2026-11-25 13:45:54', '2026-11-25 13:45:12');

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

--
-- Dumping data for table `tbl_penerimaan_barang`
--

INSERT INTO `tbl_penerimaan_barang` (`id_penerimaan`, `no_penerimaan`, `id_supplier`, `id_pengguna`, `status_penerimaan`, `waktu_penerimaan`, `catatan`) VALUES
(1, 'TRM-20261123-114123-361', NULL, 1, 'diterima', '2026-11-23 11:41:23', NULL),
(2, 'TRM-20261123-114203-671', NULL, 1, 'diterima', '2026-11-23 11:42:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengeluaran`
--

CREATE TABLE `tbl_pengeluaran` (
  `id_pengeluaran` bigint(20) UNSIGNED NOT NULL,
  `no_pengeluaran` varchar(50) NOT NULL,
  `kategori_pengeluaran` enum('listrik','transportasi','perlengkapan','gaji','sewa','lainnya') NOT NULL DEFAULT 'lainnya',
  `deskripsi` varchar(255) NOT NULL,
  `jumlah` decimal(14,2) NOT NULL,
  `tanggal_pengeluaran` datetime NOT NULL DEFAULT current_timestamp(),
  `id_pengguna` int(10) UNSIGNED DEFAULT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
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
(1, 'Administrator WarungMadura', 'admin', 'admin@warungmadura.local', '$2y$12$kr6NHk5sw24.FAQdtj0Dyuyd2s1kFOVtCKAZsZNV1cUEw9soZMOAm', NULL, 'admin', 'aktif', '2026-11-26 08:25:11', '2026-11-11 08:39:50', '2026-11-26 08:25:11'),
(2, 'Kasir WarungMadura', 'kasir', 'kasir@warungmadura.local', '$2y$12$0gthfnBMc3oiShtTQv36N.o/hCmjH5e26nG5AFVpjSg4YLgsa03T6', NULL, 'kasir', 'aktif', '2026-11-25 10:51:43', '2026-11-11 08:39:50', '2026-11-25 10:51:43'),
(3, 'Kasir 2', 'Riska', 'riskakasir@gmail.com', '$2y$10$FfCFgDCZVotPR1OSD8vtWedwEOU6/41Pn7VDVK5wurRWzgLM4CsSe', '08123456789', 'kasir', 'aktif', '2026-11-25 10:28:47', '2026-11-25 09:46:00', '2026-11-25 10:28:47'),
(4, 'Kasir Dua WarungMadura', 'kasir2', 'kasir2@warungmadura.local', '$2y$10$UUZfLUJPGB1iZpmaGjjz7efH/1g1a0RkWA1i3lTRreniUCVF7yIbq', NULL, 'kasir', 'aktif', '2026-11-25 12:49:13', '2026-11-25 09:48:33', '2026-11-25 12:49:13'),
(5, 'Alya Putri', 'userdemo1', 'userdemo1@warungmadura.local', '$2y$10$cJOU38mS2kbVMs92eVeF5.0/xJQv7SNzwmsdtLBlanP1RNCKkMZ3S', NULL, 'pelanggan', 'aktif', '2026-11-25 13:44:59', '2026-11-25 13:05:51', '2026-11-25 13:44:59'),
(6, 'Bima Pratama', 'userdemo2', 'userdemo2@warungmadura.local', '$2y$10$cJOU38mS2kbVMs92eVeF5.0/xJQv7SNzwmsdtLBlanP1RNCKkMZ3S', NULL, 'pelanggan', 'aktif', '2026-11-25 13:06:06', '2026-11-25 13:05:51', '2026-11-25 13:06:06'),
(7, 'Nadia Safitri', 'userdemo3', 'userdemo3@warungmadura.local', '$2y$10$cJOU38mS2kbVMs92eVeF5.0/xJQv7SNzwmsdtLBlanP1RNCKkMZ3S', NULL, 'pelanggan', 'aktif', '2026-11-25 13:06:06', '2026-11-25 13:05:51', '2026-11-25 13:06:06');

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
(1, 1, NULL, 'WM-SMB-001', 'WM-SMB-001', 'Beras Premium Sania 2,5 Kg', NULL, 48900.00, 49101.00, 0, 'pack', 5, 'uploads/products/5731c0fd8c886a1a5914f98e5c3a62b6.webp', 'aktif', '2026-11-11 08:39:50', '2026-11-25 12:49:45'),
(2, 1, NULL, 'WM-SMB-002', 'WM-SMB-002', 'Minyak Sanco 2lt', NULL, 55000.00, 55101.00, 0, 'botol', 5, 'uploads/products/a67f37cffbce00c1fd8c659751291682.jpg', 'aktif', '2026-11-11 08:39:50', '2026-11-25 12:50:06'),
(3, 1, NULL, 'WM-SMB-003', 'WM-SMB-003', 'Gulaku 1 Kg', NULL, 11500.00, 12001.00, 0, 'pack', 5, 'uploads/products/da6d9d10c1f32717281d8b5e8dd9494e.jpg', 'aktif', '2026-11-11 08:39:50', '2026-11-25 12:50:06'),
(4, 1, NULL, 'WM-SMB-004', 'WM-SMB-004', 'Tepung Terigu Segitiga Biru 1 Kg', NULL, 12900.00, 13001.00, 11, 'pack', 5, 'uploads/products/ee25f7a000ae60bb39be96328b4fbc73.jpg', 'aktif', '2026-11-11 08:39:50', '2026-11-25 13:22:05'),
(5, 2, NULL, 'WM-MKN-001', '5285000390602', 'IndoMie Goreng', NULL, 100.00, 101.00, 34, 'pcs', 10, 'uploads/products/557e36ccc43a0fea8e50974a2187bc8c.png', 'aktif', '2026-11-11 08:39:50', '2026-11-25 12:49:31'),
(6, 3, NULL, 'WM-MNM-001', '8886007811113', 'Teh Poci Celup Isi 25', NULL, 6500.00, 7001.00, 14, 'box', 5, 'uploads/products/47feca75bea6d315085388e4dc2b71a9.jpg', 'aktif', '2026-11-11 08:39:50', '2026-11-25 12:49:31'),
(7, 3, NULL, 'WM-MNM-002', '8991002102132', 'Kopi Bubuk 180 g', NULL, 15900.00, 16101.00, 0, 'pack', 5, 'uploads/products/4a868b62efbce2b5c4ac941bc8d62826.jpg', 'aktif', '2026-11-11 08:39:50', '2026-11-25 13:45:12'),
(8, 3, NULL, 'WM-MNM-003', '8998009010613', 'Ultra Milk Fullcream 1000ml', NULL, 25000.00, 25501.00, 17, 'pcs', 5, 'uploads/products/26aa289c2eafaf7aca2fe9a26876b059.png', 'aktif', '2026-11-11 08:39:50', '2026-11-25 12:49:31'),
(9, 4, NULL, 'WM-PRW-001', '8998866618243', 'Giv White - Mulberry & Collagen 400ml', NULL, 18900.00, 20901.00, 0, 'pcs', 5, 'uploads/products/b447e572e54c9a851e7fe3174b0f4685.jpg', 'aktif', '2026-11-11 08:39:50', '2026-11-25 12:50:06'),
(10, 5, NULL, 'WM-KBR-001', '8999999615482', 'Sunlight Pencuci Piring 700 ml', NULL, 14000.00, 17501.00, 10, 'botol', 5, 'uploads/products/7cd1a2592baf1b5f4473d327ab693beb.jpg', 'aktif', '2026-11-11 08:39:50', '2026-11-25 12:49:31'),
(14, 8, NULL, 'WM-MKN-002', '9786232669246', 'Oreo', NULL, 8500.00, 9001.00, 5, 'pcs', 5, 'uploads/products/5c78871f58b66149f976ebe646d6e279.jpg', 'aktif', '2026-11-23 11:40:35', '2026-11-25 13:24:36');

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

--
-- Dumping data for table `tbl_saldo_pelanggan`
--

INSERT INTO `tbl_saldo_pelanggan` (`id_saldo`, `id_pelanggan`, `jenis_saldo`, `saldo`, `status_saldo`, `created_at`, `updated_at`) VALUES
(1, 1, 'tabungan', 0.00, 'aktif', '2026-11-23 11:55:56', NULL),
(2, 1, 'ewallet', 0.00, 'aktif', '2026-11-23 11:55:56', NULL),
(3, 2, 'tabungan', 0.00, 'aktif', '2026-11-25 13:05:51', NULL),
(4, 3, 'tabungan', 0.00, 'aktif', '2026-11-25 13:05:51', NULL),
(5, 4, 'tabungan', 0.00, 'aktif', '2026-11-25 13:05:51', NULL),
(6, 2, 'ewallet', 0.00, 'aktif', '2026-11-25 13:05:51', NULL),
(7, 3, 'ewallet', 0.00, 'aktif', '2026-11-25 13:05:51', NULL),
(8, 4, 'ewallet', 0.00, 'aktif', '2026-11-25 13:05:51', NULL);

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
  `metode_pembayaran` enum('tunai','tabungan','ewallet','qris_dana') DEFAULT NULL,
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
(2, 'WM-261111-113015-328', NULL, 1, 'toko', 'selesai', 'lunas', 'tunai', 'ambil_toko', '2026-11-11 11:30:15', 46000.00, 0.00, 0.00, 46000.00, 50000.00, 4000.00, NULL, NULL, NULL, NULL),
(3, 'WM-261111-124936-539', NULL, 1, 'toko', 'selesai', 'lunas', 'tunai', 'ambil_toko', '2026-11-11 12:49:36', 553502.00, 0.00, 0.00, 553502.00, 560000.00, 6498.00, NULL, NULL, NULL, NULL),
(4, 'WM-261111-125149-193', NULL, 1, 'toko', 'selesai', 'lunas', 'tunai', 'ambil_toko', '2026-11-11 12:51:49', 695502.00, 0.00, 0.00, 695502.00, 710000.00, 14498.00, NULL, NULL, NULL, NULL),
(5, 'WM-261123-114259-559', NULL, 1, 'toko', 'selesai', 'lunas', 'tunai', 'ambil_toko', '2026-11-23 11:42:59', 70408.00, 0.00, 0.00, 70408.00, 70500.00, 92.00, NULL, NULL, NULL, NULL),
(6, 'WM-261123-125809-440', 1, 1, 'toko', 'selesai', 'lunas', 'tunai', 'ambil_toko', '2026-11-23 12:58:09', 285612.00, 0.00, 0.00, 285612.00, 290000.00, 4388.00, NULL, NULL, NULL, NULL),
(7, 'WM-261123-132843-386', 1, 1, 'toko', 'selesai', 'lunas', 'qris_dana', 'ambil_toko', '2026-11-23 13:28:43', 101.00, 0.00, 0.00, 101.00, 101.00, 0.00, NULL, NULL, NULL, NULL),
(8, 'WM-261125-105124-246', 1, 1, 'toko', 'selesai', 'lunas', 'qris_dana', 'ambil_toko', '2026-11-25 10:51:24', 162208.00, 0.00, 0.00, 162208.00, 162208.00, 0.00, NULL, NULL, NULL, NULL),
(9, 'WM-261125-105200-436', NULL, 2, 'toko', 'selesai', 'lunas', 'qris_dana', 'ambil_toko', '2026-11-25 10:52:00', 213807.00, 0.00, 0.00, 213807.00, 213807.00, 0.00, NULL, NULL, NULL, NULL),
(10, 'WM-261125-105254-935', NULL, 2, 'toko', 'selesai', 'lunas', 'tunai', 'ambil_toko', '2026-11-25 10:52:54', 77003.00, 0.00, 0.00, 77003.00, 77500.00, 497.00, NULL, NULL, NULL, NULL),
(11, 'WM-261125-124931-503', NULL, 4, 'toko', 'selesai', 'lunas', 'tunai', 'ambil_toko', '2026-11-25 12:49:31', 231412.00, 0.00, 0.00, 231412.00, 232000.00, 588.00, NULL, NULL, NULL, NULL),
(12, 'WM-261125-124945-989', NULL, 4, 'toko', 'selesai', 'lunas', 'qris_dana', 'ambil_toko', '2026-11-25 12:49:45', 245505.00, 0.00, 0.00, 245505.00, 245505.00, 0.00, NULL, NULL, NULL, NULL),
(13, 'WM-261125-125006-298', NULL, 4, 'toko', 'selesai', 'lunas', 'qris_dana', 'ambil_toko', '2026-11-25 12:50:06', 459516.00, 0.00, 0.00, 459516.00, 459516.00, 0.00, NULL, NULL, NULL, NULL),
(19, 'WM-ON-261125-134512-781', 2, NULL, 'online', 'diproses', 'lunas', 'qris_dana', 'ambil_toko', '2026-11-25 13:45:12', 64404.00, 0.00, 0.00, 64404.00, 64404.00, 0.00, NULL, NULL, NULL, 'Menunggu bukti pembayaran QRIS');

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
  ADD UNIQUE KEY `uq_tbl_pelanggan_uid_rfid` (`uid_rfid`),
  ADD UNIQUE KEY `rfid_uid` (`rfid_uid`),
  ADD KEY `idx_pelanggan_nama` (`nama_pelanggan`),
  ADD KEY `idx_pelanggan_hp` (`no_hp`),
  ADD KEY `idx_kota` (`kota`);

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
-- Indexes for table `tbl_pengeluaran`
--
ALTER TABLE `tbl_pengeluaran`
  ADD PRIMARY KEY (`id_pengeluaran`),
  ADD UNIQUE KEY `no_pengeluaran` (`no_pengeluaran`),
  ADD KEY `fk_pengeluaran_pengguna` (`id_pengguna`),
  ADD KEY `idx_pengeluaran_tanggal` (`tanggal_pengeluaran`),
  ADD KEY `idx_pengeluaran_kategori` (`kategori_pengeluaran`);

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
  MODIFY `id_detail_penerimaan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_detail_transaksi`
--
ALTER TABLE `tbl_detail_transaksi`
  MODIFY `id_detail` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

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
  MODIFY `id_log` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

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
  MODIFY `id_mutasi_stok` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  MODIFY `id_pelanggan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_pembayaran`
--
ALTER TABLE `tbl_pembayaran`
  MODIFY `id_pembayaran` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tbl_penerimaan_barang`
--
ALTER TABLE `tbl_penerimaan_barang`
  MODIFY `id_penerimaan` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_pengeluaran`
--
ALTER TABLE `tbl_pengeluaran`
  MODIFY `id_pengeluaran` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_pengguna`
--
ALTER TABLE `tbl_pengguna`
  MODIFY `id_pengguna` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_produk`
--
ALTER TABLE `tbl_produk`
  MODIFY `id_produk` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_saldo_pelanggan`
--
ALTER TABLE `tbl_saldo_pelanggan`
  MODIFY `id_saldo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_supplier`
--
ALTER TABLE `tbl_supplier`
  MODIFY `id_supplier` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_transaksi`
--
ALTER TABLE `tbl_transaksi`
  MODIFY `id_transaksi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
-- Constraints for table `tbl_pengeluaran`
--
ALTER TABLE `tbl_pengeluaran`
  ADD CONSTRAINT `fk_pengeluaran_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tbl_pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE;

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

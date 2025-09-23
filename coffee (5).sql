-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 04, 2025 at 01:06 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coffee`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_admin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `is_super_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `foto` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`, `password`, `nama_admin`, `email`, `is_super_admin`, `created_at`, `foto`) VALUES
('fadil', '$2y$10$HUK4qMVVII1dnPHrMjq5OOZQrCGk7sovgjSl1Dlr5/4oW.9/pUuxi', 'fadilcs', 'fadilcs@gmail.com', 0, '2025-07-18 22:46:52', 'admin2.jpg'),
('nona', '$2y$10$PDewoFfMLFruYzHfjK2uWu77VeuDen2YtYf/06wxapRDU9cI6VMs6', 'githa', 'githanonaQ@gmail.com', 0, '2025-07-18 22:54:54', 'admin1.jpg'),
('rahma', '$2y$10$Z8kxLrTCyWOGKkwDyYNGmurBM3m5OHFTffkNRgelf6tGcuajLw7Nq', 'Rahmadana Cantika', 'rahma849@gmail.com', 1, '2025-07-30 00:45:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `kode_menu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_menu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kategori` enum('Coffe','Non Coffe') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `harga` int NOT NULL,
  `status` enum('Tersedia','Tidak Tersedia') NOT NULL DEFAULT 'Tersedia',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `stok` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`kode_menu`, `nama_menu`, `kategori`, `harga`, `status`, `created_at`, `stok`) VALUES
('KM001', 'vanila', 'Coffe', 10000, 'Tersedia', '2025-07-21 15:27:00', 963),
('KM002', 'vanila', 'Coffe', 12000, 'Tersedia', '2025-07-21 16:10:44', 3);

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `kode_pesanan` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_pelanggan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kode_menu` varchar(255) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `tgl_pesanan` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `jumlah` int NOT NULL,
  `status_pesanan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`kode_pesanan`, `nama_pelanggan`, `kode_menu`, `total_harga`, `tgl_pesanan`, `jumlah`, `status_pesanan`) VALUES
('PSN001', 'calu', 'KM002', '12048000.00', '2025-07-23 00:00:07', 1004, 'Menunggu'),
('PSN002', 'calu', 'KM001', '250000.00', '2025-07-23 00:01:25', 25, 'Diproses'),
('PSN003', 'nona', 'KM002', '12000.00', '2025-07-22 01:26:02', 1, 'Siap'),
('PSN004', 'loli', 'KM001', '400000.00', '2025-07-23 00:01:18', 40, 'Selesai');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `nama_staff` varchar(100) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text,
  `kode_gerobak` varchar(50) DEFAULT NULL,
  `lokasi_jualan` varchar(255) DEFAULT NULL,
  `tanggal_dibuat` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`nama_staff`, `jabatan`, `username`, `password`, `email`, `no_hp`, `alamat`, `kode_gerobak`, `lokasi_jualan`, `tanggal_dibuat`) VALUES
('Alya cantik', 'Staff', 'alya', '$2y$10$vxXg959WQS9yCw89oeMts.E6wyIsdoDxm6mXtwQixGSA6oP1E.2DC', 'alya2@gmail.com', '089978655673', 'sudiang', 'GRB 3', 'Jl. Somba Opu', '2025-07-21 15:26:18'),
('Fadil Gs', 'Staff', 'fadil', '$2y$10$tC/1q2/KY0GmJ.1jM1jkyOqyNPHGrHOmewr55zawHd9QUti2ZjTK2', 'fadilcs@gmail.com', '089923236546', 'Monceng loe, belang btp, dekat kampus polikteknik nergeri ujung pandang (PNUP kampus 2) no jl poros, blok F98, warna rumah coklat', 'GRB 1', 'Jl. Veteran', '2025-07-19 16:12:13'),
('Githa Bunga', 'Staff', 'nona', '$2y$10$SuQA4e9xIXX4LnQKBDyt5.TmM67UOAPsW0bBmkqCwMuAeH3w0iSh2', 'githanonaQ@gmail.com', '089923237536', 'momere Nusa Tengara Timur, kab.sikka, kampu ojang, atas nama githa  ', 'GRB 2', 'Jl. Veteran', '2025-07-19 16:15:33'),
('Lolitha', 'Staff', 'loli', '$2y$10$2NVnl5nZdOMUUwYbsjqlO.jJuHzaHfPFsyMKGZC/GY9d2JuEujv/6', 'loli90@gmail.com', '089454541213', 'Toraja Rantepao', 'GRB 5', 'Jl. Hertasning', '2025-07-26 18:41:36'),
('ubayy', 'Staff', 'ubay', '$2y$10$oymkxsUs/LRnQ8p0SZI8jON9x1OE2RrJuFfbkss5lt7Oi22N9CQtu', 'ubay23@gmail.com', '089976543212', 'perintis. jln masuk hotto', 'GRB 6', 'Jl. Perintis Kemerdekaan', '2025-07-26 18:45:56');

-- --------------------------------------------------------

--
-- Table structure for table `stok_history`
--

CREATE TABLE `stok_history` (
  `id` int NOT NULL,
  `kode_menu` varchar(50) NOT NULL,
  `stok_lama` int NOT NULL,
  `stok_baru` int NOT NULL,
  `tgl_update` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `keterangan` varchar(255) DEFAULT NULL,
  `harga` decimal(10,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stok_history`
--

INSERT INTO `stok_history` (`id`, `kode_menu`, `stok_lama`, `stok_baru`, `tgl_update`, `keterangan`, `harga`) VALUES
(285, 'KM001', 0, 1000, '2025-07-21 23:27:00', 'stok yang baru ditambahkan', NULL),
(286, 'KM001', 1000, 1001, '2025-07-21 23:27:05', 'Hari  Senin, pada bulan  Juli stok di ubah', NULL),
(287, 'KM001', 1001, 1002, '2025-07-21 23:27:14', 'Penambahan stok', NULL),
(288, 'KM001', 1001, 1002, '2025-07-21 23:27:14', 'Update stok pada hari Senin, bulan Juli', NULL),
(289, 'KM001', 1002, 1003, '2025-07-21 23:30:43', 'Update stok pada hari Senin, bulan Juli', NULL),
(290, 'KM001', 1003, 1004, '2025-07-21 23:32:12', 'Hari  Senin, pada bulan  Juli stok di ubah', NULL),
(291, 'KM001', 1004, 1005, '2025-07-21 23:32:23', 'Penambahan stok', NULL),
(292, 'KM001', 1004, 1005, '2025-07-21 23:32:23', 'Update stok pada hari Senin, bulan Juli', NULL),
(293, 'KM001', 1005, 1007, '2025-07-21 23:32:45', 'Penambahan stok', NULL),
(294, 'KM001', 1005, 1007, '2025-07-21 23:32:45', 'Update stok pada hari Senin, bulan Juli', NULL),
(295, 'KM001', 1007, 1005, '2025-07-21 23:35:50', 'Pengurangan stok', NULL),
(296, 'KM001', 1007, 1005, '2025-07-21 23:35:50', 'Update stok pada hari Senin, bulan Juli', NULL),
(297, 'KM001', 1005, 1006, '2025-07-21 23:37:25', 'Penambahan stok', NULL),
(298, 'KM001', 1005, 1006, '2025-07-21 23:37:25', 'Update stok pada hari Senin, bulan Juli', NULL),
(299, 'KM001', 1006, 1007, '2025-07-21 23:41:57', 'Penambahan stok', NULL),
(300, 'KM001', 1006, 1007, '2025-07-21 23:41:57', 'Update stok pada hari Senin, bulan Juli', NULL),
(301, 'KM001', 1007, 1004, '2025-07-21 23:46:20', 'Pengurangan stok', NULL),
(302, 'KM001', 1007, 1004, '2025-07-21 23:46:20', 'Update stok pada hari Senin, bulan Juli', NULL),
(303, 'KM001', 1004, 1003, '2025-07-21 23:48:16', 'Pengurangan stok', NULL),
(304, 'KM001', 1004, 1003, '2025-07-21 23:48:16', 'Update stok Senin/Juli', NULL),
(305, 'KM001', 1003, 1004, '2025-07-22 00:08:29', 'Hari  Selasa, pada bulan  Juli stok di ubah', NULL),
(306, 'KM001', 1004, 1005, '2025-07-22 00:08:40', 'Hari  Selasa, pada bulan  Juli stok di ubah', NULL),
(307, 'KM001', 1005, 1006, '2025-07-22 00:09:17', 'Penambahan stok', NULL),
(308, 'KM001', 1006, 1007, '2025-07-22 00:09:30', 'Penambahan stok', NULL),
(309, 'KM001', 1007, 1005, '2025-07-22 00:09:52', 'Pengurangan stok', NULL),
(310, 'KM002', 0, 1000, '2025-07-22 00:10:44', 'stok yang baru ditambahkan', NULL),
(311, 'KM002', 1000, 1001, '2025-07-22 00:11:11', 'Hari  Selasa, pada bulan  Juli stok di ubah', NULL),
(312, 'KM002', 1001, 1002, '2025-07-22 00:14:12', 'Penambahan stok', NULL),
(313, 'KM002', 1002, 1003, '2025-07-22 00:18:38', 'Penambahan stok', NULL),
(314, 'KM002', 1003, 1004, '2025-07-22 00:18:43', 'Penambahan stok', NULL),
(315, 'KM001', 1005, 1004, '2025-07-22 00:18:45', 'Pengurangan stok', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `email_221065` (`email`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`kode_menu`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`kode_pesanan`),
  ADD KEY `kode_menu` (`kode_menu`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`nama_staff`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `stok_history`
--
ALTER TABLE `stok_history`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `stok_history`
--
ALTER TABLE `stok_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=316;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`kode_menu`) REFERENCES `menu` (`kode_menu`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 18, 2025 at 10:03 PM
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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`, `password`, `nama_admin`, `email`, `is_super_admin`, `created_at`) VALUES
('admin', '$2y$10$24n7U98KT7eCM.ei0bxcyuGvdii9OkLw40sQ3vQLrMNloZ0iPDbb6', 'Super Admin', 'admin@cinema221065.com', 1, '2025-06-25 16:38:16');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `kode_menu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_menu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kategori` enum('Coffe','Non Coffe') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `harga` int NOT NULL,
  `status` enum('available','unavailable','discontinued') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `stok` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`kode_menu`, `nama_menu`, `kategori`, `harga`, `status`, `created_at`, `stok`) VALUES
('KM001', 'vanila', 'Non Coffe', 12000, 'available', '2025-07-14 08:17:49', 15),
('KM002', 'gula aren', 'Non Coffe', 15000, 'available', '2025-07-14 08:19:51', 5),
('KM003', 'coffe lathe', 'Coffe', 10000, 'available', '2025-07-14 08:20:29', 5),
('KM004', 'cokolate ', 'Non Coffe', 12000, 'available', '2025-07-17 20:28:02', 5),
('KM005', 'grean tea', 'Non Coffe', 13000, 'available', '2025-07-17 20:28:44', 10),
('KM006', 'Toramoka', 'Coffe', 12000, 'available', '2025-07-17 21:31:24', 9);

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `kode_pesanan` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_pelanggan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kode_menu` varchar(255) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `tgl_pesanan` datetime NOT NULL,
  `jumlah` int NOT NULL,
  `status_pesanan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`kode_pesanan`, `nama_pelanggan`, `kode_menu`, `total_harga`, `tgl_pesanan`, `jumlah`, `status_pesanan`) VALUES
('PSN001', 'sss', 'KM005', '39000.00', '2025-07-18 00:00:00', 3, 'pending'),
('PSN002', 'alya', 'KM003', '60000.00', '2025-07-18 00:00:00', 6, 'ready');

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
  `tanggal_dibuat` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`nama_staff`, `jabatan`, `username`, `password`, `email`, `no_hp`, `alamat`, `tanggal_dibuat`) VALUES
('calu', 'karyawan dipa', 'calu', '$2y$10$kgysYYRQG3Xa43g.jqYcxelbBAwaFzNDh9sdl1z16BvLiFnBmtI7K', 'efg@gmail.com', '000909909', 'dipa\r\n', '2025-07-14 10:06:46'),
('Rusdi', 'karyawan unhas', 'rusdi', '$2y$10$NCQH2Q9iTaYp7Vv1y.E2KO38haQoReWViI5OBqH6Xpfhcyne5u0Xy', 'abc@gmail.com', '081917345965', 'antang komplek id', '2025-07-14 10:05:11');

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

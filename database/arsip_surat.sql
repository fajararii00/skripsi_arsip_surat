-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 05, 2026 at 07:30 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `arsip_surat`
--

-- --------------------------------------------------------

--
-- Table structure for table `status_history`
--

CREATE TABLE `status_history` (
  `id` int(11) NOT NULL,
  `ref_type` enum('surat_keluar','surat_masuk','disposisi') NOT NULL,
  `ref_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_history`
--

INSERT INTO `status_history` (`id`, `ref_type`, `ref_id`, `status`, `keterangan`, `updated_by`, `created_at`) VALUES
(1, 'surat_keluar', 4, 'draft', 'Status awal', 1, '2026-03-07 12:35:51'),
(2, 'surat_keluar', 6, 'draft', 'Status awal', 1, '2026-04-23 05:10:05'),
(3, 'surat_keluar', 7, 'draft', 'Status awal', 1, '2026-04-23 06:18:19'),
(4, 'surat_keluar', 8, 'draft', 'Status awal', 1, '2026-04-23 06:25:34'),
(5, 'surat_keluar', 9, 'draft', 'Status awal', 1, '2026-04-23 06:28:05'),
(6, 'surat_masuk', 6, 'draft', 'Status awal', NULL, '2026-03-07 12:33:24'),
(7, 'surat_masuk', 7, 'draft', 'Status awal', NULL, '2026-04-23 05:07:04'),
(8, 'surat_masuk', 8, 'draft', 'Status awal', NULL, '2026-04-23 06:15:57'),
(9, 'surat_masuk', 9, 'draft', 'Status awal', NULL, '2026-04-23 06:19:55'),
(10, 'surat_masuk', 10, 'draft', 'Status awal', NULL, '2026-04-23 06:26:37'),
(11, 'surat_masuk', 11, 'draft', 'Status awal', NULL, '2026-07-22 04:06:05'),
(12, 'disposisi', 5, 'selesai', 'Status awal', 1, '2026-05-01 12:35:31'),
(13, 'disposisi', 6, 'selesai', 'Status awal', 1, '2026-05-01 12:35:58'),
(14, 'disposisi', 7, 'selesai', 'Status awal', 1, '2026-05-01 12:36:55'),
(15, 'disposisi', 8, 'diproses_tata_usaha', 'Status awal', 1, '2026-05-01 12:37:24'),
(16, 'disposisi', 9, 'draft', 'Status awal', 1, '2026-05-01 12:37:49');

-- --------------------------------------------------------

--
-- Table structure for table `disposisi`
--

CREATE TABLE `disposisi` (
  `id` int(11) NOT NULL,
  `surat_masuk_id` int(11) DEFAULT NULL,
  `pengirim_id` int(11) DEFAULT NULL,
  `penerima_id` int(11) DEFAULT NULL,
  `tgl_disposisi` date DEFAULT NULL,
  `instruksi` text DEFAULT NULL,
  `catatan_pimpinan` text DEFAULT NULL,
  `status` enum('draft','menunggu_verifikasi','terverifikasi','diproses_tata_usaha','selesai') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `disposisi`
--

INSERT INTO `disposisi` (`id`, `surat_masuk_id`, `pengirim_id`, `penerima_id`, `tgl_disposisi`, `instruksi`, `catatan_pimpinan`, `status`, `created_at`, `updated_at`) VALUES
(5, 7, 1, 2, '2026-04-01', '', NULL, 'selesai', '2026-05-01 12:35:31', '2026-05-01 12:35:31'),
(6, 6, 1, 2, '2026-04-10', '', NULL, 'selesai', '2026-05-01 12:35:58', '2026-05-01 12:35:58'),
(7, 8, 1, 2, '2026-06-25', '', NULL, 'selesai', '2026-05-01 12:36:55', '2026-05-01 12:36:55'),
(8, 10, 1, 2, '2026-04-27', '', NULL, 'diproses_tata_usaha', '2026-05-01 12:37:24', '2026-05-01 12:37:24'),
(9, 9, 1, 2, '2026-04-30', '', NULL, 'draft', '2026-05-01 12:37:49', '2026-05-01 12:37:49');

-- --------------------------------------------------------

--
-- Table structure for table `kode_surat`
--

CREATE TABLE `kode_surat` (
  `id` int(11) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kode_surat`
--

INSERT INTO `kode_surat` (`id`, `kode`, `nama`) VALUES
(1, '001', 'Surat Undangan'),
(2, '002', 'Surat Edaran'),
(3, '003', 'Surat Tugas'),
(4, '004', 'Surat Keterangan'),
(5, '005', 'Surat Pernyataan');

-- --------------------------------------------------------

--
-- Table structure for table `surat_keluar`
--

CREATE TABLE `surat_keluar` (
  `id` int(11) NOT NULL,
  `no_agenda` varchar(50) DEFAULT NULL,
  `no_surat` varchar(100) DEFAULT NULL,
  `kode_surat_id` int(11) DEFAULT NULL,
  `tgl_surat` date DEFAULT NULL,
  `tujuan` varchar(150) DEFAULT NULL,
  `instansi` varchar(150) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `perihal` varchar(255) DEFAULT NULL,
  `isi_surat` text DEFAULT NULL,
  `file_surat` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `pembuat_id` int(11) DEFAULT NULL,
  `penyetuju_id` int(11) DEFAULT NULL,
  `status` enum('draft','menunggu_verifikasi','terverifikasi','diproses_tata_usaha','selesai') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surat_keluar`
--

INSERT INTO `surat_keluar` (`id`, `no_agenda`, `no_surat`, `kode_surat_id`, `tgl_surat`, `tujuan`, `instansi`, `kategori`, `perihal`, `isi_surat`, `file_surat`, `qr_code`, `pembuat_id`, `penyetuju_id`, `status`, `created_at`) VALUES
(4, '01', '01', 1, '2026-03-02', 'Kantor PUPR', 'Dinas Pendidikan dan Kebudayaan', 'Penting', 'Bom Nuklir', 'Terjaga', '', NULL, 1, NULL, 'draft', '2026-03-07 12:35:51'),
(6, '02', '002', 1, '2026-03-12', 'Kantor PUPR', 'Dinas Pendidikan dan Kebudayaan', 'Penting', 'Privat', 'Rahasia', '', NULL, 1, NULL, 'draft', '2026-04-23 05:10:05'),
(7, '03', '003', 1, '2026-03-17', 'BADAN KEPEGAWAIAN DAERAH (BKD)', 'Dinas Pendidikan dan Kebudayaan Muaro Jambi', 'Privat', '.', 'Kepada yang terhormat kepala dinas ', '', NULL, 1, NULL, 'draft', '2026-04-23 06:18:19'),
(8, '04', '004', 1, '2026-03-24', 'Kantor Kehutanan dan Pertanian muaro Jambi', 'Dinas Pendidikan dan Kebudayaan Muaro Jambi', 'Privat', 'Penting', 'Rahasia', '', NULL, 1, NULL, 'draft', '2026-04-23 06:25:34'),
(9, '05', '005', 2, '2026-04-07', 'Dinas Perikanan', 'Dinas Pendidikan dan Kebudayaan Muaro Jambi', 'Privat', 'Masalah Ikan di Batanghari', 'Diharapkan Bisa Hadir', '', NULL, 1, NULL, 'draft', '2026-04-23 06:28:05');

-- --------------------------------------------------------

--
-- Table structure for table `surat_masuk`
--

CREATE TABLE `surat_masuk` (
  `id` int(11) NOT NULL,
  `no_agenda` varchar(50) DEFAULT NULL,
  `no_surat` varchar(100) DEFAULT NULL,
  `tgl_surat` date DEFAULT NULL,
  `tgl_diterima` date DEFAULT NULL,
  `pengirim` varchar(150) DEFAULT NULL,
  `instansi` varchar(150) DEFAULT NULL,
  `perihal` varchar(255) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `file_surat` varchar(255) DEFAULT NULL,
  `status` enum('draft','menunggu_verifikasi','terverifikasi','diproses_tata_usaha','selesai') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surat_masuk`
--

INSERT INTO `surat_masuk` (`id`, `no_agenda`, `no_surat`, `tgl_surat`, `tgl_diterima`, `pengirim`, `instansi`, `perihal`, `kategori`, `file_surat`, `status`, `created_at`) VALUES
(6, NULL, '01', '2026-03-02', '2026-03-03', 'Dinas Pertahanan', 'Dinas Pendidikan ', 'Kenaikan Pangkat', 'UMUM', '', 'baru', '2026-03-07 12:33:24'),
(7, NULL, '02', '2026-03-10', '2026-03-12', 'Dinas Pendidikan', 'SD N 67 Muaro Jambi', 'Ada Hal Yang Harus Dibahas', 'Privat', '', 'baru', '2026-04-23 05:07:04'),
(8, NULL, '03', '2026-03-17', '2026-05-18', 'Dinas Pendidikan dan Kebudayaan ', 'SMP N 5 Muaro Jambi', 'Ada Yang Mau Dibahas', 'Penting', '', 'baru', '2026-04-23 06:15:57'),
(9, NULL, '04', '2026-03-22', '2026-03-23', 'Dinas Pendidikan dan Kebudayaan', 'SD N 2 Muaro Jambi', 'MBG', 'Penting', '', 'baru', '2026-04-23 06:19:55'),
(10, NULL, '05', '2026-04-03', '2026-04-04', 'Dinas Pendidikan dan Kebudayaan', 'SMP N 6 Muaro Jambi', 'Persiapan Pensi', 'Penting', '', 'baru', '2026-04-23 06:26:37'),
(11, NULL, 'laskdfhlkjasd', '2026-07-22', '2026-07-20', 'dhflkhaldsfa', 'sfldksajfh;adlskfha', 'lsadkjfhalsdkjfhalsjkdhflasdjkhfljasdhflasdjfhlajdsfha', 'ldaskjfha;dslkfha', '1784693165_0a602bc7-f099-45a2-ab90-31205b8e576c.JPG', 'baru', '2026-07-22 04:06:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@mail.com', '$2y$12$5c676ZG0T0hLyw7Rw7Sl/.VaU84jEZEdq85Ij5PxGk.rxeD6YrXKu', 'admin', '2025-09-05 04:28:12'),
(2, 'Fajar', 'fajar@gmail.com', '$2y$10$t/.QsGLolVQMxO4XRqZ3FOuuxVlxZkfHSW9DBRhJJfUmyl92KUU2C', 'pimpinan', '2025-09-05 16:22:03'),
(3, 'Tata Usaha', 'tatausaha@mail.com', '$2y$12$F1Il8obtikbpnoC0EvGeE.7GHcxMrBY60kS1fbP/Z1eXRet9Yqb2O', 'tata_usaha', '2026-08-14 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `status_history`
--
ALTER TABLE `status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ref_type` (`ref_type`,`ref_id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `disposisi`
--
ALTER TABLE `disposisi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surat_masuk_id` (`surat_masuk_id`),
  ADD KEY `pengirim_id` (`pengirim_id`),
  ADD KEY `penerima_id` (`penerima_id`);

--
-- Indexes for table `kode_surat`
--
ALTER TABLE `kode_surat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indexes for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_surat_id` (`kode_surat_id`),
  ADD KEY `pembuat_id` (`pembuat_id`),
  ADD KEY `penyetuju_id` (`penyetuju_id`);

--
-- Indexes for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `status_history`
--
ALTER TABLE `status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `disposisi`
--
ALTER TABLE `disposisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `kode_surat`
--
ALTER TABLE `kode_surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `status_history`
--
ALTER TABLE `status_history`
  ADD CONSTRAINT `status_history_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `disposisi`
--
ALTER TABLE `disposisi`
  ADD CONSTRAINT `disposisi_ibfk_1` FOREIGN KEY (`surat_masuk_id`) REFERENCES `surat_masuk` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disposisi_ibfk_2` FOREIGN KEY (`pengirim_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `disposisi_ibfk_3` FOREIGN KEY (`penerima_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD CONSTRAINT `surat_keluar_ibfk_1` FOREIGN KEY (`pembuat_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `surat_keluar_ibfk_2` FOREIGN KEY (`penyetuju_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `surat_keluar_ibfk_3` FOREIGN KEY (`kode_surat_id`) REFERENCES `kode_surat` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

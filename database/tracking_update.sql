-- ============================================================
-- MIGRASI FITUR TRACKING SURAT
-- Alur status: draft -> menunggu_verifikasi -> terverifikasi
--              -> diproses_kasi_pais -> selesai
-- Jalankan script ini di phpMyAdmin (database arsip_surat)
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- --------------------------------------------------------
-- 1. Tabel baru: status_history (riwayat pergerakan status)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `status_history` (
  `id` int(11) NOT NULL,
  `ref_type` enum('surat_keluar','surat_masuk','disposisi') NOT NULL,
  `ref_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ref_type` (`ref_type`,`ref_id`),
  ADD KEY `updated_by` (`updated_by`);

ALTER TABLE `status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `status_history`
  ADD CONSTRAINT `status_history_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- --------------------------------------------------------
-- 2. Mapping data lama -> baru (SEBELUM ubah enum)
-- --------------------------------------------------------
UPDATE `surat_keluar` SET `status` = 'draft'               WHERE `status` = 'draft';
UPDATE `surat_keluar` SET `status` = 'menunggu_verifikasi' WHERE `status` = 'menunggu';
UPDATE `surat_keluar` SET `status` = 'terverifikasi'       WHERE `status` = 'disetujui';
UPDATE `surat_keluar` SET `status` = 'selesai'             WHERE `status` = 'dikirim';

UPDATE `surat_masuk` SET `status` = 'draft'               WHERE `status` = 'baru';
UPDATE `surat_masuk` SET `status` = 'terverifikasi'       WHERE `status` = 'didisposisikan';
UPDATE `surat_masuk` SET `status` = 'selesai'             WHERE `status` = 'selesai';

UPDATE `disposisi` SET `status` = 'draft'                 WHERE `status` = 'pending';
UPDATE `disposisi` SET `status` = 'diproses_kasi_pais'    WHERE `status` = 'proses';
UPDATE `disposisi` SET `status` = 'selesai'               WHERE `status` = 'selesai';

-- --------------------------------------------------------
-- 3. Ubah enum surat_keluar.status
-- --------------------------------------------------------
ALTER TABLE `surat_keluar` MODIFY `status` enum('draft','menunggu_verifikasi','terverifikasi','diproses_kasi_pais','selesai') DEFAULT 'draft';

-- --------------------------------------------------------
-- 4. Ubah enum surat_masuk.status
-- --------------------------------------------------------
ALTER TABLE `surat_masuk` MODIFY `status` enum('draft','menunggu_verifikasi','terverifikasi','diproses_kasi_pais','selesai') DEFAULT 'draft';

-- --------------------------------------------------------
-- 5. Ubah enum disposisi.status
-- --------------------------------------------------------
ALTER TABLE `disposisi` MODIFY `status` enum('draft','menunggu_verifikasi','terverifikasi','diproses_kasi_pais','selesai') DEFAULT 'draft';

-- --------------------------------------------------------
-- 6. Perbaikan tambahan: nilai kosong (akibat enum lama
--    ter-clobber) dikembalikan ke 'draft'
-- --------------------------------------------------------
UPDATE `surat_masuk` SET `status` = 'draft' WHERE `status` = '' OR `status` IS NULL;
UPDATE `disposisi` SET `status` = 'draft' WHERE `status` = '' OR `status` IS NULL;

-- --------------------------------------------------------
-- 7. Backfill riwayat status dari data yang sudah ada
-- --------------------------------------------------------
INSERT INTO `status_history` (`ref_type`, `ref_id`, `status`, `keterangan`, `updated_by`, `created_at`)
SELECT 'surat_keluar', `id`, `status`, 'Status awal', `pembuat_id`, `created_at`
FROM `surat_keluar`;

INSERT INTO `status_history` (`ref_type`, `ref_id`, `status`, `keterangan`, `updated_by`, `created_at`)
SELECT 'surat_masuk', `id`, `status`, 'Status awal', NULL, `created_at`
FROM `surat_masuk`;

INSERT INTO `status_history` (`ref_type`, `ref_id`, `status`, `keterangan`, `updated_by`, `created_at`)
SELECT 'disposisi', `id`, `status`, 'Status awal', `pengirim_id`, `created_at`
FROM `disposisi`;

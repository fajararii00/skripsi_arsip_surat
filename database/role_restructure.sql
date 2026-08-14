-- ============================================================
-- MIGRASI RESTRUKTURISASI ROLE & STATUS
-- Perubahan role: staf -> pimpinan, user -> tata_usaha
-- Perubahan status: diproses_kasi_pais -> diproses_tata_usaha
-- Jalankan script ini di phpMyAdmin (database arsip_surat)
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- --------------------------------------------------------
-- 1. Rename role users
-- --------------------------------------------------------
UPDATE `users` SET `role` = 'pimpinan'    WHERE `role` = 'staf';
UPDATE `users` SET `role` = 'tata_usaha'  WHERE `role` = 'user';

-- --------------------------------------------------------
-- 2. Rename nilai status di semua tabel & riwayat
-- --------------------------------------------------------
UPDATE `surat_keluar` SET `status` = 'diproses_tata_usaha' WHERE `status` = 'diproses_kasi_pais';
UPDATE `surat_masuk`  SET `status` = 'diproses_tata_usaha' WHERE `status` = 'diproses_kasi_pais';
UPDATE `disposisi`    SET `status` = 'diproses_tata_usaha' WHERE `status` = 'diproses_kasi_pais';
UPDATE `status_history` SET `status` = 'diproses_tata_usaha' WHERE `status` = 'diproses_kasi_pais';

-- Bersihkan nilai status kosong (akibat enum lama)
UPDATE `surat_masuk`  SET `status` = 'draft' WHERE `status` = '' OR `status` IS NULL;
UPDATE `surat_keluar` SET `status` = 'draft' WHERE `status` = '' OR `status` IS NULL;
UPDATE `disposisi`    SET `status` = 'draft' WHERE `status` = '' OR `status` IS NULL;

-- --------------------------------------------------------
-- 3. Ubah definisi ENUM status
-- --------------------------------------------------------
ALTER TABLE `surat_keluar` MODIFY `status` enum('draft','menunggu_verifikasi','terverifikasi','diproses_tata_usaha','selesai') DEFAULT 'draft';
ALTER TABLE `surat_masuk`  MODIFY `status` enum('draft','menunggu_verifikasi','terverifikasi','diproses_tata_usaha','selesai') DEFAULT 'draft';
ALTER TABLE `disposisi`    MODIFY `status` enum('draft','menunggu_verifikasi','terverifikasi','diproses_tata_usaha','selesai') DEFAULT 'draft';

-- --------------------------------------------------------
-- 4. User contoh: Tata Usaha
--    Email : tatausaha@mail.com
--    Password : tatausaha123
-- --------------------------------------------------------
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `created_at`)
VALUES ('Tata Usaha', 'tatausaha@mail.com', '$2y$12$F1Il8obtikbpnoC0EvGeE.7GHcxMrBY60kS1fbP/Z1eXRet9Yqb2O', 'tata_usaha', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `role` = 'tata_usaha';

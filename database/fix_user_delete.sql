-- ============================================================
-- FIX: User tidak bisa dihapus karena FK RESTRICT
-- Mengubah foreign key user menjadi ON DELETE SET NULL
-- agar data surat/disposisi tetap tersimpan, referensi user
-- otomatis menjadi NULL saat user dihapus.
-- Jalankan script ini di phpMyAdmin (database arsip_surat)
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- Hapus foreign key lama (RESTRICT)
ALTER TABLE `disposisi` DROP FOREIGN KEY `disposisi_ibfk_2`;
ALTER TABLE `disposisi` DROP FOREIGN KEY `disposisi_ibfk_3`;
ALTER TABLE `surat_keluar` DROP FOREIGN KEY `surat_keluar_ibfk_1`;
ALTER TABLE `surat_keluar` DROP FOREIGN KEY `surat_keluar_ibfk_2`;

-- Tambahkan kembali dengan ON DELETE SET NULL
ALTER TABLE `disposisi`
  ADD CONSTRAINT `disposisi_ibfk_2` FOREIGN KEY (`pengirim_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `disposisi_ibfk_3` FOREIGN KEY (`penerima_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `surat_keluar`
  ADD CONSTRAINT `surat_keluar_ibfk_1` FOREIGN KEY (`pembuat_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `surat_keluar_ibfk_2` FOREIGN KEY (`penyetuju_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

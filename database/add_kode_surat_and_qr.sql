-- Migration: Tambah tabel kode_surat, kolom kode_surat_id & qr_code di surat_keluar
-- Jalankan file ini di phpMyAdmin atau MySQL CLI

-- --------------------------------------------------------
-- 1. Buat tabel kode_surat
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kode_surat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Insert data awal (contoh kode surat)
-- --------------------------------------------------------
INSERT INTO `kode_surat` (`kode`, `nama`) VALUES
('001', 'Surat Undangan'),
('002', 'Surat Edaran'),
('003', 'Surat Tugas'),
('004', 'Surat Keterangan'),
('005', 'Surat Pernyataan');

-- --------------------------------------------------------
-- 3. Tambah kolom kode_surat_id dan qr_code ke tabel surat_keluar
-- --------------------------------------------------------
ALTER TABLE `surat_keluar`
  ADD COLUMN `kode_surat_id` int(11) DEFAULT NULL AFTER `no_surat`,
  ADD COLUMN `qr_code` varchar(255) DEFAULT NULL AFTER `file_surat`;

-- --------------------------------------------------------
-- 4. Hapus UNIQUE constraint pada no_surat
--    (karena no_surat sekarang per kode/bulan/tahun, bisa duplikat di bulan berbeda)
-- --------------------------------------------------------
ALTER TABLE `surat_keluar`
  DROP INDEX IF EXISTS `no_surat`;

-- --------------------------------------------------------
-- 5. Tambah foreign key untuk kode_surat_id
-- --------------------------------------------------------
ALTER TABLE `surat_keluar`
  ADD CONSTRAINT `surat_keluar_ibfk_3` FOREIGN KEY (`kode_surat_id`) REFERENCES `kode_surat` (`id`) ON DELETE SET NULL;

-- --------------------------------------------------------
-- 6. Update existing records: map kode surat berdasarkan data lama
--    (opsional - sesuaikan jika perlu)
-- --------------------------------------------------------
UPDATE `surat_keluar` SET `kode_surat_id` = 1 WHERE `no_surat` IN ('01', '002', '003', '004', '005');

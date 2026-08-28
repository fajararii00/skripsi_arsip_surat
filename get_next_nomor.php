<?php
include "includes/db.php";

header('Content-Type: application/json');

$kode_surat_id = isset($_GET['kode_surat_id']) ? intval($_GET['kode_surat_id']) : 0;
$tgl_surat = isset($_GET['tgl_surat']) ? $_GET['tgl_surat'] : '';

if ($kode_surat_id <= 0 || empty($tgl_surat)) {
    echo json_encode(['error' => 'Parameter tidak valid']);
    exit;
}

// Ambil kode dari tabel kode_surat
$kode_result = mysqli_query($conn, "SELECT kode FROM kode_surat WHERE id=$kode_surat_id");
$kode_row = mysqli_fetch_assoc($kode_result);
if (!$kode_row) {
    echo json_encode(['error' => 'Kode surat tidak ditemukan']);
    exit;
}

$kode = $kode_row['kode'];
$bulan = date('m', strtotime($tgl_surat));
$tahun = date('Y', strtotime($tgl_surat));

// Hitung jumlah surat dengan kode + bulan + tahun yang sama
$count_result = mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM surat_keluar 
     WHERE kode_surat_id=$kode_surat_id 
     AND MONTH(tgl_surat)='$bulan' 
     AND YEAR(tgl_surat)='$tahun'"
);
$count_row = mysqli_fetch_assoc($count_result);
$next_nomor = $count_row['total'] + 1;
$nomor_padded = str_pad($next_nomor, 4, '0', STR_PAD_LEFT);

$preview = "$kode/$bulan/$tahun/$nomor_padded";

echo json_encode([
    'kode' => $kode,
    'bulan' => $bulan,
    'tahun' => $tahun,
    'nomor' => $nomor_padded,
    'preview' => $preview
]);
?>

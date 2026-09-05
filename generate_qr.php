<?php
include "includes/db.php";
include "libs/phpqrcode/qrlib.php";

header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID tidak valid']);
    exit;
}

// Pastikan direktori ada dan writable
$qr_dir = __DIR__ . "/assets/uploads/qr_codes/";
if (!is_dir($qr_dir)) {
    mkdir($qr_dir, 0777, true);
}

// Generate QR
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            ? 'https' : 'http';
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$cek_url   = $protocol . '://' . $_SERVER['HTTP_HOST'] . $base_path . '/cek_legalitas.php?id=' . $id;
$qr_file   = $qr_dir . 'surat_' . $id . '.png';

@QRcode::png($cek_url, $qr_file, QR_ECLEVEL_M, 8, 2);

if (file_exists($qr_file) && filesize($qr_file) > 0) {
    $qr_path = 'surat_' . $id . '.png';
    mysqli_query($conn, "UPDATE surat_keluar SET qr_code='$qr_path' WHERE id=$id");
    echo json_encode(['success' => true, 'qr' => $qr_path]);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal generate QR code - periksa permission folder']);
}
?>

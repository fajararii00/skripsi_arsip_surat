<?php
include "../includes/auth.php";
include "../includes/db.php";

// hanya admin yang boleh hapus
requireRole(['admin',]);


$id = intval($_GET['id']);

// ambil data file untuk dihapus dari folder juga
$result = mysqli_query($conn, "SELECT file_surat FROM surat_keluar WHERE id=$id");
$row = mysqli_fetch_assoc($result);

if ($row && $row['file_surat']) {
    $file_path = __DIR__ . "/../assets/uploads/surat_keluar/" . $row['file_surat'];
    if (file_exists($file_path)) {
        unlink($file_path); // hapus file
    }
}

// hapus dari database
if (mysqli_query($conn, "DELETE FROM surat_keluar WHERE id=$id")) {
    $_SESSION['toast_success'] = "Surat keluar berhasil dihapus!";
} else {
    $_SESSION['toast_error'] = "Gagal menghapus surat keluar: " . mysqli_error($conn);
}

header("Location: surat_keluar.php");
exit;


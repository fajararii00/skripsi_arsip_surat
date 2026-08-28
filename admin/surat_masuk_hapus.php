<?php
include "../includes/auth.php";
include "../includes/db.php";

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT file_surat FROM surat_masuk WHERE id=$id");
$data = mysqli_fetch_assoc($result);

// Hapus file jika ada
if ($data && $data['file_surat']) {
    $file_path = "../assets/uploads/surat_masuk/" . $data['file_surat'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Hapus data di DB
if (mysqli_query($conn, "DELETE FROM surat_masuk WHERE id=$id")) {
    $_SESSION['toast_success'] = "Surat masuk berhasil dihapus!";
} else {
    $_SESSION['toast_error'] = "Gagal menghapus surat masuk: " . mysqli_error($conn);
}
header("Location: surat_masuk.php");
exit;


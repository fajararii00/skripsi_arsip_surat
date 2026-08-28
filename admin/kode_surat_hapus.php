<?php
include "../includes/auth.php";
include "../includes/db.php";

requireRole(['admin']);

if (!isset($_GET['id'])) {
    header("Location: kode_surat.php");
    exit;
}
$id = intval($_GET['id']);

// Hapus kode surat
$query = "DELETE FROM kode_surat WHERE id=$id";
if (mysqli_query($conn, $query)) {
    header("Location: kode_surat.php");
    exit;
} else {
    die("Gagal menghapus kode surat: " . mysqli_error($conn));
}
?>

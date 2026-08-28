<?php
include "../includes/auth.php";
include "../includes/db.php";

$id = intval($_GET['id']);

// Cek apakah disposisi ada
$result = mysqli_query($conn, "SELECT id FROM disposisi WHERE id=$id");
if (mysqli_num_rows($result) == 0) {
    die("Disposisi tidak ditemukan");
}

// Hapus disposisi
if (mysqli_query($conn, "DELETE FROM disposisi WHERE id=$id")) {
    $_SESSION['toast_success'] = "Disposisi berhasil dihapus!";
} else {
    $_SESSION['toast_error'] = "Gagal menghapus disposisi: " . mysqli_error($conn);
}

header("Location: disposisi.php");
exit;

?>

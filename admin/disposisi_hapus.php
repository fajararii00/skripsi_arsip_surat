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
mysqli_query($conn, "DELETE FROM disposisi WHERE id=$id");

header("Location: disposisi.php");
exit;
?>

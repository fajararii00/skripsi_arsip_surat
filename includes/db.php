<?php
// Konfigurasi Database Lokal (Laragon)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "arsip_surat";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>

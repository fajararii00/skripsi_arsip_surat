<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// pastikan hanya tata usaha
if ($_SESSION['role'] != 'tata_usaha') {
    header("Location: ../login.php");
    exit;
}

$type = $_GET['type'] ?? '';
$id   = intval($_GET['id'] ?? 0);
$next = $_GET['next'] ?? '';

$tables = ['surat_masuk', 'surat_keluar'];
if (!in_array($type, $tables) || $id <= 0) {
    header("Location: index.php");
    exit;
}

$surat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM $type WHERE id=$id"));
if (!$surat) {
    header("Location: index.php");
    exit;
}

if (canTransition('tata_usaha', $type, $surat['status'], $next)) {
    mysqli_query($conn, "UPDATE $type SET status='$next' WHERE id=$id");
    logStatus($conn, $type, $id, $next, 'Diperbarui dari dashboard');
}

header("Location: index.php");
exit;
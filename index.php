<?php
session_start();

// Jika pengguna sudah login, alihkan ke dashboard masing-masing sesuai role
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
        exit;
    } elseif ($_SESSION['role'] === 'pimpinan') {
        header("Location: pimpinan/index.php");
        exit;
    } elseif ($_SESSION['role'] === 'tata_usaha') {
        header("Location: tata_usaha/index.php");
        exit;
    }
}

// Jika belum login, alihkan ke halaman login.php
header("Location: login.php");
exit;
?>

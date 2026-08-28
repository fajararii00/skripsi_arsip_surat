<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nama'] = 'Administrator';
$_SESSION['role'] = 'admin';
ob_start();
include 'admin/surat_masuk.php';
$html = ob_get_clean();
// Potong hanya bagian navbar untuk inspeksi
$start = strpos($html, '<nav');
$end = strpos($html, '</nav>') + 6;
echo substr($html, $start, $end - $start);

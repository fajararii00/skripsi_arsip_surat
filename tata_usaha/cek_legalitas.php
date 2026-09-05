<?php
// Fallback redirect untuk kompatibilitas QR code lama
$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: ../cek_legalitas.php" . $qs);
exit;
?>

<?php
session_start();
session_unset();
session_destroy();

session_start();
$_SESSION['toast_success'] = "Berhasil logout dari sistem.";

// arahkan kembali ke halaman login
header("Location: login.php");
exit;


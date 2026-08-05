<?php
session_start();
session_unset();
session_destroy();

// arahkan kembali ke halaman login
header("Location: login.php");
exit;

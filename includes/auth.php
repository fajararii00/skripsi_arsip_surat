<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// 🔑 fungsi cek role
function requireRole($roles = []) {
    if (!in_array($_SESSION['role'], $roles)) {
        $_SESSION['error_message'] = "Anda tidak memiliki akses ke halaman ini!";
        
        if ($_SESSION['role'] == 'admin') {
            header("Location: ../admin/index.php");
        } elseif ($_SESSION['role'] == 'staf') {
            header("Location: ../staf/index.php");
        } else {
            header("Location: ../login.php");
        }
        exit;
    }
}

?>

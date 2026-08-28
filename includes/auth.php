<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Label tampilan per role
function getRoleLabel($role) {
    $labels = [
        'admin'      => 'Admin',
        'pimpinan'   => 'Pimpinan',
        'tata_usaha' => 'Tata Usaha',
    ];
    return isset($labels[$role]) ? $labels[$role] : ucfirst($role);
}

// Fungsi cek role
function requireRole($roles = []) {
    if (!in_array($_SESSION['role'], $roles)) {
        $_SESSION['error_message'] = "Anda tidak memiliki akses ke halaman ini!";
        
        if ($_SESSION['role'] == 'admin') {
            header("Location: ../admin/index.php");
        } elseif ($_SESSION['role'] == 'pimpinan') {
            header("Location: ../pimpinan/index.php");
        } elseif ($_SESSION['role'] == 'tata_usaha') {
            header("Location: ../tata_usaha/index.php");
        } else {
            header("Location: ../login.php");
        }
        exit;
    }
}

?>

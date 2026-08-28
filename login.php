<?php
session_start();
include "includes/db.php"; // koneksi database

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: admin/index.php");
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // cek password (gunakan password_hash di register)
        if (password_verify($password, $user['password'])) {
            // simpan ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['toast_success'] = "Berhasil login! Selamat datang, " . $user['nama'];

            // redirect sesuai role
            if ($user['role'] == 'admin') {
                header("Location: admin/index.php");
                exit;
            } elseif ($user['role'] == 'pimpinan') {
                header("Location: pimpinan/index.php");
                exit;
            } elseif ($user['role'] == 'tata_usaha') {
                header("Location: tata_usaha/index.php");
                exit;
            } else {
                header("Location: login.php"); // fallback kalau role tidak dikenali
                exit;
            }

        } else {
            $error = "Email atau password salah!";
            $_SESSION['toast_error'] = $error;
        }
    } else {
        $error = "Email atau password salah!";
        $_SESSION['toast_error'] = $error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Arsip Surat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo-disdikbud.png">
    <style>
        body {
            background: linear-gradient(135deg, #f0f4ff, #dff6ff);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border-radius: 16px;
        }
        .logo {
            max-width: 100px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="card shadow-lg p-4 text-center" style="width: 400px;">

        <!-- Logo Kantor -->
        <div class="mb-2">
            <img src="images.jpg" alt="Logo DISDIKBUD" class="logo rounded-circle shadow-sm">
        </div>
        <h5 class="fw-bold mb-1">DISDIKBUD</h5>
        <p class="text-muted small mb-3">Dinas Pendidikan dan Kebudayaan Kab. Muaro Jambi</p>

        <h4 class="text-center mb-4">Login Arsip Surat</h4>



        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="passwordInput" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fa fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const toggleIcon = document.getElementById('toggleIcon');

    if (togglePassword && passwordInput && toggleIcon) {
        togglePassword.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
    }
});
</script>
<?php include "includes/toast.php"; ?>
</body>
</html>





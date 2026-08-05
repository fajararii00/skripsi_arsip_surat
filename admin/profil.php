<?php
include "../includes/auth.php";
include "../includes/db.php";

// Ambil data user login
$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET nama='$nama', email='$email', password='$hashed' WHERE id='$user_id'";
    } else {
        $query = "UPDATE users SET nama='$nama', email='$email' WHERE id='$user_id'";
    }

    if (mysqli_query($conn, $query)) {
        $_SESSION['nama'] = $nama; // update session supaya nama baru langsung muncul di navbar
        $msg = "✅ Profil berhasil diperbarui!";
    } else {
        $msg = "❌ Gagal update profil: " . mysqli_error($conn);
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="fa fa-user"></i> Profil Saya</h5>
        </div>
        <div class="card-body">
          <?php if($msg): ?>
            <div class="alert alert-info"><?= $msg ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Nama</label>
              <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password Baru</label>
              <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin ganti">
            </div>
            <div class="d-flex justify-content-between">
              <a href="index.php" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Update Profil
              </button>
              <a href="../logout.php" class="btn btn-danger">
                <i class="fa fa-sign-out-alt"></i> Logout
              </a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>


<?php include "../includes/footer.php"; ?>

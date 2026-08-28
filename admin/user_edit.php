<?php
include "../includes/auth.php";
include "../includes/db.php";

$id = intval($_GET['id']);
$msg = "";

// Ambil data user lama
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$id"));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Jika password diisi, update password
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE users SET nama='$nama', email='$email', password='$password', role='$role' WHERE id=$id";
    } else {
        $query = "UPDATE users SET nama='$nama', email='$email', role='$role' WHERE id=$id";
    }

    if (mysqli_query($conn, $query)) {
        $_SESSION['toast_success'] = "Data user berhasil diperbarui!";
        header("Location: users.php");
        exit;
    } else {
        $msg = "Gagal mengupdate user: " . mysqli_error($conn);
        $_SESSION['toast_error'] = $msg;
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold">Edit User</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']); ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password (Kosongkan jika tidak diganti)</label>
      <input type="password" name="password" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Role</label>
      <select name="role" class="form-control" required>
        <option value="admin" <?= $user['role']=='admin'?'selected':''; ?>>Admin</option>
        <option value="pimpinan" <?= $user['role']=='pimpinan'?'selected':''; ?>>Pimpinan</option>
        <option value="tata_usaha" <?= $user['role']=='tata_usaha'?'selected':''; ?>>Tata Usaha</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="users.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>

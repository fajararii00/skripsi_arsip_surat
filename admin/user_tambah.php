<?php
include "../includes/auth.php";
include "../includes/db.php";

$nama = $email = $password = $role = "";
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $query = "INSERT INTO users (nama, email, password, role) 
              VALUES ('$nama', '$email', '$password', '$role')";
    if (mysqli_query($conn, $query)) {
        header("Location: users.php");
        exit;
    } else {
        $msg = "Gagal menambah user: " . mysqli_error($conn);
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold">Tambah User</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input type="text" name="nama" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Role</label>
      <select name="role" class="form-control" required>
        <option value="staf">Staf</option>
        <option value="user">User</option>
      </select>
    </div>
    <button type="submit" class="btn btn-success">Simpan</button>
    <a href="users.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>

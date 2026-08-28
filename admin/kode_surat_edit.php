<?php
include "../includes/auth.php";
include "../includes/db.php";

requireRole(['admin']);

if (!isset($_GET['id'])) {
    header("Location: kode_surat.php");
    exit;
}
$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM kode_surat WHERE id=$id");
$kode = mysqli_fetch_assoc($result);
if (!$kode) {
    echo "<div class='alert alert-danger'>Data tidak ditemukan</div>";
    exit;
}

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kode_val = mysqli_real_escape_string($conn, trim($_POST['kode']));
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));

    if (empty($kode_val) || empty($nama)) {
        $msg = "Semua field wajib diisi!";
    } else {
        // Cek duplikat kode (kecuali id yang sedang diedit)
        $cek = mysqli_query($conn, "SELECT id FROM kode_surat WHERE kode='$kode_val' AND id!=$id");
        if (mysqli_num_rows($cek) > 0) {
            $msg = "Kode surat '$kode_val' sudah digunakan!";
        } else {
            $query = "UPDATE kode_surat SET kode='$kode_val', nama='$nama' WHERE id=$id";
            if (mysqli_query($conn, $query)) {
                header("Location: kode_surat.php");
                exit;
            } else {
                $msg = "Gagal update: " . mysqli_error($conn);
            }
        }
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3"><i class="fa fa-edit"></i> Edit Kode Surat</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Kode <small class="text-muted">(misal: 001, 002)</small></label>
      <input type="text" name="kode" class="form-control" required maxlength="10"
             value="<?= htmlspecialchars($kode['kode']); ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Nama Jenis Surat</label>
      <input type="text" name="nama" class="form-control" required maxlength="100"
             value="<?= htmlspecialchars($kode['nama']); ?>">
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="kode_surat.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>

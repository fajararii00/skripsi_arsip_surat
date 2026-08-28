<?php
include "../includes/auth.php";
include "../includes/db.php";

requireRole(['admin']);

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kode = mysqli_real_escape_string($conn, trim($_POST['kode']));
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));

    if (empty($kode) || empty($nama)) {
        $msg = "Semua field wajib diisi!";
    } else {
        // Cek duplikat kode
        $cek = mysqli_query($conn, "SELECT id FROM kode_surat WHERE kode='$kode'");
        if (mysqli_num_rows($cek) > 0) {
            $msg = "Kode surat '$kode' sudah ada!";
        } else {
            $query = "INSERT INTO kode_surat (kode, nama) VALUES ('$kode', '$nama')";
            if (mysqli_query($conn, $query)) {
                header("Location: kode_surat.php");
                exit;
            } else {
                $msg = "Gagal menambahkan kode surat: " . mysqli_error($conn);
            }
        }
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3"><i class="fa fa-plus"></i> Tambah Kode Surat</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Kode <small class="text-muted">(misal: 001, 002)</small></label>
      <input type="text" name="kode" class="form-control" required maxlength="10"
             placeholder="Masukkan kode surat">
    </div>
    <div class="mb-3">
      <label class="form-label">Nama Jenis Surat</label>
      <input type="text" name="nama" class="form-control" required maxlength="100"
             placeholder="Masukkan nama jenis surat">
    </div>
    <button type="submit" class="btn btn-success">Simpan</button>
    <a href="kode_surat.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>

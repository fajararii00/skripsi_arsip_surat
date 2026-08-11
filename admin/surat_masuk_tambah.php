<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_surat     = mysqli_real_escape_string($conn, $_POST['no_surat']);
    $tgl_surat    = mysqli_real_escape_string($conn, $_POST['tgl_surat']);
    $tgl_diterima = mysqli_real_escape_string($conn, $_POST['tgl_diterima']);
    $pengirim     = mysqli_real_escape_string($conn, $_POST['pengirim']);
    $instansi     = mysqli_real_escape_string($conn, $_POST['instansi']);
    $kategori     = mysqli_real_escape_string($conn, $_POST['kategori']);
    $perihal      = mysqli_real_escape_string($conn, $_POST['perihal']);
    $status       = "draft";
    
    // Upload file
    $file_name = null;
    if (!empty($_FILES['file_surat']['name'])) {
        $file_name = time() . "_" . basename($_FILES['file_surat']['name']);
        $target_dir = __DIR__ . "/../assets/uploads/surat_masuk/"; 
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // bikin folder kalau belum ada
        }
        $target = $target_dir . $file_name;

        if (!move_uploaded_file($_FILES['file_surat']['tmp_name'], $target)) {
            $msg = "Upload file gagal!";
        }
    }

    if (!$msg) {
        $query = "INSERT INTO surat_masuk 
                  (no_surat, tgl_surat, tgl_diterima, pengirim, instansi, kategori, perihal, file_surat, status) 
                  VALUES 
                  ('$no_surat','$tgl_surat','$tgl_diterima','$pengirim','$instansi','$kategori','$perihal','$file_name','$status')";
        if (mysqli_query($conn, $query)) {
            $new_id = mysqli_insert_id($conn);
            logStatus($conn, 'surat_masuk', $new_id, $status, 'Surat diterima');
            header("Location: surat_masuk.php");
            exit;
        } else {
            $msg = "Gagal menambahkan surat: " . mysqli_error($conn);
        }
    }
}
?>

<?php include "../includes/header.php"; ?>

<h3 class="fw-bold mb-3">Tambah Surat Masuk</h3>
<?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="form-label">Nomor Surat</label>
    <input type="text" name="no_surat" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Tanggal Surat</label>
    <input type="date" name="tgl_surat" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Tanggal Diterima</label>
    <input type="date" name="tgl_diterima" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Pengirim</label>
    <input type="text" name="pengirim" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Instansi</label>
    <input type="text" name="instansi" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Kategori</label>
    <input type="text" name="kategori" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Perihal</label>
    <textarea name="perihal" class="form-control" required></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">File Surat (PDF/JPG)</label>
    <input type="file" name="file_surat" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
  </div>
  <button type="submit" class="btn btn-success">Simpan</button>
  <a href="surat_masuk.php" class="btn btn-secondary">Kembali</a>
</form>

<?php include "../includes/footer.php"; ?>

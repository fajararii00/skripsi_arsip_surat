<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// hanya admin yang boleh akses (tata usaha punya halaman sendiri)
requireRole(['admin']);

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_agenda  = mysqli_real_escape_string($conn, $_POST['no_agenda']);
    $no_surat   = mysqli_real_escape_string($conn, $_POST['no_surat']);
    $tgl_surat  = mysqli_real_escape_string($conn, $_POST['tgl_surat']);
    $tujuan     = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $instansi   = mysqli_real_escape_string($conn, $_POST['instansi']);
    $kategori   = mysqli_real_escape_string($conn, $_POST['kategori']);
    $perihal    = mysqli_real_escape_string($conn, $_POST['perihal']);
    $isi_surat  = mysqli_real_escape_string($conn, $_POST['isi_surat']);
    $pembuat_id = $_SESSION['user_id']; // ambil dari user login
    $status     = "draft";

    // Upload file
    $file_name = null;
    if (!empty($_FILES['file_surat']['name'])) {
        $file_name = time() . "_" . basename($_FILES['file_surat']['name']);
        $target_dir = __DIR__ . "/../assets/uploads/surat_keluar/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target = $target_dir . $file_name;
        if (!move_uploaded_file($_FILES['file_surat']['tmp_name'], $target)) {
            $msg = "Upload file gagal!";
        }
    }

    if (!$msg) {
        $query = "INSERT INTO surat_keluar 
                 (no_agenda, no_surat, tgl_surat, tujuan, instansi, kategori, perihal, isi_surat, file_surat, pembuat_id, status)
                 VALUES
                 ('$no_agenda','$no_surat','$tgl_surat','$tujuan','$instansi','$kategori','$perihal','$isi_surat','$file_name','$pembuat_id','$status')";
        
        if (mysqli_query($conn, $query)) {
            $new_id = mysqli_insert_id($conn);
            logStatus($conn, 'surat_keluar', $new_id, $status, 'Surat dibuat');
            header("Location: surat_keluar.php");
            exit;
        } else {
            $msg = "Gagal menambahkan surat keluar: " . mysqli_error($conn);
        }
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Tambah Surat Keluar</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">No Agenda</label>
      <input type="text" name="no_agenda" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">No Surat</label>
      <input type="text" name="no_surat" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Tanggal Surat</label>
      <input type="date" name="tgl_surat" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Tujuan</label>
      <input type="text" name="tujuan" class="form-control" required>
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
      <input type="text" name="perihal" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Isi Surat</label>
      <textarea name="isi_surat" class="form-control" rows="4"></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">File Surat</label>
      <input type="file" name="file_surat" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
    </div>
    <button type="submit" class="btn btn-success">Simpan</button>
    <a href="surat_keluar.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>

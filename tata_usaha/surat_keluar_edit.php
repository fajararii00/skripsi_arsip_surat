<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

if ($_SESSION['role'] != 'tata_usaha') {
    header("Location: ../login.php");
    exit;
}

// cek id
if (!isset($_GET['id'])) {
    header("Location: surat_keluar.php");
    exit;
}
$id = intval($_GET['id']);

// ambil data surat keluar
$result = mysqli_query($conn, "SELECT * FROM surat_keluar WHERE id=$id");
$surat = mysqli_fetch_assoc($result);
if (!$surat) {
    echo "<div class='alert alert-danger'>Data tidak ditemukan</div>";
    exit;
}

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

    // file upload
    $update_file = "";
    if (!empty($_FILES['file_surat']['name'])) {
        $file_name = time() . "_" . basename($_FILES['file_surat']['name']);
        $target_dir = __DIR__ . "/../assets/uploads/surat_keluar/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $target)) {
            $update_file = ", file_surat='$file_name'";
        } else {
            $msg = "Upload file gagal!";
        }
    }

    if (!$msg) {
        $query = "UPDATE surat_keluar SET 
                    no_agenda='$no_agenda',
                    no_surat='$no_surat',
                    tgl_surat='$tgl_surat',
                    tujuan='$tujuan',
                    instansi='$instansi',
                    kategori='$kategori',
                    perihal='$perihal',
                    isi_surat='$isi_surat'
                    $update_file
                  WHERE id=$id";
        if (mysqli_query($conn, $query)) {
            header("Location: surat_keluar.php");
            exit;
        } else {
            $msg = "Gagal update: " . mysqli_error($conn);
        }
    }
}
?>

<?php include "../includes/header_tata_usaha.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Edit Surat Keluar</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label>No Agenda</label>
      <input type="text" name="no_agenda" value="<?= htmlspecialchars($surat['no_agenda']); ?>" class="form-control">
    </div>
    <div class="mb-3">
      <label>No Surat</label>
      <input type="text" name="no_surat" value="<?= htmlspecialchars($surat['no_surat']); ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Tanggal Surat</label>
      <input type="date" name="tgl_surat" value="<?= $surat['tgl_surat']; ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Tujuan</label>
      <input type="text" name="tujuan" value="<?= htmlspecialchars($surat['tujuan']); ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Instansi</label>
      <input type="text" name="instansi" value="<?= htmlspecialchars($surat['instansi']); ?>" class="form-control">
    </div>
    <div class="mb-3">
      <label>Kategori</label>
      <input type="text" name="kategori" value="<?= htmlspecialchars($surat['kategori']); ?>" class="form-control">
    </div>
    <div class="mb-3">
      <label>Perihal</label>
      <input type="text" name="perihal" value="<?= htmlspecialchars($surat['perihal']); ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Isi Surat</label>
      <textarea name="isi_surat" class="form-control" rows="4"><?= htmlspecialchars($surat['isi_surat']); ?></textarea>
    </div>
    <div class="mb-3">
      <label>File Surat</label>
      <input type="file" name="file_surat" class="form-control">
      <small>File lama: <?= htmlspecialchars($surat['file_surat']); ?></small>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="surat_keluar.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>
<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM surat_masuk WHERE id=$id");
$surat = mysqli_fetch_assoc($result);

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_surat     = mysqli_real_escape_string($conn, $_POST['no_surat']);
    $tgl_surat    = mysqli_real_escape_string($conn, $_POST['tgl_surat']);
    $tgl_diterima = mysqli_real_escape_string($conn, $_POST['tgl_diterima']);
    $pengirim     = mysqli_real_escape_string($conn, $_POST['pengirim']);
    $instansi     = mysqli_real_escape_string($conn, $_POST['instansi']);
    $kategori     = mysqli_real_escape_string($conn, $_POST['kategori']);
    $perihal      = mysqli_real_escape_string($conn, $_POST['perihal']);
    $status       = mysqli_real_escape_string($conn, $_POST['status']);

    // Jika ada file baru
    if (!empty($_FILES['file_surat']['name'])) {
        $file_name = time() . "_" . basename($_FILES['file_surat']['name']);
        $target_dir = __DIR__ . "/../assets/uploads/surat_masuk/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $target)) {
            $update_file = ", file_surat='$file_name'";
        } else {
            $msg = "Gagal upload file!";
        }
    } else {
        $update_file = "";
    }

    if (!$msg) {
        $query = "UPDATE surat_masuk SET 
                    no_surat='$no_surat', 
                    tgl_surat='$tgl_surat',
                    tgl_diterima='$tgl_diterima',
                    pengirim='$pengirim',
                    instansi='$instansi',
                    kategori='$kategori',
                    perihal='$perihal',
                    status='$status'
                    $update_file
                  WHERE id=$id";
        if (mysqli_query($conn, $query)) {
            if ($status != $surat['status']) {
                logStatus($conn, 'surat_masuk', $id, $status);
            }
            header("Location: surat_masuk.php");
            exit;
        } else {
            $msg = "Gagal update: " . mysqli_error($conn);
        }
    }
}
?>
<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Edit Surat Masuk</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label>No Surat</label>
      <input type="text" name="no_surat" value="<?= htmlspecialchars($surat['no_surat']); ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Tanggal Surat</label>
      <input type="date" name="tgl_surat" value="<?= htmlspecialchars($surat['tgl_surat']); ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Tanggal Diterima</label>
      <input type="date" name="tgl_diterima" value="<?= htmlspecialchars($surat['tgl_diterima']); ?>" class="form-control">
    </div>
    <div class="mb-3">
      <label>Pengirim</label>
      <input type="text" name="pengirim" value="<?= htmlspecialchars($surat['pengirim']); ?>" class="form-control" required>
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
      <textarea name="perihal" class="form-control" required><?= htmlspecialchars($surat['perihal']); ?></textarea>
    </div>
    <div class="mb-3">
      <label>Status</label>
      <select name="status" class="form-select">
        <?php foreach (getStatusSteps() as $key => $label): ?>
          <option value="<?= $key; ?>" <?= $surat['status'] == $key ? 'selected' : ''; ?>><?= $label; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>File Surat</label>
      <input type="file" name="file_surat" class="form-control">
      <?php if($surat['file_surat']): ?>
        <small>File lama: <a href="../assets/uploads/surat_masuk/<?= htmlspecialchars($surat['file_surat']); ?>" target="_blank"><?= htmlspecialchars($surat['file_surat']); ?></a></small>
      <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="surat_masuk.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>

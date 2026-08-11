<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM disposisi WHERE id=$id");
$disposisi = mysqli_fetch_assoc($result);

if (!$disposisi) {
    die("Disposisi tidak ditemukan");
}

$msg = "";

// Ambil daftar surat masuk untuk dropdown
$surat = mysqli_query($conn, "SELECT id, no_surat, perihal FROM surat_masuk ORDER BY tgl_surat DESC");
// Ambil daftar user untuk dropdown penerima
$users = mysqli_query($conn, "SELECT id, nama FROM users ORDER BY nama ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $surat_masuk_id = intval($_POST['surat_masuk_id']);
    $penerima_id    = intval($_POST['penerima_id']);
    $instruksi      = mysqli_real_escape_string($conn, $_POST['instruksi']);
    $tgl_disposisi  = mysqli_real_escape_string($conn, $_POST['tgl_disposisi']);
    $status         = mysqli_real_escape_string($conn, $_POST['status']);

    $query = "UPDATE disposisi SET 
                surat_masuk_id='$surat_masuk_id',
                penerima_id='$penerima_id',
                instruksi='$instruksi',
                tgl_disposisi='$tgl_disposisi',
                status='$status'
              WHERE id=$id";

    if (mysqli_query($conn, $query)) {
        if ($status != $disposisi['status']) {
            logStatus($conn, 'disposisi', $id, $status);
        }
        header("Location: disposisi.php");
        exit;
    } else {
        $msg = "Gagal mengupdate disposisi: " . mysqli_error($conn);
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Edit Disposisi</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Pilih Surat Masuk</label>
      <select name="surat_masuk_id" class="form-select" required>
        <?php while($s=mysqli_fetch_assoc($surat)): ?>
          <option value="<?= $s['id']; ?>" <?= $s['id']==$disposisi['surat_masuk_id']?'selected':'' ?>>
            <?= $s['no_surat']; ?> - <?= htmlspecialchars($s['perihal']); ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Penerima Disposisi</label>
      <select name="penerima_id" class="form-select" required>
        <?php while($u=mysqli_fetch_assoc($users)): ?>
          <option value="<?= $u['id']; ?>" <?= $u['id']==$disposisi['penerima_id']?'selected':'' ?>>
            <?= htmlspecialchars($u['nama']); ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Tanggal Disposisi</label>
      <input type="date" name="tgl_disposisi" value="<?= $disposisi['tgl_disposisi']; ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Instruksi</label>
      <textarea name="instruksi" class="form-control" required><?= htmlspecialchars($disposisi['instruksi']); ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select" required>
        <?php foreach (getStatusSteps() as $key => $label): ?>
          <option value="<?= $key; ?>" <?= $disposisi['status'] == $key ? 'selected' : ''; ?>><?= $label; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="disposisi.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>

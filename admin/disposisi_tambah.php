<?php
include "../includes/auth.php";
include "../includes/db.php";

$msg = "";

// Ambil daftar surat masuk untuk dropdown
$surat = mysqli_query($conn, "SELECT id, no_surat, perihal FROM surat_masuk ORDER BY tgl_surat DESC");

// Ambil data pimpinan (hanya satu atau lebih dari satu, kita ambil yang pertama saja)
$pimpinan = mysqli_query($conn, "SELECT id, nama FROM users WHERE role = 'staf' LIMIT 1");
$penerima = mysqli_fetch_assoc($pimpinan);

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $surat_masuk_id = intval($_POST['surat_masuk_id']);
    $pengirim_id    = $_SESSION['user_id']; // otomatis user login sebagai pengirim
    $penerima_id    = intval($_POST['penerima_id']); // hidden input
    $catatan        = mysqli_real_escape_string($conn, $_POST['catatan'] ?? ''); // opsional
    $tgl_disposisi  = mysqli_real_escape_string($conn, $_POST['tgl_disposisi']);
    $status         = mysqli_real_escape_string($conn, $_POST['status']);

    $query = "INSERT INTO disposisi (surat_masuk_id, pengirim_id, penerima_id, instruksi, tgl_disposisi, status)
              VALUES ('$surat_masuk_id','$pengirim_id','$penerima_id','$catatan','$tgl_disposisi','$status')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: disposisi.php");
        exit;
    } else {
        $msg = "Gagal menambah disposisi: " . mysqli_error($conn);
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Tambah Disposisi</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Pilih Surat Masuk</label>
      <select name="surat_masuk_id" class="form-select" required>
        <option value="">-- Pilih Surat --</option>
        <?php while($s=mysqli_fetch_assoc($surat)): ?>
          <option value="<?= $s['id']; ?>"><?= $s['no_surat']; ?> - <?= htmlspecialchars($s['perihal']); ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Penerima Disposisi</label>
      <?php if ($penerima): ?>
        <input type="hidden" name="penerima_id" value="<?= $penerima['id']; ?>">
        <input type="text" class="form-control" value="<?= htmlspecialchars($penerima['nama']); ?>" readonly>
      <?php else: ?>
        <input type="text" class="form-control" value="(Belum ada user role pimpinan)" readonly>
      <?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Tanggal Disposisi</label>
      <input type="date" name="tgl_disposisi" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Catatan <small class="text-muted">(Opsional)</small></label>
      <textarea name="catatan" class="form-control" placeholder="Isi catatan tambahan jika diperlukan..."></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select" required>
        <option value="pending">Pending</option>
        <option value="proses">Proses</option>
        <option value="selesai">Selesai</option>
      </select>
    </div>

    <button type="submit" class="btn btn-success">Simpan</button>
    <a href="disposisi.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>

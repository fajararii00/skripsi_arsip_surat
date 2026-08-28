<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// pastikan hanya tata usaha yang bisa update disposisi
if ($_SESSION['role'] != 'tata_usaha') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);
$msg = "";

// Ambil data disposisi berdasarkan id
$query = "SELECT d.*, sm.no_surat, sm.perihal, u1.nama AS pengirim
          FROM disposisi d
          JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
          LEFT JOIN users u1 ON d.pengirim_id = u1.id
          WHERE d.id = $id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: disposisi.php");
    exit;
}

// Proses update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

    $update = "UPDATE disposisi 
               SET status = '$status', catatan_pimpinan = '$catatan' 
               WHERE id = $id";

    if (mysqli_query($conn, $update)) {
        if ($status != $data['status']) {
            logStatus($conn, 'disposisi', $id, $status, $catatan ?: null);
        }
        header("Location: disposisi.php");
        exit;
    } else {
        $msg = "Gagal update: " . mysqli_error($conn);
    }
}
?>

<?php include "../includes/header_tata_usaha.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Update Status Disposisi</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Nomor Surat</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($data['no_surat']); ?>" disabled>
    </div>

    <div class="mb-3">
      <label class="form-label">Perihal</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($data['perihal']); ?>" disabled>
    </div>

    <div class="mb-3">
      <label class="form-label">Pengirim Disposisi</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($data['pengirim'] ?? '-'); ?>" disabled>
    </div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select" required>
        <?php foreach (getStatusSteps() as $key => $label): ?>
          <option value="<?= $key; ?>" <?= $data['status'] == $key ? 'selected' : ''; ?>><?= $label; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Catatan Tindak Lanjut <small class="text-muted">(Opsional)</small></label>
      <textarea name="catatan" class="form-control" placeholder="Tambahkan catatan tindak lanjut..."><?= htmlspecialchars($data['catatan_pimpinan'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="disposisi.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>
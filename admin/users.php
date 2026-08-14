<?php
include "../includes/auth.php";
include "../includes/db.php";

// Hapus user jika ada request delete
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);

    if ($id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Tidak dapat menghapus akun yang sedang Anda gunakan.";
        header("Location: users.php");
        exit;
    }

    if (mysqli_query($conn, "DELETE FROM users WHERE id=$id")) {
        $_SESSION['success_message'] = "User berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus user: " . mysqli_error($conn);
    }
    header("Location: users.php");
    exit;
}

// Flash message
$flash_success = $_SESSION['success_message'] ?? '';
$flash_error   = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Ambil kata kunci pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

// Query data user
if ($q) {
    $sql = "SELECT * FROM users 
            WHERE nama LIKE '%$q%' 
               OR email LIKE '%$q%' 
               OR role LIKE '%$q%' 
            ORDER BY role, nama";
} else {
    $sql = "SELECT * FROM users ORDER BY role, nama";
}
$users = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen User - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<?php include "../includes/header.php"; ?>

<?php if ($flash_success): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash_success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<?php if ($flash_error): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash_error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <h3 class="fw-bold mb-2">Manajemen User</h3>

    <div class="d-flex flex-wrap gap-2">
      <!-- Form Pencarian -->
      <form class="d-flex mb-3" method="get" action="">
        <div class="input-group" style="max-width: 400px;">
          <input type="text" name="q" value="<?= htmlspecialchars($q ?? ""); ?>" 
                class="form-control form-control-sm" 
                placeholder="Cari data...">

          <button class="btn btn-sm btn-primary" type="submit">
            <i class="fa fa-search"></i>
          </button>

            <a href="export.php?type=users" class="btn btn-sm btn-success ms-2">
              <i class="fa fa-download"></i>
            </a>
        </div>
      </form>


      <!-- Tombol Tambah User -->
      <a href="user_tambah.php" class="btn btn-success btn-sm mb-3">
        <i class="fa fa-plus"></i> Tambah User
      </a>

    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead class="text-white" style="background: linear-gradient(90deg, #4e73df, #1cc88a);">
          <tr>
            <th style="width:5%;">#</th>
            <th style="width:25%;">Nama</th>
            <th style="width:30%;">Email</th>
            <th style="width:15%;">Role</th>
            <th style="width:25%;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($users) > 0): ?>
            <?php $no=1; while($row=mysqli_fetch_assoc($users)): ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($row['nama']); ?></td>
              <td><?= htmlspecialchars($row['email']); ?></td>
              <td>
                <?php if($row['role'] == 'admin'): ?>
                  <span class="badge bg-primary">Admin</span>
                <?php elseif($row['role'] == 'pimpinan'): ?>
                  <span class="badge bg-success">Pimpinan</span>
                <?php elseif($row['role'] == 'tata_usaha'): ?>
                  <span class="badge bg-warning text-dark">Tata Usaha</span>
                <?php else: ?>
                  <span class="badge bg-secondary"><?= htmlspecialchars($row['role']); ?></span>
                <?php endif; ?>
              </td>
              <td>
                <a href="user_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">
                  <i class="fa fa-edit"></i> Edit
                </a>
                <?php if ($row['role'] != 'admin'): ?>
                  <a href="users.php?hapus=<?= $row['id']; ?>" 
                     class="btn btn-sm btn-danger" 
                     onclick="return confirm('Yakin hapus user ini?');">
                    <i class="fa fa-trash"></i> Hapus
                  </a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center text-muted">Tidak ada user ditemukan.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>

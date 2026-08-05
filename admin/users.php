<?php
include "../includes/auth.php";
include "../includes/db.php";

// Hapus user jika ada request delete
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    header("Location: users.php");
    exit;
}

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
                <?php elseif($row['role'] == 'staf'): ?>
                  <span class="badge bg-success">Staf</span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark">User</span>
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

</body>
</html>

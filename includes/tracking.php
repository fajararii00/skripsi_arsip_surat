<?php
// Helper fitur tracking surat (draft -> menunggu verifikasi -> terverifikasi
// -> diproses kasi PAIS -> selesai)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Daftar step status sesuai urutan alur
function getStatusSteps() {
    return [
        'draft'                 => 'Draft',
        'menunggu_verifikasi'   => 'Menunggu Verifikasi',
        'terverifikasi'         => 'Terverifikasi',
        'diproses_kasi_pais'    => 'Diproses Kasi PAIS',
        'selesai'               => 'Selesai',
    ];
}

// Ambil label satu status
function getStatusLabel($status) {
    $steps = getStatusSteps();
    return isset($steps[$status]) ? $steps[$status] : ucfirst(str_replace('_', ' ', $status));
}

// Warna badge / progress per status
function getStatusColor($status) {
    $colors = [
        'draft'                 => 'secondary',
        'menunggu_verifikasi'   => 'warning',
        'terverifikasi'         => 'info',
        'diproses_kasi_pais'    => 'primary',
        'selesai'               => 'success',
    ];
    return isset($colors[$status]) ? $colors[$status] : 'secondary';
}

// Rendering badge status
function statusBadge($status) {
    return '<span class="badge bg-' . getStatusColor($status) . '">' . htmlspecialchars(getStatusLabel($status)) . '</span>';
}

// Catat pergerakan status ke tabel status_history
function logStatus($conn, $ref_type, $ref_id, $status, $keterangan = null) {
    $keterangan = $keterangan !== null ? mysqli_real_escape_string($conn, $keterangan) : null;
    $updated_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'NULL';

    if ($keterangan !== null) {
        $sql = "INSERT INTO status_history (ref_type, ref_id, status, keterangan, updated_by)
                VALUES ('$ref_type', '$ref_id', '$status', '$keterangan', $updated_by)";
    } else {
        $sql = "INSERT INTO status_history (ref_type, ref_id, status, updated_by)
                VALUES ('$ref_type', '$ref_id', '$status', $updated_by)";
    }
    return mysqli_query($conn, $sql);
}

// Ambil riwayat status (untuk timeline), terbaru di bawah
function getStatusHistory($conn, $ref_type, $ref_id) {
    $result = mysqli_query($conn, "
        SELECT h.*, u.nama
        FROM status_history h
        LEFT JOIN users u ON h.updated_by = u.id
        WHERE h.ref_type = '$ref_type' AND h.ref_id = '$ref_id'
        ORDER BY h.created_at ASC, h.id ASC
    ");
    $history = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = $row;
    }
    return $history;
}

// Status selanjutnya dalam alur (null jika sudah selesai)
function getNextStatus($status) {
    $steps = array_keys(getStatusSteps());
    $idx = array_search($status, $steps);
    if ($idx === false || $idx >= count($steps) - 1) {
        return null;
    }
    return $steps[$idx + 1];
}

// Posisi (index 0-4) sebuah status dalam alur
function getStatusIndex($status) {
    $idx = array_search($status, array_keys(getStatusSteps()));
    return $idx === false ? -1 : $idx;
}

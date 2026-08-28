<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$toast_success = $_SESSION['toast_success'] ?? $_SESSION['success_message'] ?? $_SESSION['success'] ?? '';
$toast_error   = $_SESSION['toast_error'] ?? $_SESSION['error_message'] ?? $_SESSION['error'] ?? '';

unset($_SESSION['toast_success'], $_SESSION['success_message'], $_SESSION['success']);
unset($_SESSION['toast_error'], $_SESSION['error_message'], $_SESSION['error']);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    <?php if (!empty($toast_success)): ?>
    Toast.fire({
        icon: 'success',
        title: <?= json_encode($toast_success); ?>
    });
    <?php endif; ?>

    <?php if (!empty($toast_error)): ?>
    Toast.fire({
        icon: 'error',
        title: <?= json_encode($toast_error); ?>
    });
    <?php endif; ?>
});
</script>

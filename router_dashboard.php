<?php
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: /login');
    exit();
}

// Redirect berdasarkan role
if ($_SESSION['role_id'] == 1) {
    // Dosen
    require_once 'view/dosen/dashboard.php';
} else if ($_SESSION['role_id'] == 2) {
    // Mahasiswa
    require_once 'view/mahasiswa/dashboard.php';
} else {
    // Role tidak valid
    session_destroy();
    header('Location: /login');
    exit();
}
?>

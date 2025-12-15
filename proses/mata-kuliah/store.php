<?php
require_once '../../config/db.php';
require_once 'create.php';

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: /project/login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_matkul = trim($_POST['nama_matkul']);
    $kode_matkul = trim($_POST['kode_matkul']);
    $id_dosen = $_SESSION['user_id'];

    // Validasi input
    if (empty($nama_matkul) || empty($kode_matkul)) {
        $_SESSION['error'] = "Semua field harus diisi!";
        header('Location: /project/mata-kuliah');
        exit();
    }

    $check_sql = "SELECT id_matkul FROM mata_kuliah WHERE kode_matkul = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $kode_matkul);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Kode mata kuliah sudah digunakan!";
        header('Location: /project/mata-kuliah');
        exit();
    }

    // Insert mata kuliah baru
    if (createMatkul($nama_matkul, $kode_matkul, $id_dosen)) {
        $_SESSION['success'] = "Mata kuliah berhasil ditambahkan!";
        header('Location: /project/mata-kuliah');
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan mata kuliah!";
        header('Location: /project/mata-kuliah');
        exit();
    }
} else {
    // Jika bukan POST, redirect
    header('Location: /project/mata-kuliah');
    exit();
}
?>
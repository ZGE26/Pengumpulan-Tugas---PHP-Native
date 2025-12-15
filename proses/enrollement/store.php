<?php
session_start();

// Load database connection dan query functions
require_once '../../config/db.php';
require_once 'query.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 2) {
    header('Location: /project/login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_matkul'])) {
        $id_matkul = $_POST['id_matkul'];
        $id_mahasiswa = $_SESSION['user_id'];

        $result = createEnrollment($id_mahasiswa, $id_matkul);

        if ($result) {
            $_SESSION['success'] = "Berhasil mengambil mata kuliah!";
            header('Location: /project/matakuliah');
            exit();
        } else {
            $_SESSION['error'] = "Anda sudah mengambil mata kuliah ini sebelumnya.";
            header('Location: /project/matakuliah');
            exit();
        }
    } else {
        $_SESSION['error'] = "Data tidak lengkap.";
        header('Location: /project/matakuliah');
        exit();
    }
} else {
    $_SESSION['error'] = "Metode permintaan tidak valid.";
    header('Location: /project/matakuliah');
    exit();
}
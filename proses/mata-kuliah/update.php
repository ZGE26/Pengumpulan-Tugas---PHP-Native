<?php
require_once '../../config/db.php';
require_once 'edit.php';

session_start();

// Cek login dan role
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: /project/login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_matkul'], $_POST['nama_matkul'], $_POST['kode_matkul'], $_POST['id_dosen'])) {
        $id_matkul = $_POST['id_matkul'];
        $nama_matkul = $_POST['nama_matkul'];
        $kode_matkul = $_POST['kode_matkul'];
        $id_dosen = $_POST['id_dosen'];

        $result = updateMatkul($id_matkul, $nama_matkul, $kode_matkul, $id_dosen);

        if ($result) {
            $_SESSION['success'] = "Mata kuliah berhasil diupdate.";
            header('Location: /project/mata-kuliah');
            exit();
        } else {
            $_SESSION['error'] = "Gagal mengupdate mata kuliah.";
            header('Location: /project/mata-kuliah/edit?id=' . $id_matkul);
            exit();
        }
    } else {
        $_SESSION['error'] = "Data tidak lengkap.";
        header('Location: /project/mata-kuliah');
        exit();
    }
} else {
    $_SESSION['error'] = "Metode permintaan tidak valid.";
    header('Location: /project/mata-kuliah');
    exit();
}
?>
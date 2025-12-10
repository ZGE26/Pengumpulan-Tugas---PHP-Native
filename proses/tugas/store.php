<?php
require_once '../../config/db.php';
require_once 'query.php';

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: /login');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_matkul'], $_POST['judul'], $_POST['deskripsi'], $_POST['bobot_nilai'], $_POST['deadline'])) {
        $id_matkul = $_POST['id_matkul'];
        $judul = $_POST['judul'];
        $deskripsi = $_POST['deskripsi'];
        $bobot_nilai = $_POST['bobot_nilai'];
        $deadline = $_POST['deadline'];

        $result = createTugas($id_matkul, $judul, $deskripsi, $bobot_nilai, $deadline);

        if ($result) {
            $_SESSION['success'] = "Tugas berhasil dibuat.";
            header('Location: /tugas');
            exit();
        } else {
            $_SESSION['error'] = "Gagal membuat tugas.";
            header('Location: /tugas');
            exit();
        }
    } else {
        $_SESSION['error'] = "Data tidak lengkap.";
        header('Location: /tugas');
        exit();
    }
} else {
    $_SESSION['error'] = "Metode permintaan tidak valid.";
    header('Location: /tugas');
    exit();
}
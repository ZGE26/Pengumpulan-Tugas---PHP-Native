<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: /project/login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /project/tugas');
    exit();
}

$id_tugas = intval($_POST['id_tugas']);
$id_matkul = intval($_POST['id_matkul']);
$judul_tugas = trim($_POST['judul_tugas']);
$deskripsi_tugas = trim($_POST['deskripsi_tugas']);
$bobot_nilai = floatval($_POST['bobot_nilai']);
$deadline = $_POST['deadline'];
$id_dosen = $_SESSION['user_id'];

// Validasi input
if (empty($judul_tugas) || empty($deskripsi_tugas) || empty($deadline)) {
    $_SESSION['error'] = 'Semua field harus diisi!';
    header('Location: /project/tugas/edit?id=' . $id_tugas);
    exit();
}

if ($bobot_nilai < 0 || $bobot_nilai > 100) {
    $_SESSION['error'] = 'Bobot nilai harus antara 0-100!';
    header('Location: /project/tugas/edit?id=' . $id_tugas);
    exit();
}

// Pastikan tugas ini milik dosen yang login
$check_query = "SELECT t.id_tugas 
                FROM tugas t
                INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
                WHERE t.id_tugas = ? AND m.id_dosen = ?";

$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $id_tugas, $id_dosen);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows == 0) {
    $_SESSION['error'] = 'Tugas tidak ditemukan atau bukan milik Anda!';
    header('Location: /project/tugas');
    exit();
}

// Update tugas
$update_query = "UPDATE tugas 
                 SET id_matkul = ?, judul_tugas = ?, deskripsi_tugas = ?, 
                     bobot_nilai = ?, deadline = ?
                 WHERE id_tugas = ?";

$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("issdsi", $id_matkul, $judul_tugas, $deskripsi_tugas, $bobot_nilai, $deadline, $id_tugas);

if ($update_stmt->execute()) {
    $_SESSION['success'] = 'Tugas berhasil diupdate!';
} else {
    $_SESSION['error'] = 'Gagal mengupdate tugas!';
}

header('Location: /project/tugas');
exit();
?>

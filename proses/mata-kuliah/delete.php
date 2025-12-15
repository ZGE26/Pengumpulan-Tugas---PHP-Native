<?php
require_once '../../config/db.php';

session_start();

// Cek login dan role
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: /project/login');
    exit();
}

// Cek parameter id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID Mata Kuliah tidak valid.";
    header('Location: /project/mata-kuliah');
    exit();
}

$id_matkul = intval($_GET['id']);

// Cek apakah mata kuliah memiliki tugas
$check_tugas = "SELECT COUNT(*) as total FROM tugas WHERE id_matkul = ?";
$stmt = $conn->prepare($check_tugas);
$stmt->bind_param("i", $id_matkul);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    $_SESSION['error'] = "Tidak dapat menghapus mata kuliah. Masih ada " . $row['total'] . " tugas yang terkait.";
    header('Location: /project/mata-kuliah');
    exit();
}

// Cek apakah mata kuliah memiliki enrollment
$check_enrollment = "SELECT COUNT(*) as total FROM enrollment WHERE id_matkul = ?";
$stmt = $conn->prepare($check_enrollment);
$stmt->bind_param("i", $id_matkul);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    $_SESSION['error'] = "Tidak dapat menghapus mata kuliah. Masih ada " . $row['total'] . " mahasiswa yang terdaftar.";
    header('Location: /project/mata-kuliah');
    exit();
}

// Hapus mata kuliah
$sql = "DELETE FROM mata_kuliah WHERE id_matkul = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_matkul);

if ($stmt->execute()) {
    $_SESSION['success'] = "Mata kuliah berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus mata kuliah.";
}

header('Location: /project/mata-kuliah');
exit();
?>

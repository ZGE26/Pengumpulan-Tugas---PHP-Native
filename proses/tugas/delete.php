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
    $_SESSION['error'] = "ID Tugas tidak valid.";
    header('Location: /project/tugas');
    exit();
}

$id_tugas = intval($_GET['id']);

// Cek apakah tugas dibuat oleh dosen yang login
$check_sql = "SELECT t.*, mk.id_dosen 
              FROM tugas t
              INNER JOIN mata_kuliah mk ON t.id_matkul = mk.id_matkul
              WHERE t.id_tugas = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Tugas tidak ditemukan.";
    header('Location: /project/tugas');
    exit();
}

$tugas = $result->fetch_assoc();

// Verifikasi bahwa dosen yang login adalah pemilik tugas
if ($tugas['id_dosen'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk menghapus tugas ini.";
    header('Location: /project/tugas');
    exit();
}

// Cek apakah ada pengumpulan
$check_pengumpulan = "SELECT COUNT(*) as total FROM pengumpulan WHERE id_tugas = ?";
$stmt = $conn->prepare($check_pengumpulan);
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    // Hapus file-file pengumpulan terlebih dahulu
    $get_files = "SELECT nama_file FROM pengumpulan WHERE id_tugas = ? AND nama_file IS NOT NULL";
    $stmt = $conn->prepare($get_files);
    $stmt->bind_param("i", $id_tugas);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($file = $result->fetch_assoc()) {
        $file_path = '../../public/uploads/' . $file['nama_file'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Hapus data pengumpulan
    $delete_pengumpulan = "DELETE FROM pengumpulan WHERE id_tugas = ?";
    $stmt = $conn->prepare($delete_pengumpulan);
    $stmt->bind_param("i", $id_tugas);
    $stmt->execute();
}

// Hapus tugas
$sql = "DELETE FROM tugas WHERE id_tugas = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_tugas);

if ($stmt->execute()) {
    $_SESSION['success'] = "Tugas berhasil dihapus beserta semua pengumpulannya.";
} else {
    $_SESSION['error'] = "Gagal menghapus tugas.";
}

header('Location: /project/tugas');
exit();
?>

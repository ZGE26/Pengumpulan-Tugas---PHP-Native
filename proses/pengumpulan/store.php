<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 2) {
    header('Location: /login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /tugas');
    exit();
}

$id_tugas = intval($_POST['id_tugas']);
$id_mahasiswa = $_SESSION['user_id'];
$link_pengumpulan = $_POST['link_pengumpulan'] ?? null;

// Validasi tugas exists dan belum expired
$query = "SELECT deadline FROM tugas WHERE id_tugas = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Tugas tidak ditemukan!';
    header('Location: /tugas');
    exit();
}

$tugas = $result->fetch_assoc();
if (strtotime($tugas['deadline']) < time()) {
    $_SESSION['error'] = 'Deadline tugas sudah lewat!';
    header('Location: /tugas/detail?id=' . $id_tugas);
    exit();
}

// Validasi file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'File harus diupload!';
    header('Location: /tugas/detail?id=' . $id_tugas);
    exit();
}

$file = $_FILES['file'];
$allowed_extensions = ['pdf', 'doc', 'docx', 'zip', 'rar'];
$max_size = 10 * 1024 * 1024; // 10MB

$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    $_SESSION['error'] = 'Format file tidak diizinkan! Gunakan: PDF, DOC, DOCX, ZIP, RAR';
    header('Location: /tugas/detail?id=' . $id_tugas);
    exit();
}

if ($file['size'] > $max_size) {
    $_SESSION['error'] = 'Ukuran file terlalu besar! Maksimal 10MB';
    header('Location: /tugas/detail?id=' . $id_tugas);
    exit();
}

// Generate nama file unik
$new_filename = 'tugas_' . $id_tugas . '_' . $id_mahasiswa . '_' . time() . '.' . $file_extension;
$upload_path = '../../public/uploads/' . $new_filename;

// Upload file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    $_SESSION['error'] = 'Gagal mengupload file!';
    header('Location: /tugas/detail?id=' . $id_tugas);
    exit();
}

// Cek apakah sudah pernah mengumpulkan
$check_query = "SELECT id_pengumpulan FROM pengumpulan WHERE id_tugas = ? AND id_mahasiswa = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $id_tugas, $id_mahasiswa);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update pengumpulan
    $pengumpulan = $check_result->fetch_assoc();

    // Hapus file lama
    $old_file = '../../public/uploads/' . $pengumpulan['nama_file'];
    if (file_exists($old_file)) {
        unlink($old_file);
    }

    $update_query = "UPDATE pengumpulan SET nama_file = ?, link_pengumpulan = ?, tanggal_kumpul = NOW() 
                     WHERE id_pengumpulan = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssi", $new_filename, $link_pengumpulan, $pengumpulan['id_pengumpulan']);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = 'Pengumpulan tugas berhasil diupdate!';
    } else {
        $_SESSION['error'] = 'Gagal mengupdate pengumpulan!';
    }
} else {
    // Insert pengumpulan baru
    $insert_query = "INSERT INTO pengumpulan (id_tugas, id_mahasiswa, nama_file, link_pengumpulan, tanggal_kumpul) 
                     VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iiss", $id_tugas, $id_mahasiswa, $new_filename, $link_pengumpulan);

    if ($insert_stmt->execute()) {
        $_SESSION['success'] = 'Tugas berhasil dikumpulkan!';
    } else {
        $_SESSION['error'] = 'Gagal mengumpulkan tugas!';
    }
}

header('Location: /tugas/detail?id=' . $id_tugas);
exit();
?>
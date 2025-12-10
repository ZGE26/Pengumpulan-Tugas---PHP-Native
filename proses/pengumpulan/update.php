<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 2) {
    $_SESSION['error'] = 'Akses ditolak!';
    header('Location: /login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Metode request tidak valid';
    header('Location: /tugas');
    exit();
}

$id_pengumpulan = intval($_POST['id_pengumpulan'] ?? 0);
$id_tugas = intval($_POST['id_tugas'] ?? 0);
$link_pengumpulan = trim($_POST['link_pengumpulan'] ?? '');
$id_mahasiswa = $_SESSION['user_id'];

// Validasi
if ($id_pengumpulan == 0 || $id_tugas == 0) {
    $_SESSION['error'] = 'Data tidak valid!';
    header('Location: /tugas/detail?id=' . $id_tugas);
    exit();
}

// Cek apakah pengumpulan milik mahasiswa ini
$check_query = "SELECT * FROM pengumpulan WHERE id_pengumpulan = ? AND id_mahasiswa = ?";
$stmt_check = $conn->prepare($check_query);
$stmt_check->bind_param("ii", $id_pengumpulan, $id_mahasiswa);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    $_SESSION['error'] = 'Pengumpulan tidak ditemukan atau bukan milik Anda!';
    header('Location: /tugas/detail?id=' . $id_tugas);
    exit();
}

$pengumpulan = $result_check->fetch_assoc();

// Cek apakah sudah dinilai
if ($pengumpulan['nilai'] !== null) {
    $_SESSION['error'] = 'Pengumpulan sudah dinilai, tidak bisa diedit!';
    header('Location: /tugas/detail?id=' . $id_tugas);
    exit();
}

// Cek apakah ada file baru
$upload_success = true;
$new_file_name = $pengumpulan['nama_file']; // Gunakan file lama sebagai default

if (isset($_FILES['file_tugas']) && $_FILES['file_tugas']['error'] == UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['file_tugas']['tmp_name'];
    $file_name = $_FILES['file_tugas']['name'];
    $file_size = $_FILES['file_tugas']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Validasi tipe file
    $allowed_ext = ['pdf', 'doc', 'docx', 'zip', 'rar'];
    if (!in_array($file_ext, $allowed_ext)) {
        $_SESSION['error'] = 'Tipe file tidak diizinkan! Hanya: PDF, DOC, DOCX, ZIP, RAR';
        header('Location: /tugas/detail?id=' . $id_tugas);
        exit();
    }

    // Validasi ukuran file (max 10MB)
    if ($file_size > 10 * 1024 * 1024) {
        $_SESSION['error'] = 'Ukuran file terlalu besar! Maksimal 10MB';
        header('Location: /tugas/detail?id=' . $id_tugas);
        exit();
    }

    // Generate nama file unik
    $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
    $upload_path = __DIR__ . '/../public/uploads/' . $new_file_name;

    // Upload file baru
    if (move_uploaded_file($file_tmp, $upload_path)) {
        // Hapus file lama jika ada
        if (!empty($pengumpulan['nama_file'])) {
            $old_file_path = __DIR__ . '/../public/uploads/' . $pengumpulan['nama_file'];
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        }
    } else {
        $upload_success = false;
        $_SESSION['error'] = 'Gagal mengupload file baru!';
        header('Location: /tugas/detail?id=' . $id_tugas);
        exit();
    }
}

// Update database
$update_query = "UPDATE pengumpulan 
                 SET nama_file = ?, 
                     link_pengumpulan = ?,
                     tanggal_kumpul = NOW()
                 WHERE id_pengumpulan = ?";

$stmt_update = $conn->prepare($update_query);
$stmt_update->bind_param("ssi", $new_file_name, $link_pengumpulan, $id_pengumpulan);

if ($stmt_update->execute()) {
    $_SESSION['success'] = 'Pengumpulan berhasil diupdate!';
} else {
    $_SESSION['error'] = 'Gagal update pengumpulan: ' . $conn->error;
}

$stmt_update->close();
$conn->close();

header('Location: /tugas/detail?id=' . $id_tugas);
exit();

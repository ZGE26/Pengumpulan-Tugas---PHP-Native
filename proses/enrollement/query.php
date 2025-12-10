<?php

function getAllEnrollByUserId($userId)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT e.*, mk.nama_matkul, mk.kode_matkul, u.nama_lengkap as nama_dosen
        FROM enrollment e
        INNER JOIN mata_kuliah mk ON e.id_matkul = mk.id_matkul
        INNER JOIN user u ON mk.id_dosen = u.user_id
        WHERE e.id_mahasiswa = ?
        ORDER BY e.tanggal_ambil DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function createEnrollment($id_mahasiswa, $id_matkul)
{
    global $conn;

    // Cek apakah mahasiswa sudah mengambil mata kuliah ini
    $check_query = "SELECT id_enrollment FROM enrollment 
                    WHERE id_mahasiswa = ? AND id_matkul = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $id_mahasiswa, $id_matkul);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    // Jika sudah ada, return false
    if ($result->num_rows > 0) {
        return false;
    }

    // Jika belum ada, insert data baru
    $query = "INSERT INTO enrollment (id_mahasiswa, id_matkul, tanggal_ambil) 
              VALUES (?, ?, NOW())";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_mahasiswa, $id_matkul);

    return $stmt->execute();
}

function getEnrollmentByMatkulAndMahasiswa($id_mahasiswa, $id_matkul)
{
    global $conn;

    $query = "SELECT * FROM enrollment 
              WHERE id_mahasiswa = ? AND id_matkul = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_mahasiswa, $id_matkul);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return true;
    }

    return false;
}
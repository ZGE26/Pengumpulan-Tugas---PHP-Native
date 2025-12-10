<?php
function createTugas($id_matkul, $judul_tugas, $deskripsi_tugas, $bobot_nilai, $deadline)
{
    global $conn;

    $query = "INSERT INTO tugas (id_matkul, judul_tugas, deskripsi_tugas, bobot_nilai, deadline) 
              VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("issis", $id_matkul, $judul_tugas, $deskripsi_tugas, $bobot_nilai, $deadline);

    return $stmt->execute();
}

function getAllTugas()
{
    global $conn;

    $query = "SELECT t.*, m.nama_matkul, m.kode_matkul, u.nama_lengkap as nama_dosen
              FROM tugas t
              INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
              INNER JOIN user u ON m.id_dosen = u.user_id
              ORDER BY t.deadline DESC";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    return [];
}

function getTugasByEnrollMahasiswa($id_mahasiswa)
{
    global $conn;

    $query = "SELECT t.*, m.nama_matkul, m.kode_matkul, u.nama_lengkap as nama_dosen
              FROM tugas t
              INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
              INNER JOIN enrollment e ON m.id_matkul = e.id_matkul
              INNER JOIN user u ON m.id_dosen = u.user_id
              WHERE e.id_mahasiswa = ?
              ORDER BY t.deadline DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_mahasiswa);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    return [];

}

function getTugasByDosen($id_dosen)
{
    global $conn;

    $query = "SELECT t.*, m.nama_matkul, m.kode_matkul
              FROM tugas t
              INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
              WHERE m.id_dosen = ?
              ORDER BY t.deadline DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_dosen);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    return [];
}
function getTugasByMatkul($id_matkul, $id_mahasiswa = null)
{
    global $conn;

    $query = "SELECT t.*, m.nama_matkul, m.kode_matkul, u.nama_lengkap as nama_dosen
              FROM tugas t
              INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
              INNER JOIN user u ON m.id_dosen = u.user_id
              WHERE t.id_matkul = ?
              ORDER BY t.deadline DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("getTugasByMatkul Prepare failed: " . $conn->error);
        return [];
    }

    $stmt->bind_param("i", $id_matkul);

    if (!$stmt->execute()) {
        error_log("getTugasByMatkul Execute failed: " . $stmt->error);
        return [];
    }

    $result = $stmt->get_result();
    error_log("getTugasByMatkul found " . $result->num_rows . " rows for id_matkul=" . $id_matkul);

    if ($result->num_rows > 0) {
        $data = $result->fetch_all(MYSQLI_ASSOC);

        if ($id_mahasiswa) {
            foreach ($data as &$tugas) {
                $pengumpulan_query = "SELECT id_pengumpulan, tanggal_kumpul, nilai, catatan_dosen 
                                      FROM pengumpulan 
                                      WHERE id_tugas = ? AND id_mahasiswa = ?";
                $p_stmt = $conn->prepare($pengumpulan_query);
                if ($p_stmt) {
                    $p_stmt->bind_param("ii", $tugas['id_tugas'], $id_mahasiswa);
                    $p_stmt->execute();
                    $p_result = $p_stmt->get_result();
                    if ($p_result->num_rows > 0) {
                        $pengumpulan = $p_result->fetch_assoc();
                        $tugas['id_pengumpulan'] = $pengumpulan['id_pengumpulan'];
                        $tugas['tanggal_kumpul'] = $pengumpulan['tanggal_kumpul'];
                        $tugas['nilai'] = $pengumpulan['nilai'];
                        $tugas['catatan_dosen'] = $pengumpulan['catatan_dosen'];
                    } else {
                        $tugas['id_pengumpulan'] = null;
                        $tugas['tanggal_kumpul'] = null;
                        $tugas['nilai'] = null;
                        $tugas['catatan_dosen'] = null;
                    }
                }
            }
        }

        return $data;
    }

    return [];
}
?>
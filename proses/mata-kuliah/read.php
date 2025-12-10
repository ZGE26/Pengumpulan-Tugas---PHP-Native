<?php
// File ini berisi fungsi-fungsi untuk mengambil data mata kuliah

function getAllMatkul()
{
    global $conn;

    $sql = "SELECT mk.*, u.nama_lengkap as nama_dosen 
            FROM mata_kuliah mk
            INNER JOIN user u ON mk.id_dosen = u.user_id
            ORDER BY mk.nama_matkul ASC";

    $result = $conn->query($sql);
    $matkuls = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $matkuls[] = $row;
        }
    }

    return $matkuls;
}

function getMatkulByDosen($id_dosen)
{
    global $conn;

    $sql = "SELECT * FROM mata_kuliah WHERE id_dosen = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_dosen);
    $stmt->execute();
    $result = $stmt->get_result();

    $matkuls = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $matkuls[] = $row;
        }
    }

    return $matkuls;
}

function getMatkulById($id_matkul)
{
    global $conn;

    $sql = "SELECT mk.*, u.nama_lengkap as nama_dosen 
            FROM mata_kuliah mk
            INNER JOIN user u ON mk.id_dosen = u.user_id
            WHERE mk.id_matkul = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_matkul);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        return $result->fetch_assoc();
    }

    return null;
}

function getNameMatkulById($id_matkul)
{
    global $conn;

    $sql = "SELECT nama_matkul FROM mata_kuliah WHERE id_matkul = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_matkul);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row['nama_matkul'];
    }

    return null;
}
?>
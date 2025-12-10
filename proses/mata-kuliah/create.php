<?php
function createMatkul($nama_matkul, $kode_matkul, $id_dosen)
{
    global $conn;

    $sql = "INSERT INTO mata_kuliah (nama_matkul, kode_matkul, id_dosen) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nama_matkul, $kode_matkul, $id_dosen);

    return $stmt->execute();
}
?>
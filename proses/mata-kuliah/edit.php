<?php
function updateMatkul($id_matkul, $nama_matkul, $kode_matkul, $id_dosen)
{
    global $conn;

    $sql = "UPDATE mata_kuliah SET nama_matkul = ?, kode_matkul = ?, id_dosen = ? WHERE id_matkul = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $nama_matkul, $kode_matkul, $id_dosen, $id_matkul);

    return $stmt->execute();
}
?>
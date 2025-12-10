<?php
function getAllDosen()
{
    global $conn;

    $sql = "SELECT user_id, user_name, nama_lengkap, nomor_induk
            FROM user 
            WHERE role_id = 1
            ORDER BY nama_lengkap ASC";
    $result = $conn->query($sql);

    $dosen_list = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dosen_list[] = $row;
        }
    }

    return $dosen_list;
}
?>
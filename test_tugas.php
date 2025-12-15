<?php
// File test untuk debugging tugas
session_start();
require_once 'config/db.php';
require_once 'proses/tugas.php';

// Cek login
if (!isset($_SESSION['logged_in'])) {
    die("Silakan login terlebih dahulu");
}

echo "<h1>Test Debugging Tugas</h1>";
echo "<hr>";

// Test 1: Cek session
echo "<h2>1. Session Info</h2>";
echo "User ID: " . $_SESSION['user_id'] . "<br>";
echo "Role ID: " . $_SESSION['role_id'] . "<br>";
echo "Selected Matkul: " . (isset($_SESSION['selected_matkul']) ? $_SESSION['selected_matkul'] : 'TIDAK ADA') . "<br>";
echo "<hr>";

// Test 2: Cek semua tugas di database
echo "<h2>2. Semua Tugas di Database</h2>";
$query = "SELECT t.*, m.nama_matkul FROM tugas t 
          INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul";
$result = $conn->query($query);
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Judul</th><th>Mata Kuliah</th><th>ID Matkul</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id_tugas'] . "</td>";
        echo "<td>" . $row['judul_tugas'] . "</td>";
        echo "<td>" . $row['nama_matkul'] . "</td>";
        echo "<td>" . $row['id_matkul'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>TIDAK ADA TUGAS DI DATABASE!</p>";
}
echo "<hr>";

// Test 3: Cek enrollment mahasiswa
if ($_SESSION['role_id'] == 2) {
    echo "<h2>3. Enrollment Mahasiswa</h2>";
    require_once 'proses/enrollement/query.php';
    $enrollments = getAllEnrollByUserId($_SESSION['user_id']);
    if (!empty($enrollments)) {
        echo "<table border='1'>";
        echo "<tr><th>ID Enrollment</th><th>ID Matkul</th><th>Nama Matkul</th></tr>";
        foreach ($enrollments as $enroll) {
            echo "<tr>";
            echo "<td>" . $enroll['id_enrollment'] . "</td>";
            echo "<td>" . $enroll['id_matkul'] . "</td>";
            echo "<td>" . $enroll['nama_matkul'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Mahasiswa belum enroll di mata kuliah manapun!</p>";
    }
    echo "<hr>";
}

// Test 4: Cek fungsi getTugasByMatkul
if (isset($_SESSION['selected_matkul'])) {
    echo "<h2>4. Test getTugasByMatkul</h2>";
    $tugas_list = getTugasByMatkul($_SESSION['selected_matkul'], $_SESSION['user_id']);
    echo "Jumlah tugas ditemukan: " . count($tugas_list) . "<br>";
    if (!empty($tugas_list)) {
        echo "<pre>";
        print_r($tugas_list);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>Fungsi getTugasByMatkul mengembalikan array kosong!</p>";
        
        // Test query manual
        echo "<h3>Test Query Manual:</h3>";
        $manual_query = "SELECT t.*, m.nama_matkul, m.kode_matkul, u.nama_lengkap as nama_dosen
                  FROM tugas t
                  INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
                  INNER JOIN user u ON m.id_dosen = u.user_id
                  WHERE t.id_matkul = " . intval($_SESSION['selected_matkul']);
        echo "Query: <code>" . $manual_query . "</code><br>";
        $manual_result = $conn->query($manual_query);
        echo "Hasil: " . $manual_result->num_rows . " rows<br>";
        if ($manual_result->num_rows > 0) {
            echo "<pre>";
            while ($row = $manual_result->fetch_assoc()) {
                print_r($row);
            }
            echo "</pre>";
        }
    }
}

echo "<hr>";
echo "<a href='/project/matakuliah'>← Kembali ke Matakuliah</a> ";
echo "<a href='/project/tugas'>Lihat Halaman Tugas</a>";
?>

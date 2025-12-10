<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: /login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /tugas');
    exit();
}

$id_pengumpulan = intval($_POST['id_pengumpulan']);
$id_tugas = intval($_POST['id_tugas']);
$nilai = floatval($_POST['nilai']);
$catatan_dosen = $_POST['catatan_dosen'] ?? null;
$id_dosen = $_SESSION['user_id'];

if ($nilai < 0 || $nilai > 100) {
    $_SESSION['error'] = 'Nilai harus antara 0-100!';
    header('Location: /tugas/nilai?id=' . $id_pengumpulan);
    exit();
}

$query = "SELECT p.id_pengumpulan, p.tanggal_kumpul, t.deadline
          FROM pengumpulan p
          INNER JOIN tugas t ON p.id_tugas = t.id_tugas
          INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
          WHERE p.id_pengumpulan = ? AND m.id_dosen = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_pengumpulan, $id_dosen);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Pengumpulan tidak ditemukan!';
    header('Location: /tugas');
    exit();
}

$data = $result->fetch_assoc();

$deadline = strtotime($data['deadline']);
$tanggal_kumpul = strtotime($data['tanggal_kumpul']);

$nilai_final = $nilai;
$keterangan_tambahan = '';

if ($tanggal_kumpul > $deadline) {
    // Terlambat - kurangi 10 poin
    $nilai_final = max(0, $nilai - 10); // Tidak boleh kurang dari 0
    $keterangan_tambahan = "\n\n[OTOMATIS] Pengumpulan terlambat. Nilai dikurangi 10 poin (dari " . $nilai . " menjadi " . $nilai_final . ")";

    if ($catatan_dosen) {
        $catatan_dosen .= $keterangan_tambahan;
    } else {
        $catatan_dosen = "Nilai: " . $nilai . $keterangan_tambahan;
    }
}

$update_query = "UPDATE pengumpulan SET nilai = ?, catatan_dosen = ? WHERE id_pengumpulan = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("dsi", $nilai_final, $catatan_dosen, $id_pengumpulan);

if ($update_stmt->execute()) {
    if ($tanggal_kumpul > $deadline) {
        $_SESSION['success'] = 'Nilai berhasil disimpan! (Nilai dikurangi 10 poin karena terlambat: ' . $nilai . ' → ' . $nilai_final . ')';
    } else {
        $_SESSION['success'] = 'Nilai berhasil disimpan!';
    }
} else {
    $_SESSION['error'] = 'Gagal menyimpan nilai!';
}

header('Location: /tugas/pengumpulan?id=' . $id_tugas);
exit();
?>
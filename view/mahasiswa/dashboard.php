<?php
// File dipanggil melalui routing, session dan db connection sudah diload di index.php
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 2) {
    header('Location: /login');
    exit();
}

$id_mahasiswa = $_SESSION['user_id'];

// Cek apakah mahasiswa sudah enroll mata kuliah
$check_enrollment = "SELECT COUNT(*) as total FROM enrollment WHERE id_mahasiswa = ?";
$stmt_check = $conn->prepare($check_enrollment);
$stmt_check->bind_param("i", $id_mahasiswa);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$enrollment_count = $result_check->fetch_assoc()['total'];

// Jika belum enroll sama sekali, redirect ke halaman matakuliah
if ($enrollment_count == 0) {
    $_SESSION['info'] = 'Silakan ambil mata kuliah terlebih dahulu untuk memulai.';
    header('Location: /matakuliah');
    exit();
}

// Query untuk mendapatkan semua tugas yang belum dikumpulkan dari mata kuliah yang dienroll
$query = "SELECT t.*, m.nama_matkul, m.kode_matkul, u.nama_lengkap as nama_dosen,
          e.id_enrollment
          FROM tugas t
          INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
          INNER JOIN enrollment e ON m.id_matkul = e.id_matkul
          INNER JOIN user u ON m.id_dosen = u.user_id
          LEFT JOIN pengumpulan p ON t.id_tugas = p.id_tugas AND p.id_mahasiswa = ?
          WHERE e.id_mahasiswa = ? AND p.id_pengumpulan IS NULL
          ORDER BY t.deadline ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_mahasiswa, $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();
$tugas_belum_dikumpulkan = $result->fetch_all(MYSQLI_ASSOC);

// Hitung statistik
$total_belum_dikumpulkan = count($tugas_belum_dikumpulkan);

// Hitung tugas yang sudah expired
$tugas_expired = 0;
$tugas_mendesak = 0; // deadline dalam 24 jam
$now = time();

foreach ($tugas_belum_dikumpulkan as $tugas) {
    $deadline = strtotime($tugas['deadline']);
    if ($deadline < $now) {
        $tugas_expired++;
    } elseif ($deadline < ($now + 86400)) { // 86400 = 24 jam dalam detik
        $tugas_mendesak++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Sistem Tugas</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>

<body>
    <div class="headers">
        <div class="content-header">
            <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</p>
            <h1>Dashboard Mahasiswa</h1>
        </div>
        <div class="logout-button">
            <a href="/logout">Logout</a>
        </div>
    </div>

    <nav>
        <a href="/dashboard">Dashboard</a>
        <a href="/matakuliah">Mata Kuliah</a>
    </nav>
    
    <div class="dashboard-container">
        <h2>Ringkasan Tugas</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <!-- <div class="stat-icon">📋</div> -->
                <div class="stat-info">
                    <div class="stat-number"><?= $total_belum_dikumpulkan ?></div>
                    <div class="stat-label">Total Belum Dikumpulkan</div>
                </div>
            </div>
            
            <div class="stat-card urgent">
                <!-- <div class="stat-icon">⚡</div> -->
                <div class="stat-info">
                    <div class="stat-number"><?= $tugas_mendesak ?></div>
                    <div class="stat-label">Tugas Mendesak (< 24 jam)</div>
                </div>
            </div>
            
            <div class="stat-card expired">
                <!-- <div class="stat-icon">⚠️</div> -->
                <div class="stat-info">
                    <div class="stat-number"><?= $tugas_expired ?></div>
                    <div class="stat-label">Tugas Terlambat</div>
                </div>
            </div>
        </div>

        <h2 style="margin-top: 40px;">Daftar Tugas yang Belum Dikumpulkan</h2>

        <?php if ($total_belum_dikumpulkan > 0): ?>
            <table border="1" cellpadding="10" width="100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Tugas</th>
                        <th>Mata Kuliah</th>
                        <th>Dosen</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($tugas_belum_dikumpulkan as $tugas):
                        $deadline = strtotime($tugas['deadline']);
                        $is_expired = $deadline < $now;
                        $is_urgent = !$is_expired && $deadline < ($now + 86400);
                        $time_left = $deadline - $now;
                        ?>
                        <tr class="<?= $is_expired ? 'row-expired' : ($is_urgent ? 'row-urgent' : '') ?>">
                            <td><?= $no++ ?></td>
                            <td>
                                <div class="task-title"><?= htmlspecialchars($tugas['judul_tugas']) ?></div>
                                <div class="task-desc"><?= htmlspecialchars(substr($tugas['deskripsi_tugas'] ?? '', 0, 80)) ?><?= strlen($tugas['deskripsi_tugas'] ?? '') > 80 ? '...' : '' ?></div>
                            </td>
                            <td>
                                <div class="matkul-name"><?= htmlspecialchars($tugas['nama_matkul']) ?></div>
                                <div class="matkul-code"><?= htmlspecialchars($tugas['kode_matkul']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($tugas['nama_dosen']) ?></td>
                            <td>
                                <div class="deadline-date"><?= date('d M Y, H:i', $deadline) ?></div>
                                <?php if ($is_expired): ?>
                                    <span class="badge badge-danger">TERLAMBAT</span>
                                <?php elseif ($is_urgent): ?>
                                    <span class="badge badge-warning">MENDESAK - <?= floor($time_left / 3600) ?> jam lagi</span>
                                <?php else: ?>
                                    <span class="badge badge-info"><?= floor($time_left / 86400) ?> hari lagi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($is_expired): ?>
                                    <span class="status-badge status-expired">Expired</span>
                                <?php elseif ($is_urgent): ?>
                                    <span class="status-badge status-urgent">Mendesak</span>
                                <?php else: ?>
                                    <span class="status-badge status-active">Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/tugas/detail?id=<?= $tugas['id_tugas'] ?>" class="btn-action">
                                    <?= $is_expired ? 'Lihat Detail' : 'Kumpulkan' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">🎉</div>
                <h3>Selamat!</h3>
                <p>Anda tidak memiliki tugas yang belum dikumpulkan.</p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
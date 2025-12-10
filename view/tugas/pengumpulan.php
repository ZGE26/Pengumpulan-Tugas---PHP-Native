<?php
require_once 'proses/tugas/query.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: /login');
    exit();
}

// Ambil ID tugas dari URL
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID Tugas tidak valid!';
    header('Location: /tugas');
    exit();
}

$id_tugas = intval($_GET['id']);
$id_dosen = $_SESSION['user_id'];

$query = "SELECT t.*, m.nama_matkul, m.kode_matkul
          FROM tugas t
          INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
          WHERE t.id_tugas = ? AND m.id_dosen = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_tugas, $id_dosen);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Tugas tidak ditemukan!';
    header('Location: /tugas');
    exit();
}

$tugas = $result->fetch_assoc();

// Ambil daftar pengumpulan mahasiswa
$query_pengumpulan = "SELECT p.*, u.nama_lengkap, u.nomor_induk
                      FROM pengumpulan p
                      INNER JOIN user u ON p.id_mahasiswa = u.user_id
                      WHERE p.id_tugas = ?
                      ORDER BY p.tanggal_kumpul DESC";

$stmt_p = $conn->prepare($query_pengumpulan);
$stmt_p->bind_param("i", $id_tugas);
$stmt_p->execute();
$result_p = $stmt_p->get_result();
$pengumpulan_list = $result_p->fetch_all(MYSQLI_ASSOC);

// Hitung statistik
$total_mahasiswa = $conn->query("SELECT COUNT(*) as total FROM enrollment WHERE id_matkul = " . $tugas['id_matkul'])->fetch_assoc()['total'];
$total_pengumpulan = count($pengumpulan_list);
$belum_mengumpulkan = $total_mahasiswa - $total_pengumpulan;

$deadline = strtotime($tugas['deadline']);
$now = time();
$is_expired = $deadline < $now;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumpulan Tugas - Sistem Tugas</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>

<body>
    <div class="headers">
        <div class="content-header">
            <p>Dosen - Pengumpulan Tugas</p>
            <h1>📋 <?= htmlspecialchars($tugas['judul_tugas']) ?></h1>
        </div>
        <div class="logout-button">
            <a href="/logout">Logout</a>
        </div>
    </div>

    <nav>
        <a href="/dashboard">Dashboard</a>
        <a href="/mata-kuliah">Mata Kuliah</a>
        <a href="/tugas">Tugas</a>
        <a href="/tugas">Kembali</a>
    </nav>
    
    <div class="dashboard-container">
    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    ?>

    <!-- Tugas Info Card -->
    <div class="tugas-info-card">
        <div class="tugas-info-header">
            <div>
                <h2><?= htmlspecialchars($tugas['judul_tugas']) ?></h2>
                <p class="tugas-info-subtitle" style="color: white;">
                    <?= htmlspecialchars($tugas['nama_matkul']) ?> 
                    <span class="kode-badge"><?= htmlspecialchars($tugas['kode_matkul']) ?></span>
                </p>
            </div>
        </div>
        <div class="tugas-info-body">
            <div class="info-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <span><strong>Bobot:</strong> <?= htmlspecialchars($tugas['bobot_nilai']) ?>%</span>
            </div>
            <div class="info-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>
                    <strong>Deadline:</strong> <?= date('d F Y, H:i', strtotime($tugas['deadline'])) ?>
                    <?php if ($is_expired): ?>
                        <span class="status-badge status-expired" style="margin-left: 8px;">EXPIRED</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="stats-grid-pengumpulan">
        <div class="stat-card-pengumpulan">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Mahasiswa</p>
                <h3 class="stat-value"><?= $total_mahasiswa ?></h3>
            </div>
        </div>
        
        <div class="stat-card-pengumpulan">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Sudah Mengumpulkan</p>
                <h3 class="stat-value"><?= $total_pengumpulan ?></h3>
            </div>
        </div>
        
        <div class="stat-card-pengumpulan">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Belum Mengumpulkan</p>
                <h3 class="stat-value"><?= $belum_mengumpulkan ?></h3>
            </div>
        </div>
    </div>

    <!-- Daftar Pengumpulan -->
    <div class="page-header">
        <h2>Daftar Pengumpulan Mahasiswa</h2>
    </div>

    <?php if (count($pengumpulan_list) > 0): ?>
        <div class="pengumpulan-table-card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Tanggal Kumpul</th>
                        <th>File</th>
                        <th>Link</th>
                        <th>Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($pengumpulan_list as $p):
                        // Cek apakah terlambat
                        $terlambat = strtotime($p['tanggal_kumpul']) > $deadline;
                        ?>
                        <tr <?= $terlambat ? 'class="row-late"' : '' ?>>
                            <td><?= $no++ ?></td>
                            <td><span class="nim-badge"><?= htmlspecialchars($p['nomor_induk']) ?></span></td>
                            <td><strong><?= htmlspecialchars($p['nama_lengkap']) ?></strong></td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($p['tanggal_kumpul'])) ?>
                                <?php if ($terlambat): ?>
                                    <br><span class="badge-late">TERLAMBAT (-10)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/public/uploads/<?= htmlspecialchars($p['nama_file']) ?>" target="_blank" class="file-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    Download
                                </a>
                            </td>
                            <td>
                                <?php if ($p['link_pengumpulan']): ?>
                                    <a href="<?= htmlspecialchars($p['link_pengumpulan']) ?>" target="_blank" class="file-link">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                        </svg>
                                        Link
                                    </a>
                                <?php else: ?>
                                    <span style="color: #9ca3af;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['nilai']): ?>
                                    <span class="nilai-badge"><?= htmlspecialchars($p['nilai']) ?></span>
                                <?php else: ?>
                                    <span class="nilai-badge nilai-empty">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/tugas/nilai?id=<?= $p['id_pengumpulan'] ?>" class="btn-grade">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    Beri Nilai
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div style="font-size: 64px; margin-bottom: 16px;">📋</div>
            <h3>Belum Ada Pengumpulan</h3>
            <p>Belum ada mahasiswa yang mengumpulkan tugas ini.</p>
        </div>
    <?php endif; ?>
    </div>
</body>

</html>
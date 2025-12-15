<?php
// Dipanggil melalui routing, session dan db sudah diload

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: /login');
    exit();
}

// Ambil ID pengumpulan dari URL
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID Pengumpulan tidak valid!';
    header('Location: /tugas');
    exit();
}

$id_pengumpulan = intval($_GET['id']);
$id_dosen = $_SESSION['user_id'];

// Ambil detail pengumpulan dan pastikan tugas milik dosen ini
$query = "SELECT p.*, u.nama_lengkap, u.nomor_induk, t.judul_tugas, t.bobot_nilai, t.deadline, m.nama_matkul
          FROM pengumpulan p
          INNER JOIN user u ON p.id_mahasiswa = u.user_id
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

$pengumpulan = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Nilai - Sistem Tugas</title>
    <link rel="stylesheet" href="/project/assets/css/dashboard.css">
</head>
<body>
    <div class="headers">
        <div class="content-header">
            <p>Dosen - Penilaian Tugas</p>
            <h1>
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Beri Nilai
            </h1>
        </div>
        <div class="logout-button">
            <a href="/project/logout">Logout</a>
        </div>
    </div>

    <nav>
        <a href="/project/dashboard">Dashboard</a>
        <a href="/project/mata-kuliah">Mata Kuliah</a>
        <a href="/project/tugas">Tugas</a>
        <a href="/project/tugas/pengumpulan?id=<?= $pengumpulan['id_tugas'] ?>">Kembali</a>
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
    
    // Cek apakah terlambat
    $deadline_time = strtotime($pengumpulan['deadline']);
    $kumpul_time = strtotime($pengumpulan['tanggal_kumpul']);
    $terlambat = $kumpul_time > $deadline_time;
    
    if ($terlambat):
    ?>
        <div class="alert alert-warning">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div>
                <h3>PERHATIAN: Pengumpulan Terlambat</h3>
                <p style="margin: 8px 0;">
                    <strong>Deadline:</strong> <?= date('d F Y, H:i', $deadline_time) ?><br>
                    <strong>Dikumpulkan:</strong> <?= date('d F Y, H:i', $kumpul_time) ?>
                </p>
                <p style="margin: 8px 0 0 0;">
                    <strong>Nilai akan otomatis dikurangi 10 poin saat disimpan.</strong>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Student Submission Card -->
    <div class="submission-detail-card">
        <div class="submission-detail-header">
            <div class="header-left">
                <h2><?= htmlspecialchars($pengumpulan['judul_tugas']) ?></h2>
                <p class="submission-meta" style="color: white;">
                    <?= htmlspecialchars($pengumpulan['nama_matkul']) ?> • Bobot: <?= htmlspecialchars($pengumpulan['bobot_nilai']) ?>%
                </p>
            </div>
            <?php if ($pengumpulan['nilai']): ?>
                <div class="current-grade-badge">
                    <span class="grade-label">Nilai Saat Ini</span>
                    <span class="grade-value"><?= htmlspecialchars($pengumpulan['nilai']) ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="submission-detail-body">
            <div class="student-info-section">
                <h3>Informasi Mahasiswa</h3>
                <div class="student-info-grid">
                    <div class="info-item-detail">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <div>
                            <span class="info-label-detail">Nama Mahasiswa</span>
                            <span class="info-value-detail"><?= htmlspecialchars($pengumpulan['nama_lengkap']) ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item-detail">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <div>
                            <span class="info-label-detail">NIM</span>
                            <span class="info-value-detail"><?= htmlspecialchars($pengumpulan['nomor_induk']) ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item-detail">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <div>
                            <span class="info-label-detail">Tanggal Pengumpulan</span>
                            <span class="info-value-detail">
                                <?= date('d F Y, H:i', strtotime($pengumpulan['tanggal_kumpul'])) ?>
                                <?php if ($terlambat): ?>
                                    <span class="badge-late" style="margin-left: 8px;">TERLAMBAT</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="submission-files-section">
                <h3>File Pengumpulan</h3>
                <div class="files-list">
                    <div class="file-item-detail">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                        <div class="file-info">
                            <a href="/project/public/uploads/<?= htmlspecialchars($pengumpulan['nama_file']) ?>" target="_blank" class="file-link">
                                <?= htmlspecialchars($pengumpulan['nama_file']) ?>
                            </a>
                        </div>
                        <a href="/project/public/uploads/<?= htmlspecialchars($pengumpulan['nama_file']) ?>" target="_blank" class="btn-download">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Download
                        </a>
                    </div>
                    
                    <?php if ($pengumpulan['link_pengumpulan']): ?>
                        <div class="file-item-detail">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                            <div class="file-info">
                                <a href="<?= htmlspecialchars($pengumpulan['link_pengumpulan']) ?>" target="_blank" class="file-link">
                                    <?= htmlspecialchars($pengumpulan['link_pengumpulan']) ?>
                                </a>
                            </div>
                            <a href="<?= htmlspecialchars($pengumpulan['link_pengumpulan']) ?>" target="_blank" class="btn-download">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                                Buka Link
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Grading Form Card -->
    <div class="grading-form-card">
        <h2>Form Penilaian</h2>
        <form action="/project/proses/pengumpulan/nilai.php" method="POST">
            <input type="hidden" name="id_pengumpulan" value="<?= $id_pengumpulan ?>">
            <input type="hidden" name="id_tugas" value="<?= $pengumpulan['id_tugas'] ?>">
            
            <div class="form-group">
                <label for="nilai">Nilai * (0-100)</label>
                <input type="number" name="nilai" id="nilai" min="0" max="100" 
                       value="<?= htmlspecialchars($pengumpulan['nilai'] ?? '') ?>" required
                       placeholder="Masukkan nilai 0-100">
                <small style="color: #6b7280; font-size: 13px; margin-top: 6px; display: block;">
                    <?php if ($terlambat): ?>
                        ⚠️ Nilai akan otomatis dikurangi 10 poin karena pengumpulan terlambat
                    <?php else: ?>
                        Masukkan nilai berdasarkan kualitas pengerjaan tugas
                    <?php endif; ?>
                </small>
            </div>
            
            <div class="form-group">
                <label for="catatan_dosen">Catatan untuk Mahasiswa</label>
                <textarea name="catatan_dosen" id="catatan_dosen" rows="6"
                          placeholder="Berikan feedback atau catatan untuk mahasiswa (opsional)"><?= htmlspecialchars($pengumpulan['catatan_dosen'] ?? '') ?></textarea>
                <small style="color: #6b7280; font-size: 13px; margin-top: 6px; display: block;">
                    Catatan ini akan dilihat oleh mahasiswa
                </small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Simpan Nilai
                </button>
                <a href="/project/tugas/pengumpulan?id=<?= $pengumpulan['id_tugas'] ?>" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>

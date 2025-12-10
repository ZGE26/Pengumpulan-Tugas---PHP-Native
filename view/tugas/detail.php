<?php
// Dipanggil melalui routing, session dan db sudah diload
require_once 'proses/tugas/query.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 2) {
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
$id_mahasiswa = $_SESSION['user_id'];

// Ambil detail tugas
$query = "SELECT t.*, m.nama_matkul, m.kode_matkul, u.nama_lengkap as nama_dosen
          FROM tugas t
          INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
          INNER JOIN user u ON m.id_dosen = u.user_id
          WHERE t.id_tugas = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Tugas tidak ditemukan!';
    header('Location: /tugas');
    exit();
}

$tugas = $result->fetch_assoc();

// Cek apakah mahasiswa sudah mengumpulkan
$query_pengumpulan = "SELECT * FROM pengumpulan WHERE id_tugas = ? AND id_mahasiswa = ?";
$stmt_p = $conn->prepare($query_pengumpulan);
$stmt_p->bind_param("ii", $id_tugas, $id_mahasiswa);
$stmt_p->execute();
$result_p = $stmt_p->get_result();
$pengumpulan = $result_p->fetch_assoc();

$deadline = strtotime($tugas['deadline']);
$now = time();
$is_expired = $deadline < $now;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas - Sistem Tugas</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <div class="headers">
        <div class="content-header">
            <p>Mahasiswa - Detail Tugas</p>
            <h1><?= htmlspecialchars($tugas['judul_tugas']) ?></h1>
        </div>
        <div class="logout-button">
            <a href="/logout">Logout</a>
        </div>
    </div>

    <nav>
        <a href="/dashboard">Dashboard</a>
        <a href="/matakuliah">Mata Kuliah</a>
        <a href="/tugas">Kembali</a>
        <?php if ($pengumpulan && $pengumpulan['nilai'] === null): ?>
            <a href="/tugas/edit-pengumpulan?id=<?= $pengumpulan['id_pengumpulan'] ?>">✏️ Edit Pengumpulan</a>
        <?php endif; ?>
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

    <!-- Detail Tugas Card -->
    <div class="detail-card">
        <div class="detail-card-header">
            <div class="detail-icon">📝</div>
            <div class="detail-title-section">
                <h2><?= htmlspecialchars($tugas['judul_tugas']) ?></h2>
                <p class="detail-subtitle" style="color: white;"><?= htmlspecialchars($tugas['nama_matkul']) ?> (<?= htmlspecialchars($tugas['kode_matkul']) ?>)</p>
            </div>
        </div>

        <div class="detail-card-body">
            <div class="detail-info-grid">
                <div class="info-item">
                    <span class="info-label">Dosen</span>
                    <span class="info-value"><?= htmlspecialchars($tugas['nama_dosen']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Bobot Nilai</span>
                    <span class="info-value"><?= htmlspecialchars($tugas['bobot_nilai']) ?>%</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Deadline</span>
                    <span class="info-value <?= $is_expired ? 'text-danger' : '' ?>">
                        <?= date('d F Y, H:i', strtotime($tugas['deadline'])) ?>
                        <?php if ($is_expired): ?>
                            <span class="badge-small badge-danger">EXPIRED</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="detail-description">
                <h3>Deskripsi Tugas</h3>
                <p><?= nl2br(htmlspecialchars($tugas['deskripsi_tugas'])) ?></p>
            </div>
        </div>
    </div>

    <?php if ($pengumpulan): ?>
        <!-- Submission Status Card -->
        <div class="submission-card">
            <div class="submission-header">
                <h3>Status Pengumpulan</h3>
                <?php 
                $terlambat = strtotime($pengumpulan['tanggal_kumpul']) > $deadline;
                ?>
                <?php if ($pengumpulan['nilai']): ?>
                    <div class="grade-badge">
                        <span class="grade-label">Nilai</span>
                        <span class="grade-value"><?= htmlspecialchars($pengumpulan['nilai']) ?></span>
                    </div>
                <?php else: ?>
                    <span class="status-badge status-pending">Menunggu Penilaian</span>
                <?php endif; ?>
            </div>

            <div class="submission-body">
                <div class="submission-info">
                    <div class="submission-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <div>
                            <span class="submission-label">Tanggal Pengumpulan</span>
                            <span class="submission-value"><?= date('d F Y, H:i', strtotime($pengumpulan['tanggal_kumpul'])) ?></span>
                            <?php if ($terlambat): ?>
                                <span class="warning-text">⚠️ Terlambat (Akan dikurangi 10 poin)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="submission-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                        <div>
                            <span class="submission-label">File</span>
                            <a href="/public/uploads/<?= htmlspecialchars($pengumpulan['nama_file']) ?>" target="_blank" class="file-link">
                                <?= htmlspecialchars($pengumpulan['nama_file']) ?>
                            </a>
                        </div>
                    </div>

                    <?php if ($pengumpulan['link_pengumpulan']): ?>
                        <div class="submission-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                            <div>
                                <span class="submission-label">Link Pengumpulan</span>
                                <a href="<?= htmlspecialchars($pengumpulan['link_pengumpulan']) ?>" target="_blank" class="file-link">
                                    <?= htmlspecialchars($pengumpulan['link_pengumpulan']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($pengumpulan['catatan_dosen']): ?>
                        <div class="feedback-section">
                            <h4>Catatan Dosen</h4>
                            <p><?= nl2br(htmlspecialchars($pengumpulan['catatan_dosen'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Submission Form Card -->
        <div class="submission-form-card">
            <h3>Form Pengumpulan Tugas</h3>
            
            <?php if ($is_expired): ?>
                <div class="alert alert-error">
                    <strong>Maaf, deadline tugas sudah lewat!</strong>
                </div>
            <?php else: ?>
                <form action="/proses/pengumpulan/store.php" method="POST" enctype="multipart/form-data" class="submission-form">
                    <input type="hidden" name="id_tugas" value="<?= $id_tugas ?>">
                    
                    <div class="form-group">
                        <label for="file">Upload File *</label>
                        <input type="file" name="file" id="file" required>
                        <small>Max: 10MB (PDF, DOC, DOCX, ZIP, RAR)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="link_pengumpulan">Link Pengumpulan (Opsional)</label>
                        <input type="url" name="link_pengumpulan" id="link_pengumpulan" placeholder="https://...">
                        <small>Contoh: Google Drive, GitHub, dll</small>
                    </div>
                    
                    <button type="submit" class="btn-submit-form">Kumpulkan Tugas</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </div>
</body>
</html>

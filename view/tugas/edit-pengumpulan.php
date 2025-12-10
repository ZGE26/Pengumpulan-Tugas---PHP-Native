<?php
// Dipanggil melalui routing, session dan db sudah diload
require_once 'proses/tugas/query.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 2) {
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
$id_mahasiswa = $_SESSION['user_id'];

// Ambil detail pengumpulan
$query = "SELECT p.*, t.judul_tugas, t.deskripsi_tugas, t.deadline, t.bobot_nilai, 
          m.nama_matkul, m.kode_matkul, u.nama_lengkap as nama_dosen
          FROM pengumpulan p
          INNER JOIN tugas t ON p.id_tugas = t.id_tugas
          INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
          INNER JOIN user u ON m.id_dosen = u.user_id
          WHERE p.id_pengumpulan = ? AND p.id_mahasiswa = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_pengumpulan, $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Pengumpulan tidak ditemukan atau bukan milik Anda!';
    header('Location: /tugas');
    exit();
}

$pengumpulan = $result->fetch_assoc();

// Cek apakah sudah dinilai
if ($pengumpulan['nilai'] !== null) {
    $_SESSION['error'] = 'Pengumpulan sudah dinilai, tidak bisa diedit!';
    header('Location: /tugas/detail?id=' . $pengumpulan['id_tugas']);
    exit();
}

$deadline = strtotime($pengumpulan['deadline']);
$now = time();
$is_late = strtotime($pengumpulan['tanggal_kumpul']) > $deadline;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengumpulan - Sistem Tugas</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <div class="headers">
        <div class="content-header">
            <p>Mahasiswa - Edit Pengumpulan</p>
            <h1>✏️ Edit Pengumpulan Tugas</h1>
        </div>
        <div class="logout-button">
            <a href="/logout">Logout</a>
        </div>
    </div>

    <nav>
        <a href="/dashboard">Dashboard</a>
        <a href="/tugas">Tugas</a>
        <a href="/tugas/detail?id=<?= $pengumpulan['id_tugas'] ?>">Kembali</a>
    </nav>
    
    <div class="dashboard-container">

    <?php if ($is_late): ?>
        <div class="alert alert-warning">
            <h3>⚠️ Peringatan: Pengumpulan Terlambat</h3>
            <p>Anda telah mengumpulkan tugas ini setelah deadline. Pengumpulan yang terlambat akan mendapat pengurangan nilai -10 poin saat dinilai oleh dosen.</p>
        </div>
    <?php endif; ?>

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

    <!-- Assignment Info Card -->
    <div class="edit-info-card">
        <h2><?= htmlspecialchars($pengumpulan['judul_tugas']) ?></h2>
        
        <div class="info-grid-edit">
            <div class="info-box">
                <span class="info-label">Mata Kuliah</span>
                <span class="info-text"><?= htmlspecialchars($pengumpulan['nama_matkul']) ?> (<?= htmlspecialchars($pengumpulan['kode_matkul']) ?>)</span>
            </div>
            <div class="info-box">
                <span class="info-label">Dosen</span>
                <span class="info-text"><?= htmlspecialchars($pengumpulan['nama_dosen']) ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Deadline</span>
                <span class="info-text"><?= date('d M Y, H:i', $deadline) ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Bobot Nilai</span>
                <span class="info-text"><?= $pengumpulan['bobot_nilai'] ?>%</span>
            </div>
        </div>
    </div>

    <!-- Current Submission Card -->
    <div class="current-submission-card">
        <h3>📄 Pengumpulan Saat Ini</h3>
        
        <div class="current-items">
            <div class="current-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <div>
                    <span class="current-label">Waktu Kumpul</span>
                    <span class="current-value"><?= date('d M Y, H:i:s', strtotime($pengumpulan['tanggal_kumpul'])) ?></span>
                </div>
            </div>

            <?php if (!empty($pengumpulan['nama_file'])): ?>
            <div class="current-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                    <polyline points="13 2 13 9 20 9"></polyline>
                </svg>
                <div>
                    <span class="current-label">File</span>
                    <a href="/public/uploads/<?= htmlspecialchars($pengumpulan['nama_file']) ?>" target="_blank" class="file-link">
                        <?= htmlspecialchars($pengumpulan['nama_file']) ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($pengumpulan['link_pengumpulan'])): ?>
            <div class="current-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                </svg>
                <div>
                    <span class="current-label">Link</span>
                    <a href="<?= htmlspecialchars($pengumpulan['link_pengumpulan']) ?>" target="_blank" class="file-link">
                        <?= htmlspecialchars($pengumpulan['link_pengumpulan']) ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="edit-form-card">
        <h3>Edit Pengumpulan</h3>
        
        <form action="/proses/pengumpulan/update.php" method="POST" enctype="multipart/form-data" class="edit-form">
            <input type="hidden" name="id_pengumpulan" value="<?= $pengumpulan['id_pengumpulan'] ?>">
            <input type="hidden" name="id_tugas" value="<?= $pengumpulan['id_tugas'] ?>">

            <div class="form-group">
                <label for="file_tugas">Upload File Baru (Opsional)</label>
                <input type="file" id="file_tugas" name="file_tugas" accept=".pdf,.doc,.docx,.zip,.rar">
                <small>⚠️ Jika Anda upload file baru, file lama akan terhapus. Tipe file: PDF, DOC, DOCX, ZIP, RAR (Max 10MB)</small>
            </div>

            <div class="form-group">
                <label for="link_pengumpulan">Link Pengumpulan (Opsional)</label>
                <input type="url" 
                       id="link_pengumpulan" 
                       name="link_pengumpulan" 
                       value="<?= htmlspecialchars($pengumpulan['link_pengumpulan'] ?? '') ?>"
                       placeholder="https://drive.google.com/...">
                <small>Contoh: Google Drive, Dropbox, GitHub, dll.</small>
            </div>

            <div class="alert alert-info">
                <h4>💡 Perhatian</h4>
                <ul>
                    <li>Waktu pengumpulan akan diperbarui ke waktu saat ini</li>
                    <li>Pastikan Anda sudah memeriksa file yang akan diupload</li>
                    <li>Pengumpulan yang sudah dinilai tidak dapat diedit</li>
                    <li>File atau link lama akan diganti dengan yang baru</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-update">Update Pengumpulan</button>
                <a href="/tugas/detail?id=<?= $pengumpulan['id_tugas'] ?>" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
    </div>
</body>
</html>

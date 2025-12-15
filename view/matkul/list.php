<?php
require_once 'proses/enrollement/query.php';
require_once 'proses/mata-kuliah/read.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 2) {
    header('Location: /login');
    exit();
}

$enrollments = getAllEnrollByUserId($_SESSION['user_id']);

if (isset($_GET['id_matkul'])) {
    $_SESSION['selected_matkul'] = $_GET['id_matkul'];
    header('Location: /tugas');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Kuliah - Sistem Tugas</title>
    <link rel="stylesheet" href="/project/assets/css/dashboard.css">
</head>

<body>
    <div class="headers">
        <div class="content-header">
            <p>Mahasiswa</p>
            <h1>📚 Mata Kuliah Saya</h1>
        </div>
        <div class="logout-button">
            <a href="/project/logout">Logout</a>
        </div>
    </div>

    <nav>
        <a href="/project/dashboard">Dashboard</a>
        <a href="/project/matakuliah">Mata Kuliah</a>
    </nav>
    
    <div class="dashboard-container">
        <h2>Ambil Mata Kuliah Baru</h2>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['info'])) {
            echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['info']) . '</div>';
            unset($_SESSION['info']);
        }
        ?>
        
        <form action="/project/proses/enrollement/store.php" method="POST" class="enroll-form">
            <div class="form-inline">
                <select name="id_matkul" id="id_matkul" required>
                    <option value="">-- Pilih Mata Kuliah --</option>
                    <?php
                    $matkul_list = getAllMatkul();
                    $enrolled_ids = array_column($enrollments, 'id_matkul');

                    foreach ($matkul_list as $matkul):
                        $is_enrolled = in_array($matkul['id_matkul'], $enrolled_ids);
                        ?>
                        <option value="<?= htmlspecialchars($matkul['id_matkul']) ?>" <?= $is_enrolled ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($matkul['nama_matkul']) ?>
                            (<?= htmlspecialchars($matkul['kode_matkul']) ?>) - 
                            Dosen: <?= htmlspecialchars($matkul['nama_dosen']) ?>
                            <?= $is_enrolled ? ' - SUDAH DIAMBIL' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-enroll">Ambil Mata Kuliah</button>
            </div>
        </form>
        
        <h2 style="margin-top: 48px; margin-bottom: 24px;">Mata Kuliah Saya</h2>
        
        <?php if (!empty($enrollments)): ?>
            <div class="matkul-grid">
                <?php foreach ($enrollments as $enrollment): ?>
                    <a href="/project/matakuliah?id_matkul=<?= $enrollment['id_matkul'] ?>" class="matkul-card">
                        <div class="matkul-card-header">
                            <span class="matkul-badge"><?= htmlspecialchars($enrollment['kode_matkul']) ?></span>
                        </div>
                        <div class="matkul-card-body">
                            <h3 class="matkul-title"><?= htmlspecialchars($enrollment['nama_matkul']) ?></h3>
                            <div class="matkul-info">
                                <div class="matkul-dosen">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <?= htmlspecialchars($enrollment['nama_dosen']) ?>
                                </div>
                                <div class="matkul-date">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    Diambil: <?= date('d M Y', strtotime($enrollment['tanggal_ambil'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="matkul-card-footer">
                            <span class="view-tugas">Lihat Tugas</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3>Belum Ada Mata Kuliah</h3>
                <p>Anda belum mengambil mata kuliah apapun. Silakan ambil mata kuliah menggunakan form di atas.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
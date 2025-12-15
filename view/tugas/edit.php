<?php
// Dipanggil melalui routing, session dan db sudah diload
require_once 'proses/tugas/query.php';
require_once 'proses/mata-kuliah/read.php';

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

// Ambil detail tugas dan pastikan milik dosen ini
$query = "SELECT t.*, m.id_dosen
          FROM tugas t
          INNER JOIN mata_kuliah m ON t.id_matkul = m.id_matkul
          WHERE t.id_tugas = ? AND m.id_dosen = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_tugas, $id_dosen);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Tugas tidak ditemukan atau bukan milik Anda!';
    header('Location: /tugas');
    exit();
}

$tugas = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas - Sistem Tugas</title>
    <link rel="stylesheet" href="/project/assets/css/dashboard.css">
</head>
<body>
    <div class="headers">
        <div class="content-header">
            <p>Dosen - Edit Tugas</p>
            <h1>✏️ Edit Tugas</h1>
        </div>
        <div class="logout-button">
            <a href="/project/logout">Logout</a>
        </div>
    </div>

    <nav>
        <a href="/project/dashboard">Dashboard</a>
        <a href="/project/mata-kuliah">Mata Kuliah</a>
        <a href="/project/tugas">Tugas</a>
    </nav>
    
    <div class="dashboard-container">

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

    <!-- Edit Tugas Card -->
    <div class="edit-tugas-card">
        <div class="edit-tugas-header">
            <h2>Edit Tugas</h2>
            <p>Perbarui informasi tugas yang sudah dibuat</p>
        </div>

        <form action="/project/proses/tugas/update.php" method="POST">
            <input type="hidden" name="id_tugas" value="<?= $id_tugas ?>">
            
            <div class="form-group">
                <label for="id_matkul">Mata Kuliah *</label>
                <select name="id_matkul" id="id_matkul" required>
                    <?php
                    // Ambil hanya mata kuliah yang diampu oleh dosen yang login
                    $matkul_list = getMatkulByDosen($_SESSION['user_id']);
                    foreach ($matkul_list as $matkul): 
                        $selected = ($matkul['id_matkul'] == $tugas['id_matkul']) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($matkul['id_matkul']) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($matkul['kode_matkul']) ?> - <?= htmlspecialchars($matkul['nama_matkul']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="judul_tugas">Judul Tugas *</label>
                <input type="text" name="judul_tugas" id="judul_tugas" 
                       value="<?= htmlspecialchars($tugas['judul_tugas']) ?>" 
                       placeholder="Contoh: Tugas 1 - Analisis Data" required>
            </div>

            <div class="form-group">
                <label for="deskripsi_tugas">Deskripsi Tugas *</label>
                <textarea name="deskripsi_tugas" id="deskripsi_tugas" rows="6" required
                          placeholder="Jelaskan detail tugas yang harus dikerjakan..."><?= htmlspecialchars($tugas['deskripsi_tugas']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bobot_nilai">Bobot Nilai (%) *</label>
                    <input type="number" name="bobot_nilai" id="bobot_nilai" min="0" max="100" 
                           value="<?= htmlspecialchars($tugas['bobot_nilai']) ?>" 
                           placeholder="0-100" required>
                    <small style="color: #6b7280; font-size: 13px; margin-top: 6px; display: block;">
                        Persentase bobot tugas terhadap nilai akhir
                    </small>
                </div>

                <div class="form-group">
                    <label for="deadline">Deadline *</label>
                    <input type="datetime-local" name="deadline" id="deadline" 
                           value="<?= date('Y-m-d\TH:i', strtotime($tugas['deadline'])) ?>" required>
                    <small style="color: #6b7280; font-size: 13px; margin-top: 6px; display: block;">
                        Batas waktu pengumpulan tugas
                    </small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Update Tugas
                </button>
                <a href="/project/tugas" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
    </div>
</body>
</html>

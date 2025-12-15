<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db.php';
require_once 'proses/tugas/read.php';

// Cek login
if (!isset($_SESSION['logged_in'])) {
    header('Location: /login');
    exit();
}

// Ambil data tugas berdasarkan role
if ($_SESSION['role_id'] == 1) {
    // Dosen - ambil tugas yang dibuat
    $tugas_list = getTugasByDosen($_SESSION['user_id']);
} elseif ($_SESSION['role_id'] == 2) {
    // Mahasiswa - harus memilih mata kuliah terlebih dahulu
    if (!isset($_SESSION['selected_matkul'])) {
        // Debug: Log jika session tidak ada
        error_log("REDIRECT: No selected_matkul in session");
        // Redirect ke halaman matakuliah
        header('Location: /matakuliah');
        exit();
    }

    // Debug: Log session yang ada
    error_log("Mahasiswa accessing tugas: user_id=" . $_SESSION['user_id'] . ", selected_matkul=" . $_SESSION['selected_matkul']);

    // Ambil tugas dari mata kuliah yang dipilih
    require_once 'proses/tugas/query.php';
    require_once 'proses/enrollement/query.php';

    // Verifikasi mahasiswa sudah enroll di mata kuliah ini
    $enrolled_courses = getAllEnrollByUserId($_SESSION['user_id']);
    $is_enrolled = false;
    $selected_course = null;

    foreach ($enrolled_courses as $course) {
        if ($course['id_matkul'] == $_SESSION['selected_matkul']) {
            $is_enrolled = true;
            $selected_course = $course;
            break;
        }
    }

    if (!$is_enrolled) {
        $_SESSION['error'] = 'Anda tidak terdaftar di mata kuliah ini!';
        unset($_SESSION['selected_matkul']);
        header('Location: /matakuliah');
        exit();
    }

    $tugas_list = getTugasByMatkul($_SESSION['selected_matkul'], $_SESSION['user_id']);

    // Debug: Cek apakah fungsi mengembalikan data
    if (empty($tugas_list)) {
        error_log("DEBUG: getTugasByMatkul returned empty for id_matkul=" . $_SESSION['selected_matkul'] . " and user_id=" . $_SESSION['user_id']);

        // Tambahan debugging - cek langsung di database
        $test_query = "SELECT COUNT(*) as total FROM tugas WHERE id_matkul = " . intval($_SESSION['selected_matkul']);
        $test_result = $conn->query($test_query);
        $test_row = $test_result->fetch_assoc();
        error_log("DEBUG: Direct query found " . $test_row['total'] . " tugas in database");
    }
} else {
    // Role tidak valid
    header('Location: /login');
    exit();
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas - Sistem Tugas</title>
    <link rel="stylesheet" href="/project/assets/css/dashboard.css">
    <?php if ($_SESSION['role_id'] == 1): ?>
        <link rel="stylesheet" href="/project/assets/css/modal.css">
    <?php endif; ?>
</head>

<body>
    <div class="headers">
        <div class="content-header">
            <?php if ($_SESSION['role_id'] == 1): ?>
                <p>Dosen</p>
                <h1>📝 Daftar Tugas</h1>
            <?php else: ?>
                <p>Mahasiswa</p>
                <h1>📝 Tugas Saya</h1>
            <?php endif; ?>
        </div>
        <div class="logout-button">
            <a href="/project/logout">Logout</a>
        </div>
    </div>
    
    <nav>
        <?php if ($_SESSION['role_id'] == 1): ?>
            <a href="/project/dashboard">Dashboard</a>
            <a href="/project/mata-kuliah">Mata Kuliah</a>
            <a href="/project/tugas">Tugas</a>
        <?php else: ?>
            <a href="/project/dashboard">Dashboard</a>
            <a href="/project/matakuliah">Mata Kuliah</a>
            <a href="/project/tugas">Tugas</a>
            <?php if (isset($selected_course)): ?>
                <a href="/project/matakuliah">🔄 Ganti MK</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="dashboard-container">

    <?php if ($_SESSION['role_id'] == 2 && isset($selected_course)): ?>
        <h2>📚 <?= htmlspecialchars($selected_course['nama_matkul']) ?></h2>
        <p><strong>Kode:</strong> <?= htmlspecialchars($selected_course['kode_matkul']) ?> |
            <strong>Dosen:</strong> <?= htmlspecialchars($selected_course['nama_dosen']) ?>
        </p>
        <hr style="margin: 20px 0;">
    <?php endif; ?>

    <?php
    // Tampilkan pesan error jika ada
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }

    // Tampilkan pesan sukses jika ada
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    ?>

    <?php if ($_SESSION['role_id'] == 1): ?>
        <!-- Header dan Tombol untuk Dosen -->
        <div class="page-header">
            <h2>Tugas yang Anda Buat</h2>
            <button onclick="openModal()" class="btn-add-new">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Buat Tugas Baru
            </button>
        </div>
    <?php else: ?>
        <h2>Tugas yang Harus Dikerjakan</h2>
    <?php endif; ?>

    <?php if (count($tugas_list) > 0): ?>
        <?php if ($_SESSION['role_id'] == 2): ?>
            <!-- Card View untuk Mahasiswa -->
            <div class="tugas-grid">
                <?php foreach ($tugas_list as $tugas):
                    $deadline = strtotime($tugas['deadline']);
                    $now = time();
                    $is_expired = $deadline < $now;
                    $is_submitted = isset($tugas['id_pengumpulan']) && $tugas['id_pengumpulan'];
                    ?>
                    <div class="tugas-card">
                        <div class="tugas-card-header">
                            <div class="tugas-icon">📝</div>
                            <h3 class="tugas-card-title"><?= htmlspecialchars($tugas['judul_tugas']) ?></h3>
                        </div>
                        
                        <div class="tugas-card-body">
                            <p class="tugas-description"><?= htmlspecialchars(substr($tugas['deskripsi_tugas'] ?? '', 0, 120)) ?><?= strlen($tugas['deskripsi_tugas'] ?? '') > 120 ? '...' : '' ?></p>
                            
                            <div class="tugas-meta">
                                <div class="meta-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <span><strong>Deadline:</strong> <?= date('d M Y, H:i', $deadline) ?></span>
                                </div>
                                
                                <div class="meta-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                    <span><strong>Bobot:</strong> <?= htmlspecialchars($tugas['bobot_nilai']) ?>%</span>
                                </div>
                            </div>
                            
                            <div class="tugas-status-row">
                                <?php if ($is_submitted): ?>
                                    <span class="status-badge status-submitted">Sudah Dikumpulkan</span>
                                <?php elseif ($is_expired): ?>
                                    <span class="status-badge status-expired">Terlambat</span>
                                <?php else: ?>
                                    <span class="status-badge status-active">Belum Dikumpulkan</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="tugas-card-footer">
                            <a href="/project/tugas/detail?id=<?= $tugas['id_tugas'] ?>" class="btn-submit-tugas">
                                <?php if ($is_submitted): ?>
                                    Lihat Detail
                                <?php else: ?>
                                    Kumpulkan Tugas
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Table View untuk Dosen -->
            <div class="tugas-table-card">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Tugas</th>
                            <th>Mata Kuliah</th>
                            <th>Bobot Nilai</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($tugas_list as $tugas):
                            $deadline = strtotime($tugas['deadline']);
                            $now = time();
                            $is_expired = $deadline < $now;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($tugas['judul_tugas']) ?></strong>
                                    <br>
                                    <small style="color: #6b7280;"><?= htmlspecialchars(substr($tugas['deskripsi_tugas'] ?? '', 0, 50)) ?><?= strlen($tugas['deskripsi_tugas'] ?? '') > 50 ? '...' : '' ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($tugas['nama_matkul']) ?></strong>
                                    <br>
                                    <span class="kode-badge"><?= htmlspecialchars($tugas['kode_matkul']) ?></span>
                                </td>
                                <td><strong><?= htmlspecialchars($tugas['bobot_nilai']) ?>%</strong></td>
                                <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($tugas['deadline']))) ?></td>
                                <td>
                                    <?php if ($is_expired): ?>
                                        <span class="status-badge status-expired">Expired</span>
                                    <?php else: ?>
                                        <span class="status-badge status-active">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="/project/tugas/pengumpulan?id=<?= $tugas['id_tugas'] ?>" class="btn-view">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M9 11l3 3L22 4"></path>
                                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                            </svg>
                                            Pengumpulan
                                        </a>
                                        <a href="/project/tugas/edit?id=<?= $tugas['id_tugas'] ?>" class="btn-edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                        <a href="/project/proses/tugas/delete.php?id=<?= $tugas['id_tugas'] ?>" class="btn-delete"
                                            onclick="return confirm('Yakin ingin menghapus tugas ini?\n\nPeringatan: Semua pengumpulan mahasiswa akan ikut terhapus!')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <div style="font-size: 64px; margin-bottom: 16px;">📝</div>
            <h3>Belum Ada Tugas</h3>
            <p>
                <?php if ($_SESSION['role_id'] == 1): ?>
                    Klik tombol "Buat Tugas Baru" di atas untuk membuat tugas pertama Anda.
                <?php else: ?>
                    Belum ada tugas untuk mata kuliah ini.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($_SESSION['role_id'] == 1): ?>
        <!-- Modal Form Buat Tugas -->
        <div id="formModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Buat Tugas Baru</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <form action="/project/proses/tugas/store.php" method="POST">
                    <div class="form-group">
                        <label for="id_matkul">Mata Kuliah *</label>
                        <select name="id_matkul" id="id_matkul" required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php
                            require_once 'proses/mata-kuliah/read.php';
                            // Ambil hanya mata kuliah yang diampu oleh dosen yang login
                            $matkul_list = getMatkulByDosen($_SESSION['user_id']);
                            
                            if (count($matkul_list) > 0):
                                foreach ($matkul_list as $matkul): ?>
                                    <option value="<?= $matkul['id_matkul'] ?>">
                                        <?= htmlspecialchars($matkul['kode_matkul']) ?> - <?= htmlspecialchars($matkul['nama_matkul']) ?>
                                    </option>
                                <?php endforeach;
                            else: ?>
                                <option value="" disabled>Anda belum memiliki mata kuliah</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="judul">Judul Tugas *</label>
                        <input type="text" name="judul" id="judul" placeholder="Contoh: Tugas 1 - Analisis Data" required>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Tugas *</label>
                        <textarea name="deskripsi" id="deskripsi" rows="5"
                            placeholder="Jelaskan detail tugas yang harus dikerjakan..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="bobot_nilai">Bobot Nilai (%) *</label>
                        <input type="number" name="bobot_nilai" id="bobot_nilai" min="0" max="100" placeholder="0-100"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Deadline *</label>
                        <input type="datetime-local" name="deadline" id="deadline" required>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn-primary">💾 Simpan Tugas</button>
                        <button type="button" class="btn-secondary" onclick="closeModal()">❌ Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openModal() {
                document.getElementById('formModal').style.display = 'flex';
            }

            function closeModal() {
                document.getElementById('formModal').style.display = 'none';
            }

            window.onclick = function (event) {
                const modal = document.getElementById('formModal');
                if (event.target == modal) {
                    closeModal();
                }
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });
        </script>
    <?php endif; ?>
    </div>
</body>

</html>
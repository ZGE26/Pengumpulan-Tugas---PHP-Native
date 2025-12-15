<?php

require_once 'config/db.php';
require_once 'proses/mata-kuliah/read.php';
require_once 'proses/dosen/read.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: /login');
    exit();
}

$matkul_list = getAllMatkul();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Kuliah - Sistem Tugas</title>
    <link rel="stylesheet" href="/project/assets/css/dashboard.css">
    <link rel="stylesheet" href="/project/assets/css/modal.css">
</head>

<body>
    <div class="headers">
        <div class="content-header">
            <p>Dosen</p>
            <h1>📚 Mata Kuliah</h1>
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

    <?php
    // Tampilkan pesan error jika ada
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }

    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    ?>

    <div class="page-header">
        <h2>Daftar Mata Kuliah</h2>
        <button onclick="openModal()" class="btn-add-new">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Tambah Mata Kuliah Baru
        </button>
    </div>

    <!-- Daftar mata kuliah -->
    <?php if (!empty($matkul_list)): ?>
        <div class="matkul-table-card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Dosen Pengampu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($matkul_list as $matkul):
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><span class="kode-badge"><?php echo htmlspecialchars($matkul['kode_matkul']); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($matkul['nama_matkul']); ?></strong></td>
                            <td><?php echo htmlspecialchars($matkul['nama_dosen']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="/project/mata-kuliah/edit?id=<?= $matkul['id_matkul'] ?>" class="btn-edit">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        Edit
                                    </a>
                                    <a href="/project/proses/mata-kuliah/delete.php?id=<?= $matkul['id_matkul'] ?>" 
                                       class="btn-delete"
                                       onclick="return confirm('Yakin ingin menghapus mata kuliah ini?\n\nPeringatan: Mata kuliah yang memiliki tugas atau mahasiswa terdaftar tidak dapat dihapus.')">
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
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📚</div>
            <h3>Belum Ada Mata Kuliah</h3>
            <p>Silakan tambahkan mata kuliah menggunakan tombol di atas.</p>
        </div>
    <?php endif; ?>

    <!-- Modal Tambah Mata Kuliah -->
    <div id="formModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tambah Mata Kuliah Baru</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form action="/project/proses/mata-kuliah/store.php" method="POST">
                <div class="form-group">
                    <label for="nama_matkul">Nama Mata Kuliah *</label>
                    <input type="text" id="nama_matkul" name="nama_matkul" placeholder="Contoh: Pemrograman Web"
                        required>
                </div>

                <div class="form-group">
                    <label for="kode_matkul">Kode Mata Kuliah *</label>
                    <input type="text" id="kode_matkul" name="kode_matkul" placeholder="Contoh: IF101" required>
                </div>

                <div class="form-group">
                    <label for="id_dosen">Pilih Dosen *</label>
                    <select id="id_dosen" name="id_dosen" required>
                        <option value="">-- Pilih Dosen --</option>
                        <?php
                        $dosen_list = getAllDosen();
                        foreach ($dosen_list as $dosen):
                            ?>
                            <option value="<?php echo $dosen['user_id']; ?>">
                                <?php echo htmlspecialchars($dosen['nama_lengkap']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary">💾 Simpan</button>
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
    </div>
</body>

</html>
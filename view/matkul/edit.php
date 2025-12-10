<?php
require_once 'config/db.php';
require_once 'proses/mata-kuliah/read.php';
require_once 'proses/dosen/read.php';

// Cek login dan role
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: /login');
    exit();
}

// Ambil ID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID Mata Kuliah tidak valid.";
    header('Location: /mata-kuliah');
    exit();
}

$id_matkul = $_GET['id'];
$matkul = getMatkulById($id_matkul);

// Jika mata kuliah tidak ditemukan
if (!$matkul) {
    $_SESSION['error'] = "Mata kuliah tidak ditemukan.";
    header('Location: /mata-kuliah');
    exit();
}

$dosen_list = getAllDosen();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mata Kuliah - Sistem Tugas</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <div class="headers">
        <div class="content-header">
            <p>Dosen - Edit Data</p>
            <h1>✏️ Edit Mata Kuliah</h1>
        </div>
        <div class="logout-button">
            <a href="/logout">Logout</a>
        </div>
    </div>
    
    <nav>
        <a href="/dashboard">Dashboard</a>
        <a href="/mata-kuliah">Mata Kuliah</a>
        <a href="/tugas">Tugas</a>
    </nav>
    
    <div class="dashboard-container">

    <?php
    // Tampilkan pesan error jika ada
    if (isset($_SESSION['error'])) {
        echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
        unset($_SESSION['error']);
    }
    ?>

    <h2>Edit Data Mata Kuliah</h2>
    
    <form action="/proses/mata-kuliah/update.php" method="POST">
        <input type="hidden" name="id_matkul" value="<?= htmlspecialchars($matkul['id_matkul']) ?>">
        
        <table>
            <tr>
                <td><label for="nama_matkul">Nama Mata Kuliah *</label></td>
                <td>
                    <input type="text" id="nama_matkul" name="nama_matkul" 
                           value="<?= htmlspecialchars($matkul['nama_matkul']) ?>" 
                           placeholder="Contoh: Pemrograman Web" required>
                </td>
            </tr>
            <tr>
                <td><label for="kode_matkul">Kode Mata Kuliah *</label></td>
                <td>
                    <input type="text" id="kode_matkul" name="kode_matkul" 
                           value="<?= htmlspecialchars($matkul['kode_matkul']) ?>" 
                           placeholder="Contoh: IF101" required>
                </td>
            </tr>
            <tr>
                <td><label for="id_dosen">Pilih Dosen *</label></td>
                <td>
                    <select id="id_dosen" name="id_dosen" required>
                        <option value="">-- Pilih Dosen --</option>
                        <?php foreach ($dosen_list as $dosen): ?>
                            <option value="<?= $dosen['user_id'] ?>" 
                                <?= ($dosen['user_id'] == $matkul['id_dosen']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dosen['nama_lengkap']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type="submit">💾 Update</button>
                    <a href="/mata-kuliah" style="margin-left: 10px;">
                        <button type="button">❌ Batal</button>
                    </a>
                </td>
            </tr>
        </table>
    </form>
    </div>
</body>
</html>

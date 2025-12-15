<?php
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header('Location: ../../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - Sistem Tugas</title>
    <link rel="stylesheet" href="/project/assets/css/dashboard.css">
</head>
<body>
    <div class="headers">
        <div class="content-header">
            <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</p>
            <h1>Dashboard Dosen</h1>
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

    <h2>Menu Cepat</h2>
    
    <div class="quick-menu-grid">
        <a href="/project/mata-kuliah" class="quick-menu-card">
            <div class="quick-icon">📚</div>
            <div class="quick-content">
                <h3>Kelola Mata Kuliah</h3>
                <p>Buat dan kelola mata kuliah Anda</p>
            </div>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>
        
        <a href="/project/tugas" class="quick-menu-card">
            <div class="quick-icon">📝</div>
            <div class="quick-content">
                <h3>Kelola Tugas</h3>
                <p>Buat tugas dan lihat pengumpulan mahasiswa</p>
            </div>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>
    </div>

    <h2 style="margin-top: 48px;">Informasi Akun</h2>
    
    <div class="account-info-card">
        <div class="account-avatar">
            <div class="avatar-circle">
                <?php echo strtoupper(substr($_SESSION['nama'], 0, 2)); ?>
            </div>
        </div>
        
        <div class="account-details">
            <div class="account-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <div>
                    <span class="account-label">Nama Lengkap</span>
                    <span class="account-value"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                </div>
            </div>
            
            <div class="account-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <div>
                    <span class="account-label">Email</span>
                    <span class="account-value"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                </div>
            </div>
            
            <div class="account-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <div>
                    <span class="account-label">Role</span>
                    <span class="account-value">Dosen</span>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>

</html>
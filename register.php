<?php

if (!defined('DB_HOST')) {
    require_once 'config/db.php';
}

session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Tugas</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Registrasi</h1>
            <p>Buat akun baru untuk memulai</p>
        </div>
        
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
        
        <form action="proses/register.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap Anda" required>
            </div>
            
            <div class="form-group">
                <label for="user_name">Username</label>
                <input type="text" id="user_name" name="user_name" placeholder="Username Anda" required>
                <small>Minimal 4 karakter</small>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="role_id">Daftar Sebagai</label>
                    <select id="role_id" name="role_id" required>
                        <option value="">Pilih Role</option>
                        <option value="2">Mahasiswa</option>
                        <option value="1">Dosen</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="nomor_induk">Nomor Induk</label>
                    <input type="text" id="nomor_induk" name="nomor_induk" placeholder="NIM / NIDN" required>
                    <small>NIM untuk Mahasiswa atau NIDN untuk Dosen</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Buat password Anda" required>
                <small>Minimal 6 karakter</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password Anda" required>
            </div>
            
            <button type="submit" class="auth-button">Daftar</button>
        </form>
        
        <div class="auth-footer">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</body>
</html>

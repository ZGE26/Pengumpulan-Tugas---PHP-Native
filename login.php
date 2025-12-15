<?php

if (!defined('DB_HOST')) {
    require_once 'config/db.php';
}

// session_start() sudah dipanggil di index.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Tugas</title>
    <link rel="stylesheet" href="/project/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Login</h1>
            <p>Masuk ke akun Anda untuk melanjutkan</p>
        </div>
        
        <?php
        // Tampilkan pesan error jika ada
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']); // Hapus error setelah ditampilkan
        }
        
        // Tampilkan pesan success jika ada
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        ?>
        
        <form action="/project/proses/login.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
            </div>
            
            <button type="submit" class="auth-button">Login</button>
        </form>
        
        <div class="auth-footer">
            <p>Belum punya akun? <a href="/project/register">Daftar di sini</a></p>
        </div>
    </div>
</body>
</html>
<?php
session_start();
require_once '../config/db.php';

// Cek apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Metode request tidak valid';
    header('Location: /register.php');
    exit;
}

// Ambil data dari form
$role_id = $_POST['role_id'] ?? '';
$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$nomor_induk = trim($_POST['nomor_induk'] ?? '');
$email = trim($_POST['email'] ?? '');
$user_name = trim($_POST['user_name'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validasi input
if (empty($role_id) || empty($nama_lengkap) || empty($nomor_induk) || empty($email) || empty($user_name) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = 'Semua field harus diisi';
    header('Location: /register.php');
    exit;
}

// Validasi role
if (!in_array($role_id, ['1', '2'])) {
    $_SESSION['error'] = 'Role tidak valid';
    header('Location: /register.php');
    exit;
}

// Validasi username minimal 4 karakter
if (strlen($user_name) < 4) {
    $_SESSION['error'] = 'Username minimal 4 karakter';
    header('Location: /register.php');
    exit;
}

// Validasi password minimal 6 karakter
if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password minimal 6 karakter';
    header('Location: /register.php');
    exit;
}

// Validasi konfirmasi password
if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Password dan konfirmasi password tidak sama';
    header('Location: /register.php');
    exit;
}

// Validasi email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Format email tidak valid';
    header('Location: /register.php');
    exit;
}

// Cek apakah username sudah ada
$stmt = $conn->prepare("SELECT user_id FROM user WHERE user_name = ?");
$stmt->bind_param("s", $user_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = 'Username sudah digunakan';
    $stmt->close();
    header('Location: /register.php');
    exit;
}
$stmt->close();

// Cek apakah nomor induk sudah ada
$stmt = $conn->prepare("SELECT user_id FROM user WHERE nomor_induk = ?");
$stmt->bind_param("s", $nomor_induk);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = 'Nomor Induk sudah terdaftar';
    $stmt->close();
    header('Location: /register.php');
    exit;
}
$stmt->close();

// Cek apakah email sudah ada
$stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = 'Email sudah terdaftar';
    $stmt->close();
    header('Location: /register.php');
    exit;
}
$stmt->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert data ke database
$stmt = $conn->prepare("INSERT INTO user (role_id, user_name, nama_lengkap, nomor_induk, email, password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $role_id, $user_name, $nama_lengkap, $nomor_induk, $email, $hashed_password);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Registrasi berhasil! Silakan login dengan akun Anda.';
    $stmt->close();
    header('Location: /login.php');
    exit;
} else {
    $_SESSION['error'] = 'Terjadi kesalahan saat registrasi: ' . $conn->error;
    $stmt->close();
    header('Location: /register.php');
    exit;
}

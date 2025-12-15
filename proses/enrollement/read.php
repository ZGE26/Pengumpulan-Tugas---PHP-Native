<?php

// File ini dipanggil melalui routing, jadi tidak perlu require config/db.php dan session_start
// Karena sudah dilakukan di index.php
require_once 'proses/enrollement/query.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: /project/login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_SESSION['user_id'];
    $enrollments = getAllEnrollByUserId($userId);

    if ($enrollments) {
        foreach ($enrollments as $enrollment) {
            echo "<p>Mata Kuliah ID: " . htmlspecialchars($enrollment['matkul_id']) . "</p>";
            echo "<p>Tanggal Enroll: " . htmlspecialchars($enrollment['enroll_date']) . "</p>";
            echo "<hr>";
        }
    } else {
        echo "<p>Tidak ada enrollments ditemukan.</p>";
    }
} else {
    echo "<p>Metode permintaan tidak valid.</p>";
}
?>
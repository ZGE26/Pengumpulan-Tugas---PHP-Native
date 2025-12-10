<?php
session_start();

// Load database connection untuk semua route
require_once 'config/db.php';

$request_uri = strtok($_SERVER['REQUEST_URI'], '?');
$request_uri = rtrim($request_uri, '/');

// Define routes with permissions
// Format: '/url' => ['file' => 'path/ke/file.php', 'roles' => [1, 2]] 
// roles: 1 = Dosen, 2 = Mahasiswa, null = semua orang (public)
$routes = [
    '' => ['file' => 'login.php', 'roles' => null],
    '/' => ['file' => 'login.php', 'roles' => null],
    '/login' => ['file' => 'login.php', 'roles' => null],
    '/register' => ['file' => 'register.php', 'roles' => null],
    '/dashboard' => ['file' => 'router_dashboard.php', 'roles' => [1, 2]],
    '/logout' => ['file' => 'proses/logout.php', 'roles' => [1, 2]],
    '/tugas' => ['file' => 'view/tugas/index.php', 'roles' => [1, 2]],
    '/tugas/edit' => ['file' => 'view/tugas/edit.php', 'roles' => [1]],
    '/tugas/detail' => ['file' => 'view/tugas/detail.php', 'roles' => [2]],
    '/tugas/edit-pengumpulan' => ['file' => 'view/tugas/edit-pengumpulan.php', 'roles' => [2]],
    '/tugas/pengumpulan' => ['file' => 'view/tugas/pengumpulan.php', 'roles' => [1]],
    '/tugas/nilai' => ['file' => 'view/tugas/nilai.php', 'roles' => [1]],
    '/mata-kuliah' => ['file' => 'view/matkul/index.php', 'roles' => [1]],
    '/mata-kuliah/edit' => ['file' => 'view/matkul/edit.php', 'roles' => [1]],
    '/matakuliah' => ['file' => 'view/matkul/list.php', 'roles' => [2]],
];

function checkPermission($required_roles) {
    if ($required_roles === null) {
        return true;
    }
    
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        return false;
    }
    
    if (in_array($_SESSION['role_id'], $required_roles)) {
        return true;
    }
    
    return false;
}

// Check if route exists
if (array_key_exists($request_uri, $routes)) {
    $route = $routes[$request_uri];
    $file = $route['file'];
    $roles = $route['roles'];
    
    // Check permission
    if (!checkPermission($roles)) {
        // User tidak punya akses
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            // Belum login, redirect ke login
            header('Location: /login');
            exit();
        } else {
            // Sudah login tapi tidak punya permission
            http_response_code(403);
            echo "<h1>403 - Forbidden</h1>";
            echo "<p>Anda tidak memiliki akses ke halaman ini.</p>";
            echo "<a href='/dashboard'>Kembali ke Dashboard</a>";
            exit();
        }
    }
    
    // User punya permission, load file
    if (file_exists($file)) {
        require_once $file;
    } else {
        http_response_code(404);
        echo "404 - File not found: $file";
    }
} else {
    // Route not found
    http_response_code(404);
    echo "<h1>404 - Page not found</h1>";
    echo "<a href='/dashboard'>Kembali ke Dashboard</a>";
}
?>

<?php
session_start();

// Include database connection
require_once '../config/db.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username dan password harus diisi!";
        header('Location: /project/login');
        exit();
    }
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT u.user_id, u.user_name, u.password, u.nama_lengkap, u.nomor_induk, u.email, u.role_id, r.role_name 
                            FROM user u 
                            INNER JOIN role r ON u.role_id = r.role_id 
                            WHERE u.user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if user exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['user_name'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            $_SESSION['nomor_induk'] = $user['nomor_induk'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['logged_in'] = true;
            
            // Redirect based on role
            if ($user['role_id'] == 1) {
                // Dosen
                header('Location: /project/dashboard');
            } else if ($user['role_id'] == 2) {
                // Mahasiswa
                header('Location: /project/dashboard');
            } else {
                // Default redirect jika role tidak dikenali
                $_SESSION['error'] = "Role tidak valid!";
                header('Location: /project/login');
            }
            exit();
        } else {
            // Password is incorrect
            $_SESSION['error'] = "Username atau password salah!";
            header('Location: /project/login');
            exit();
        }
    } else {
        // User not found
        $_SESSION['error'] = "Username atau password salah!";
        header('Location: /project/login');
        exit();
    }
    
} else {
    // If not POST request, redirect to login page
    header('Location: /project/login');
    exit();
}
?>

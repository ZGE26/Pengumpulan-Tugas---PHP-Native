<?php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'arya');
    define('DB_PASS', '@Padangaro26');
    define('DB_NAME', 'academic_management');
    define('DB_PORT', 3306);
 
    global $conn;
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        die("Connection Failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    if (!$conn) {
        die("Unable to connect to MySQL: " . mysqli_connect_error());
    }
?>
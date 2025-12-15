<?php
session_start();
$_SESSION['success'] = "Anda berhasil logout!";
session_destroy();
session_start();
$_SESSION['success'] = "Anda berhasil logout!";
header('Location: /project/login');
exit();
?>
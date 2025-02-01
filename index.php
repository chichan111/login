<?php
session_start();

// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

// Si no está autenticado, redirigir al login
header("Location: login.php");
exit();
?>
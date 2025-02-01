<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destruir la sesión
session_destroy();

// Registrar la hora de cierre de sesión en la base de datos
require_once 'classes/Database.php';
require_once 'classes/User.php';

if (isset($_SESSION['user_id'])) {
    try {
        $user = new User();
        $user->updateLastLogout($_SESSION['user_id']);
    } catch (Exception $e) {
        // Si hay un error al actualizar la base de datos, simplemente lo ignoramos
        // ya que lo importante es cerrar la sesión
    }
}

// Redirigir al login con mensaje de éxito
session_start();
$_SESSION['success'] = "Has cerrado sesión correctamente.";
header("Location: login.php");
exit();
?>
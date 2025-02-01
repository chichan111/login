<?php
// Función para redirigir
function redirect($url) {
    header("Location: $url");
    exit();
}

// Función para establecer mensaje de error en la sesión
function setError($message) {
    $_SESSION['error'] = $message;
}

// Función para establecer mensaje de éxito en la sesión
function setSuccess($message) {
    $_SESSION['success'] = $message;
}

// Función para limpiar datos de entrada
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para validar correo electrónico
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para validar contraseña
function validatePassword($password) {
    // Mínimo 8 caracteres
    if (strlen($password) < 8) {
        return false;
    }

    // Debe contener al menos una letra mayúscula
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }

    // Debe contener al menos una letra minúscula
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }

    // Debe contener al menos un número
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }

    // Debe contener al menos un carácter especial
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        return false;
    }

    return true;
}

// Función para generar token seguro
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Función para verificar si el usuario tiene un rol específico
function hasRole($roleId) {
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == $roleId;
}

// Función para formatear fecha
function formatDate($date) {
    return date('d/m/Y H:i:s', strtotime($date));
}

// Función para verificar permisos
function checkPermission($requiredRole) {
    if (!isAuthenticated()) {
        setError("Debe iniciar sesión para acceder a esta página");
        redirect('../login.php');
    }

    if (!hasRole($requiredRole)) {
        setError("No tiene permisos para acceder a esta página");
        redirect('../admin/dashboard.php');
    }
}

// Función para generar un slug único
function generateSlug($text) {
    // Reemplazar caracteres no alfanuméricos
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterar
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Remover caracteres indeseados
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Remover guiones duplicados
    $text = preg_replace('~-+~', '-', $text);
    // Convertir a minúsculas
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

// Función para validar una fecha
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Función para obtener la IP del cliente
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Función para registrar actividad
function logActivity($userId, $action, $details = '') {
    global $db;
    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $details, getClientIP()]);
}
?>
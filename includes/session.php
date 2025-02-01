<?php
// Iniciar o reanudar la sesión
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configurar cookies seguras
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_only_cookies', 1);
        
        // Configurar el manejo de sesión
        session_name('secure_session');
        session_start();
        
        // Regenerar el ID de sesión periódicamente
        if (!isset($_SESSION['last_regeneration'])) {
            regenerateSession();
        } else {
            // Regenerar cada 30 minutos
            $interval = 1800;
            if (time() - $_SESSION['last_regeneration'] >= $interval) {
                regenerateSession();
            }
        }
    }
}

// Regenerar ID de sesión
function regenerateSession() {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Verificar si existe una sesión activa
function checkSession() {
    initSession();
    
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Verificar tiempo de inactividad (30 minutos)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        destroySession();
        return false;
    }
    
    // Actualizar tiempo de última actividad
    $_SESSION['last_activity'] = time();
    return true;
}

// Destruir sesión
function destroySession() {
    session_unset();
    session_destroy();
    
    // Destruir la cookie de sesión
    if (isset($_COOKIE[session_name()])) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
}

// Establecer datos de sesión
function setSessionData($key, $value) {
    $_SESSION[$key] = $value;
}

// Obtener datos de sesión
function getSessionData($key) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
}

// Eliminar datos de sesión específicos
function unsetSessionData($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

// Verificar token CSRF
function validateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
    
    if ($token === null || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    
    return true;
}

// Generar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Añadir token CSRF a un formulario
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// Verificar remember me token
function checkRememberMe() {
    if (isset($_COOKIE['remember_token']) && !isset($_SESSION['user_id'])) {
        require_once 'classes/User.php';
        $user = new User();
        $userData = $user->getUserByRememberToken($_COOKIE['remember_token']);
        
        if ($userData) {
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['username'] = $userData['username'];
            $_SESSION['role_id'] = $userData['role_id'];
            
            // Regenerar token para seguridad adicional
            $newToken = bin2hex(random_bytes(32));
            $user->updateRememberToken($userData['id'], $newToken);
            setcookie('remember_token', $newToken, time() + (86400 * 30), '/');
            
            return true;
        }
    }
    return false;
}

// Inicializar la sesión automáticamente
initSession();
?>
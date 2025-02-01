<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once 'functions.php';

// Verificar si se recibió una acción
if (!isset($_POST['action'])) {
    redirect('../login.php');
}

$action = $_POST['action'];
$user = new User();

switch ($action) {
    case 'login':
        handleLogin($user);
        break;
    case 'register':
        handleRegister($user);
        break;
    default:
        redirect('../login.php');
}

// Función para manejar el login
function handleLogin($user) {
    // Validar campos requeridos
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        setError("Todos los campos son requeridos");
        redirect('../login.php');
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    try {
        $result = $user->login($username, $password);
        
        if ($result['success']) {
            // Iniciar sesión
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['role_id'] = $result['user']['role_id'];

            // Si seleccionó "recordarme", crear cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 días
                $user->storeRememberToken($result['user']['id'], $token);
            }

            // Actualizar último login
            $user->updateLastLogin($result['user']['id']);

            redirect('../admin/dashboard.php');
        } else {
            setError($result['message']);
            redirect('../login.php');
        }
    } catch (Exception $e) {
        setError("Error al intentar iniciar sesión. Por favor, intente nuevamente.");
        redirect('../login.php');
    }
}

// Función para manejar el registro
function handleRegister($user) {
    // Verificar permisos
    if (!isset($_SESSION['user_id'])) {
        setError("No tiene permisos para realizar esta acción");
        redirect('../login.php');
    }

    // Validar campos requeridos
    $requiredFields = ['username', 'email', 'password', 'confirm_password', 'first_name', 'last_name', 'role_id'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            setError("Todos los campos son requeridos");
            redirect('../register.php');
        }
    }

    // Validar que las contraseñas coincidan
    if ($_POST['password'] !== $_POST['confirm_password']) {
        setError("Las contraseñas no coinciden");
        redirect('../register.php');
    }

    // Validar complejidad de la contraseña
    if (!validatePassword($_POST['password'])) {
        setError("La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial");
        redirect('../register.php');
    }

    try {
        $userData = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'password' => $_POST['password'],
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'role_id' => (int)$_POST['role_id']
        ];

        $result = $user->register($userData);
        
        if ($result['success']) {
            setSuccess("Usuario registrado exitosamente");
            redirect('../admin/users.php');
        } else {
            setError($result['message']);
            redirect('../register.php');
        }
    } catch (Exception $e) {
        setError("Error al registrar el usuario. Por favor, intente nuevamente.");
        redirect('../register.php');
    }
}
?>
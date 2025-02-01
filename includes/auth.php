<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/User.php';

// Verificar si se recibió una acción
if (!isset($_POST['action'])) {
    header("Location: ../login.php");
    exit();
}

$action = $_POST['action'];

if ($action === 'login') {
    handleLogin();
} elseif ($action === 'register') {
    handleRegister();
} else {
    header("Location: ../login.php");
    exit();
}

function handleLogin() {
    try {
        if (!isset($_POST['username']) || !isset($_POST['password'])) {
            throw new Exception("Todos los campos son requeridos");
        }

        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        // Para debugging
        error_log("Intento de login - Usuario: " . $username);
        
        $user = new User();
        $result = $user->login($username, $password);

        if ($result['success']) {
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['role_id'] = $result['user']['role_id'];

            // Si se seleccionó "recordarme"
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 días
                $user->storeRememberToken($result['user']['id'], $token);
            }

            // Actualizar último login
            $user->updateLastLogin($result['user']['id']);
            
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Usuario o contraseña incorrectos";
            header("Location: ../login.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Error en login: " . $e->getMessage());
        $_SESSION['error'] = "Error al iniciar sesión: " . $e->getMessage();
        header("Location: ../login.php");
        exit();
    }
}

function handleRegister() {
    try {
        // Verificar si hay usuario logueado
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Debe iniciar sesión para registrar usuarios");
        }

        // Validar campos requeridos
        $requiredFields = ['username', 'email', 'password', 'confirm_password', 'first_name', 'last_name', 'role_id'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                throw new Exception("Todos los campos son requeridos");
            }
        }

        // Validar que las contraseñas coincidan
        if ($_POST['password'] !== $_POST['confirm_password']) {
            throw new Exception("Las contraseñas no coinciden");
        }

        $user = new User();
        $result = $user->register([
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'password' => $_POST['password'],
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'role_id' => (int)$_POST['role_id']
        ]);

        if ($result['success']) {
            $_SESSION['success'] = "Usuario registrado exitosamente";
            header("Location: ../admin/users.php");
        } else {
            throw new Exception($result['message']);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../register.php");
    }
    exit();
}
?>
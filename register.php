<?php
session_start();

// Verificar si el usuario tiene permiso para registrar usuarios
require_once 'classes/User.php';
require_once 'classes/Role.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar si el usuario tiene permiso para registrar
$user = new User();
$role = new Role();
$currentUser = $user->getById($_SESSION['user_id']);
$userRole = $role->getById($currentUser['role_id']);

if (!$userRole['can_register_users']) {
    $_SESSION['error'] = "No tienes permiso para registrar usuarios.";
    header("Location: admin/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Sistema de Administración</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center py-5">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h1 class="h3">Registro de Usuario</h1>
                            <p class="text-muted">Completa el formulario para crear un nuevo usuario</p>
                        </div>

                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error']; 
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="includes/auth.php" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="register">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">Nombre</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="first_name" 
                                           name="first_name" 
                                           required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese el nombre
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Apellido</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="last_name" 
                                           name="last_name" 
                                           required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese el apellido
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingrese un nombre de usuario
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingrese un correo electrónico válido
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           required 
                                           minlength="8">
                                    <div class="invalid-feedback">
                                        La contraseña debe tener al menos 8 caracteres
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           required>
                                    <div class="invalid-feedback">
                                        Las contraseñas deben coincidir
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Rol</label>
                                <select class="form-select" id="role" name="role_id" required>
                                    <option value="">Seleccione un rol</option>
                                    <?php
                                    $roles = $role->getAll();
                                    foreach ($roles as $r) {
                                        echo "<option value='" . $r['id'] . "'>" . htmlspecialchars($r['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor seleccione un rol
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Registrar Usuario
                                </button>
                                <a href="admin/dashboard.php" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de formulario
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }

                        // Validación personalizada de contraseñas
                        var password = document.getElementById('password')
                        var confirm_password = document.getElementById('confirm_password')
                        
                        if (password.value !== confirm_password.value) {
                            confirm_password.setCustomValidity('Las contraseñas no coinciden')
                            event.preventDefault()
                            event.stopPropagation()
                        } else {
                            confirm_password.setCustomValidity('')
                        }

                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
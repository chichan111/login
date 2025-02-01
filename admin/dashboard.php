<?php
session_start();
require_once '../includes/session.php';
require_once '../classes/User.php';
require_once '../classes/Role.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user = new User();
$role = new Role();

// Obtener información del usuario actual
$currentUser = $user->getById($_SESSION['user_id']);
$userRole = $role->getById($currentUser['role_id']);

// Obtener estadísticas para el dashboard
$totalUsers = $user->countAll();
$totalRoles = $role->countAll();

// Obtener usuarios recientes
$recentUsers = $user->getAll(5, 0); // Últimos 5 usuarios
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel de Administración</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-heading p-3 border-bottom">
                <h5 class="m-0">Panel de Control</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action active">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="users.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
                <a href="roles.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-person-badge me-2"></i> Roles
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper" class="bg-light">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-link" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="dropdown ms-auto">
                        <button class="btn btn-link dropdown-toggle text-dark text-decoration-none" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($currentUser['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="profile.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Dashboard Content -->
            <div class="container-fluid px-4 py-4">
                <div class="row">
                    <div class="col-12">
                        <h1 class="h3 mb-4">Dashboard</h1>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h6 class="card-title mb-4">Total Usuarios</h6>
                                <h2 class="card-value mb-0"><?php echo $totalUsers; ?></h2>
                                <i class="bi bi-people card-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h6 class="card-title mb-4">Total Roles</h6>
                                <h2 class="card-value mb-0"><?php echo $totalRoles; ?></h2>
                                <i class="bi bi-person-badge card-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h6 class="card-title mb-4">Usuarios Activos</h6>
                                <h2 class="card-value mb-0">
                                    <?php echo $user->countAll("is_active = 1"); ?>
                                </h2>
                                <i class="bi bi-person-check card-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h6 class="card-title mb-4">Usuarios Inactivos</h6>
                                <h2 class="card-value mb-0">
                                    <?php echo $user->countAll("is_active = 0"); ?>
                                </h2>
                                <i class="bi bi-person-x card-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-4">Usuarios por Rol</h6>
                                <canvas id="usersByRoleChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-4">Actividad de Usuarios</h6>
                                <canvas id="userActivityChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title mb-4">Usuarios Recientes</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Email</th>
                                                <th>Rol</th>
                                                <th>Estado</th>
                                                <th>Último Acceso</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentUsers as $user): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="activity-avatar me-3">
                                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars($user['username']); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo htmlspecialchars($user['role_name']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($user['is_active']): ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        echo $user['last_login'] 
                                                            ? date('d/m/Y H:i', strtotime($user['last_login']))
                                                            : 'Nunca';
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
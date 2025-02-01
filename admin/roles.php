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

// Verificar si el usuario es administrador
if ($currentUser['role_id'] != 1) { // Asumiendo que 1 es el ID del rol administrador
    $_SESSION['error'] = "No tienes permiso para gestionar roles.";
    header('Location: dashboard.php');
    exit();
}

// Configuración de paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Búsqueda
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Obtener roles paginados
$roles = $role->getAllPaginated($limit, $offset, $search);
$totalRoles = $role->countAll($search);
$totalPages = ceil($totalRoles / $limit);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles - Panel de Administración</title>
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
                <a href="dashboard.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="users.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
                <a href="roles.php" class="list-group-item list-group-item-action active">
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

            <!-- Content -->
            <div class="container-fluid px-4 py-4">
                <!-- Header -->
                <div class="row align-items-center mb-4">
                    <div class="col">
                        <h1 class="h3 mb-0">Gestión de Roles</h1>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                            <i class="bi bi-plus-circle me-1"></i> Nuevo Rol
                        </button>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Search Bar -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Buscar roles..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Roles Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre del Rol</th>
                                        <th>Descripción</th>
                                        <th>Permisos</th>
                                        <th>Usuarios</th>
                                        <th>Fecha de Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles as $r): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($r['name']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($r['description']); ?></td>
                                        <td>
                                            <?php if($r['can_register_users']): ?>
                                                <span class="badge bg-success">Registro de Usuarios</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $userCount = $user->countAll("role_id = " . $r['id']);
                                            echo "<span class='badge bg-primary'>$userCount usuarios</span>";
                                            ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editRoleModal" 
                                                        data-role-id="<?php echo $r['id']; ?>"
                                                        <?php echo $r['id'] == 1 ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-role" 
                                                        data-role-id="<?php echo $r['id']; ?>"
                                                        <?php echo $r['id'] == 1 ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">
                                        Anterior
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">
                                        Siguiente
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Role Modal -->
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createRoleForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre del Rol</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="can_register_users" name="can_register_users">
                                <label class="form-check-label" for="can_register_users">
                                    Puede registrar usuarios
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="createRoleForm" class="btn btn-primary">Crear Rol</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editRoleForm" class="needs-validation" novalidate>
                        <input type="hidden" id="edit_role_id" name="role_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Nombre del Rol</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_can_register_users" name="can_register_users">
                                <label class="form-check-label" for="edit_can_register_users">
                                    Puede registrar usuarios
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="editRoleForm" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Confirm Delete Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar este rol?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Esta acción no se puede deshacer. Si hay usuarios asignados a este rol, la eliminación no será posible.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Users Modal -->
    <div class="modal fade" id="viewUsersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Usuarios con este Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Estado</th>
                                    <th>Último Acceso</th>
                                </tr>
                            </thead>
                            <tbody id="roleUsersList">
                                <!-- Los usuarios se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        // Script específico para la página de roles
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo del modal de edición
            const editRoleModal = document.getElementById('editRoleModal');
            if (editRoleModal) {
                editRoleModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const roleId = button.getAttribute('data-role-id');
                    
                    // Cargar datos del rol
                    fetch(`../api/roles.php?id=${roleId}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('edit_role_id').value = data.id;
                            document.getElementById('edit_name').value = data.name;
                            document.getElementById('edit_description').value = data.description;
                            document.getElementById('edit_can_register_users').checked = data.can_register_users == 1;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showAlert('error', 'Error al cargar los datos del rol');
                        });
                });
            }

            // Ver usuarios de un rol
            const viewUsersButtons = document.querySelectorAll('.view-role-users');
            viewUsersButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const roleId = this.getAttribute('data-role-id');
                    loadRoleUsers(roleId);
                });
            });

            // Eliminar rol
            let roleIdToDelete = null;
            const deleteButtons = document.querySelectorAll('.delete-role');
            const confirmDeleteBtn = document.getElementById('confirmDelete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    roleIdToDelete = this.getAttribute('data-role-id');
                    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                    modal.show();
                });
            });

            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    if (roleIdToDelete) {
                        deleteRole(roleIdToDelete);
                    }
                });
            }
        });

        // Función para cargar usuarios de un rol
        function loadRoleUsers(roleId) {
            fetch(`../api/users.php?role_id=${roleId}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('roleUsersList');
                    tbody.innerHTML = '';
                    
                    data.forEach(user => {
                        const row = `
                            <tr>
                                <td>${user.username}</td>
                                <td>${user.email}</td>
                                <td>
                                    <span class="badge bg-${user.is_active ? 'success' : 'danger'}">
                                        ${user.is_active ? 'Activo' : 'Inactivo'}
                                    </span>
                                </td>
                                <td>${user.last_login ? new Date(user.last_login).toLocaleString() : 'Nunca'}</td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });

                    const modal = new bootstrap.Modal(document.getElementById('viewUsersModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Error al cargar los usuarios del rol');
                });
        }

        // Función para eliminar rol
        function deleteRole(roleId) {
            fetch('../api/roles.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: roleId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Rol eliminado exitosamente');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message || 'Error al eliminar el rol');
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
                modal.hide();
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Error al eliminar el rol');
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
                modal.hide();
            });
        }
    </script>
</body>
</html>
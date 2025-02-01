// Inicialización del panel de administración
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar sidebar
    initSidebar();

    // Inicializar DataTables si existe
    initDataTables();

    // Inicializar gráficos si existen
    initCharts();

    // Inicializar gestión de usuarios
    initUserManagement();

    // Inicializar gestión de roles
    initRoleManagement();

    // Inicializar búsqueda y filtros
    initSearchAndFilters();
});

// Gestión del sidebar
function initSidebar() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.main-content');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
            
            // Guardar estado en localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });

        // Recuperar estado guardado
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            content.classList.add('expanded');
        }
    }
}

// Inicialización de DataTables
function initDataTables() {
    const tables = document.querySelectorAll('.datatable');
    tables.forEach(table => {
        new DataTable(table, {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            responsive: true,
            pageLength: 10,
            order: [[0, 'desc']],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    });
}

// Gestión de usuarios
function initUserManagement() {
    // Crear usuario
    const createUserForm = document.getElementById('createUserForm');
    if (createUserForm) {
        createUserForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!validateForm('createUserForm')) return;

            const formData = new FormData(createUserForm);
            try {
                const response = await fetchData('/admin/users/create', {
                    method: 'POST',
                    body: formData
                });

                if (response.success) {
                    showAlert('success', 'Usuario creado exitosamente');
                    createUserForm.reset();
                    if (typeof refreshUserTable === 'function') {
                        refreshUserTable();
                    }
                } else {
                    showAlert('error', response.message);
                }
            } catch (error) {
                showAlert('error', 'Error al crear usuario');
            }
        });
    }

    // Editar usuario
    const editUserForms = document.querySelectorAll('.edit-user-form');
    editUserForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!validateForm(form.id)) return;

            const formData = new FormData(form);
            const userId = form.dataset.userId;

            try {
                const response = await fetchData(`/admin/users/update/${userId}`, {
                    method: 'POST',
                    body: formData
                });

                if (response.success) {
                    showAlert('success', 'Usuario actualizado exitosamente');
                    if (typeof refreshUserTable === 'function') {
                        refreshUserTable();
                    }
                    // Cerrar modal si existe
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                    if (modal) modal.hide();
                } else {
                    showAlert('error', response.message);
                }
            } catch (error) {
                showAlert('error', 'Error al actualizar usuario');
            }
        });
    });

    // Eliminar usuario
    const deleteUserBtns = document.querySelectorAll('.delete-user');
    deleteUserBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const userId = btn.dataset.userId;
            
            const confirmed = await confirmAction('¿Está seguro de que desea eliminar este usuario?');
            if (!confirmed) return;

            try {
                const response = await fetchData(`/admin/users/delete/${userId}`, {
                    method: 'POST'
                });

                if (response.success) {
                    showAlert('success', 'Usuario eliminado exitosamente');
                    if (typeof refreshUserTable === 'function') {
                        refreshUserTable();
                    }
                } else {
                    showAlert('error', response.message);
                }
            } catch (error) {
                showAlert('error', 'Error al eliminar usuario');
            }
        });
    });

    // Cambiar estado de usuario (activar/desactivar)
    const toggleUserStatus = document.querySelectorAll('.toggle-user-status');
    toggleUserStatus.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const userId = btn.dataset.userId;
            const currentStatus = btn.dataset.status;
            const newStatus = currentStatus === '1' ? '0' : '1';
            const statusText = newStatus === '1' ? 'activar' : 'desactivar';

            const confirmed = await confirmAction(`¿Está seguro de que desea ${statusText} este usuario?`);
            if (!confirmed) return;

            try {
                const response = await fetchData(`/admin/users/toggle-status/${userId}`, {
                    method: 'POST',
                    body: JSON.stringify({ status: newStatus })
                });

                if (response.success) {
                    showAlert('success', `Usuario ${statusText}do exitosamente`);
                    if (typeof refreshUserTable === 'function') {
                        refreshUserTable();
                    }
                } else {
                    showAlert('error', response.message);
                }
            } catch (error) {
                showAlert('error', `Error al ${statusText} usuario`);
            }
        });
    });
}

// Gestión de roles
function initRoleManagement() {
    // Crear rol
    const createRoleForm = document.getElementById('createRoleForm');
    if (createRoleForm) {
        createRoleForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!validateForm('createRoleForm')) return;

            const formData = new FormData(createRoleForm);
            try {
                const response = await fetchData('/admin/roles/create', {
                    method: 'POST',
                    body: formData
                });

                if (response.success) {
                    showAlert('success', 'Rol creado exitosamente');
                    createRoleForm.reset();
                    if (typeof refreshRoleTable === 'function') {
                        refreshRoleTable();
                    }
                } else {
                    showAlert('error', response.message);
                }
            } catch (error) {
                showAlert('error', 'Error al crear rol');
            }
        });
    }

    // Editar rol
    const editRoleForms = document.querySelectorAll('.edit-role-form');
    editRoleForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!validateForm(form.id)) return;

            const formData = new FormData(form);
            const roleId = form.dataset.roleId;

            try {
                const response = await fetchData(`/admin/roles/update/${roleId}`, {
                    method: 'POST',
                    body: formData
                });

                if (response.success) {
                    showAlert('success', 'Rol actualizado exitosamente');
                    if (typeof refreshRoleTable === 'function') {
                        refreshRoleTable();
                    }
                    // Cerrar modal si existe
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editRoleModal'));
                    if (modal) modal.hide();
                } else {
                    showAlert('error', response.message);
                }
            } catch (error) {
                showAlert('error', 'Error al actualizar rol');
            }
        });
    });

    // Eliminar rol
    const deleteRoleBtns = document.querySelectorAll('.delete-role');
    deleteRoleBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const roleId = btn.dataset.roleId;
            
            const confirmed = await confirmAction('¿Está seguro de que desea eliminar este rol?');
            if (!confirmed) return;

            try {
                const response = await fetchData(`/admin/roles/delete/${roleId}`, {
                    method: 'POST'
                });

                if (response.success) {
                    showAlert('success', 'Rol eliminado exitosamente');
                    if (typeof refreshRoleTable === 'function') {
                        refreshRoleTable();
                    }
                } else {
                    showAlert('error', response.message);
                }
            } catch (error) {
                showAlert('error', 'Error al eliminar rol');
            }
        });
    });
}

// Inicialización de gráficos
function initCharts() {
    // Gráfico de usuarios por rol
    const usersByRoleChart = document.getElementById('usersByRoleChart');
    if (usersByRoleChart) {
        new Chart(usersByRoleChart, {
            type: 'pie',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e',
                        '#e74a3b'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Gráfico de actividad de usuarios
    const userActivityChart = document.getElementById('userActivityChart');
    if (userActivityChart) {
        new Chart(userActivityChart, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Usuarios activos',
                    data: [],
                    borderColor: '#4e73df',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Actualizar datos de los gráficos
    updateChartData();
}

// Función para actualizar datos de los gráficos
async function updateChartData() {
    try {
        const response = await fetchData('/admin/dashboard/chart-data');
        if (response.success) {
            // Actualizar gráfico de usuarios por rol
            const usersByRoleChart = Chart.getChart('usersByRoleChart');
            if (usersByRoleChart) {
                usersByRoleChart.data.labels = response.roleData.labels;
                usersByRoleChart.data.datasets[0].data = response.roleData.data;
                usersByRoleChart.update();
            }

            // Actualizar gráfico de actividad
            const userActivityChart = Chart.getChart('userActivityChart');
            if (userActivityChart) {
                userActivityChart.data.labels = response.activityData.labels;
                userActivityChart.data.datasets[0].data = response.activityData.data;
                userActivityChart.update();
            }
        }
    } catch (error) {
        console.error('Error al actualizar datos de los gráficos:', error);
    }
}

// Inicialización de búsqueda y filtros
function initSearchAndFilters() {
    // Búsqueda en tiempo real
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = e.target.value.trim();
                updateTableWithSearch(searchTerm);
            }, 500);
        });
    }

    // Filtros
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', () => {
            applyFilters();
        });
    });
}

// Función para actualizar tabla con búsqueda
async function updateTableWithSearch(searchTerm) {
    const tableBody = document.querySelector('.table tbody');
    if (!tableBody) return;

    try {
        const response = await fetchData('/admin/search', {
            method: 'POST',
            body: JSON.stringify({ search: searchTerm })
        });

        if (response.success) {
            // Actualizar tabla con resultados
            tableBody.innerHTML = response.html;
            // Reinicializar eventos en los nuevos elementos
            initTableActions();
        }
    } catch (error) {
        console.error('Error en la búsqueda:', error);
    }
}

// Función para aplicar filtros
async function applyFilters() {
    const filters = {};
    document.querySelectorAll('.filter-select').forEach(select => {
        if (select.value) {
            filters[select.name] = select.value;
        }
    });

    try {
        const response = await fetchData('/admin/filter', {
            method: 'POST',
            body: JSON.stringify({ filters })
        });

        if (response.success) {
            const tableBody = document.querySelector('.table tbody');
            if (tableBody) {
                tableBody.innerHTML = response.html;
                // Reinicializar eventos en los nuevos elementos
                initTableActions();
            }
        }
    } catch (error) {
        console.error('Error al aplicar filtros:', error);
    }
}

// Función para reinicializar acciones de la tabla
function initTableActions() {
    // Reinicializar botones de eliminar
    initUserManagement();
    initRoleManagement();
    
    // Reinicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Exportar funciones necesarias
window.refreshUserTable = async function() {
    await updateTableWithSearch(document.getElementById('searchInput')?.value || '');
};

window.refreshRoleTable = async function() {
    await updateTableWithSearch(document.getElementById('searchInput')?.value || '');
};
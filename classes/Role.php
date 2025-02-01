<?php
require_once 'Database.php';

class Role {
    private $db;
    private $table = 'roles';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Obtener todos los roles
    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error al obtener roles: " . $e->getMessage());
        }
    }

    // Obtener rol por ID
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            throw new Exception("Error al obtener rol: " . $e->getMessage());
        }
    }

    // Crear nuevo rol
    public function create($data) {
        try {
            // Verificar si ya existe un rol con el mismo nombre
            if ($this->nameExists($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un rol con ese nombre'
                ];
            }

            $query = "INSERT INTO " . $this->table . " 
                     (name, description, can_register_users) 
                     VALUES (?, ?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $data['name'],
                $data['description'],
                isset($data['can_register_users']) ? 1 : 0
            ]);

            return [
                'success' => true,
                'role_id' => $this->db->lastInsertId(),
                'message' => 'Rol creado exitosamente'
            ];
        } catch (Exception $e) {
            throw new Exception("Error al crear rol: " . $e->getMessage());
        }
    }

    // Actualizar rol
    public function update($id, $data) {
        try {
            // Verificar si el nuevo nombre ya existe (excluyendo el rol actual)
            if ($this->nameExists($data['name'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un rol con ese nombre'
                ];
            }

            $query = "UPDATE " . $this->table . " 
                     SET name = ?, 
                         description = ?, 
                         can_register_users = ? 
                     WHERE id = ?";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $data['name'],
                $data['description'],
                isset($data['can_register_users']) ? 1 : 0,
                $id
            ]);

            return [
                'success' => true,
                'message' => 'Rol actualizado exitosamente'
            ];
        } catch (Exception $e) {
            throw new Exception("Error al actualizar rol: " . $e->getMessage());
        }
    }

    // Eliminar rol
    public function delete($id) {
        try {
            // Verificar si hay usuarios con este rol
            if ($this->hasUsers($id)) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar el rol porque hay usuarios asignados a él'
                ];
            }

            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);

            return [
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
            ];
        } catch (Exception $e) {
            throw new Exception("Error al eliminar rol: " . $e->getMessage());
        }
    }

    // Verificar si existe un nombre de rol
    private function nameExists($name, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE name = ?";
        $params = [$name];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Verificar si hay usuarios asignados al rol
    private function hasUsers($roleId) {
        $query = "SELECT COUNT(*) FROM users WHERE role_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$roleId]);
        return $stmt->fetchColumn() > 0;
    }

    // Obtener roles con paginación y búsqueda
    public function getAllPaginated($limit = 10, $offset = 0, $search = '') {
        try {
            $query = "SELECT * FROM " . $this->table;
            $params = [];

            if (!empty($search)) {
                $query .= " WHERE name LIKE ? OR description LIKE ?";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm];
            }

            $query .= " ORDER BY name LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error al obtener roles: " . $e->getMessage());
        }
    }

    // Contar total de roles
    public function countAll($search = '') {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table;
            $params = [];

            if (!empty($search)) {
                $query .= " WHERE name LIKE ? OR description LIKE ?";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm];
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception("Error al contar roles: " . $e->getMessage());
        }
    }

    // Verificar si un rol tiene un permiso específico
    public function hasPermission($roleId, $permission) {
        try {
            $query = "SELECT $permission FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$roleId]);
            return (bool)$stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception("Error al verificar permiso: " . $e->getMessage());
        }
    }
}
?>
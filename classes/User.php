<?php
require_once 'Database.php';

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Método de login actualizado
    public function login($username, $password) {
        try {
            error_log("Iniciando proceso de login para usuario: " . $username);
            
            $query = "SELECT u.*, r.name as role_name, r.can_register_users 
                    FROM " . $this->table . " u 
                    JOIN roles r ON u.role_id = r.id 
                    WHERE u.username = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            error_log("Consulta completada. Usuario encontrado: " . ($user ? 'SI' : 'NO'));

            if ($user && password_verify($password, $user['password'])) {
                error_log("Contraseña verificada correctamente");
                unset($user['password']);
                return [
                    'success' => true,
                    'user' => $user
                ];
            }

            error_log("Autenticación fallida");
            return [
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos'
            ];
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            throw new Exception("Error en el login: " . $e->getMessage());
        }
    }

    // Método de registro
    public function register($userData) {
        try {
            if ($this->usernameExists($userData['username'])) {
                return [
                    'success' => false,
                    'message' => 'El nombre de usuario ya está en uso'
                ];
            }

            if ($this->emailExists($userData['email'])) {
                return [
                    'success' => false,
                    'message' => 'El correo electrónico ya está en uso'
                ];
            }

            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            $query = "INSERT INTO " . $this->table . " 
                    (username, email, password, first_name, last_name, role_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['first_name'],
                $userData['last_name'],
                $userData['role_id']
            ]);

            return [
                'success' => true,
                'user_id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            throw new Exception("Error en el registro: " . $e->getMessage());
        }
    }

    // Verificar si existe un username
    private function usernameExists($username) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE username = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }

    // Verificar si existe un email
    private function emailExists($email) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    // Obtener usuario por ID
    public function getById($id) {
        $query = "SELECT u.*, r.name as role_name 
                FROM " . $this->table . " u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            unset($user['password']);
        }
        return $user;
    }

    // Actualizar usuario
    public function update($id, $userData) {
        try {
            $fieldsToUpdate = [];
            $params = [];
            
            foreach ($userData as $key => $value) {
                if ($key !== 'id' && $key !== 'password') {
                    $fieldsToUpdate[] = "$key = ?";
                    $params[] = $value;
                }
            }

            if (!empty($userData['password'])) {
                $fieldsToUpdate[] = "password = ?";
                $params[] = password_hash($userData['password'], PASSWORD_DEFAULT);
            }

            $params[] = $id;
            
            $query = "UPDATE " . $this->table . " SET " . 
                    implode(", ", $fieldsToUpdate) . 
                    " WHERE id = ?";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Usuario actualizado correctamente'
            ];
        } catch (Exception $e) {
            throw new Exception("Error al actualizar usuario: " . $e->getMessage());
        }
    }

    // Eliminar usuario (desactivar)
    public function delete($id) {
        try {
            $query = "UPDATE " . $this->table . " SET is_active = 0 WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);

            return [
                'success' => true,
                'message' => 'Usuario desactivado correctamente'
            ];
        } catch (Exception $e) {
            throw new Exception("Error al eliminar usuario: " . $e->getMessage());
        }
    }

    // Obtener todos los usuarios
    public function getAll($limit = 10, $offset = 0, $search = '') {
        try {
            $query = "SELECT u.*, r.name as role_name 
                    FROM " . $this->table . " u 
                    JOIN roles r ON u.role_id = r.id";
            $params = [];

            if (!empty($search)) {
                $query .= " WHERE (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_fill(0, 4, $searchTerm);
            }

            $query .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error al obtener usuarios: " . $e->getMessage());
        }
    }

    // Actualizar último login
    public function updateLastLogin($userId) {
        try {
            $query = "UPDATE " . $this->table . " SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Error actualizando último login: " . $e->getMessage());
            return false;
        }
    }

    // Almacenar token de "recordarme"
    public function storeRememberToken($userId, $token) {
        try {
            $query = "UPDATE " . $this->table . " SET remember_token = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$token, $userId]);
        } catch (Exception $e) {
            error_log("Error almacenando token: " . $e->getMessage());
            return false;
        }
    }

    // Obtener usuario por token de "recordarme"
    public function getUserByRememberToken($token) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE remember_token = ? AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            if ($user) {
                unset($user['password']);
            }
            return $user;
        } catch (Exception $e) {
            error_log("Error obteniendo usuario por token: " . $e->getMessage());
            return null;
        }
    }

    // Cambiar contraseña
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            $query = "SELECT password FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ];
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = "UPDATE " . $this->table . " SET password = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$hashedPassword, $userId]);

            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ];
        } catch (Exception $e) {
            throw new Exception("Error al cambiar la contraseña: " . $e->getMessage());
        }
    }

    // Contar total de usuarios
    public function countAll($condition = '') {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table;
            $params = [];

            if (!empty($condition)) {
                $query .= " WHERE " . $condition;
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception("Error al contar usuarios: " . $e->getMessage());
        }
    }
}
?>
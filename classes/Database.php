<?php
class Database {
    private $host = "localhost";
    private $db_name = "login_system";
    private $username = "root";
    private $password = "";
    private $conn;

    // Obtener la conexión
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
        } catch(PDOException $e) {
            throw new Exception("Error de conexión: " . $e->getMessage());
        }

        return $this->conn;
    }

    // Método para ejecutar consultas SELECT
    public function select($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            throw new Exception("Error en la consulta: " . $e->getMessage());
        }
    }

    // Método para ejecutar consultas INSERT
    public function insert($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            throw new Exception("Error al insertar: " . $e->getMessage());
        }
    }

    // Método para ejecutar consultas UPDATE
    public function update($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            throw new Exception("Error al actualizar: " . $e->getMessage());
        }
    }

    // Método para ejecutar consultas DELETE
    public function delete($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            throw new Exception("Error al eliminar: " . $e->getMessage());
        }
    }

    // Método para iniciar una transacción
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    // Método para confirmar una transacción
    public function commit() {
        return $this->conn->commit();
    }

    // Método para revertir una transacción
    public function rollback() {
        return $this->conn->rollBack();
    }

    // Método para verificar si existe un registro
    public function exists($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            throw new Exception("Error al verificar existencia: " . $e->getMessage());
        }
    }

    // Método para obtener un solo registro
    public function fetchOne($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch(PDOException $e) {
            throw new Exception("Error al obtener registro: " . $e->getMessage());
        }
    }
}
?>
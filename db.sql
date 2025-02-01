-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS login_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE login_system;

-- Tabla de roles
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    can_register_users BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de usuarios
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) DEFAULT NULL,
    last_login TIMESTAMP NULL,
    last_password_change TIMESTAMP NULL,
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- Tabla de registro de actividad
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Tabla de intentos de inicio de sesión
CREATE TABLE login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50),
    ip_address VARCHAR(45),
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB;

-- Tabla de sesiones
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT,
    last_activity INT,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Tabla de recuperación de contraseña
CREATE TABLE password_resets (
    email VARCHAR(100) NOT NULL,
    token VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX password_resets_email_index (email),
    INDEX password_resets_token_index (token)
) ENGINE=InnoDB;

-- Insertar roles predeterminados
INSERT INTO roles (name, description, can_register_users) VALUES
('Administrador', 'Control total del sistema', TRUE),
('Supervisor', 'Puede ver registros y crear usuarios', TRUE),
('Usuario', 'Acceso básico al sistema', FALSE);

-- Insertar usuario administrador predeterminado
-- Password: Admin132**
INSERT INTO users (username, email, password, first_name, last_name, role_id) VALUES
('admin', 'admin@system.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 1);

-- Crear índices para optimizar búsquedas
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_activity_user ON activity_log(user_id);
CREATE INDEX idx_activity_created ON activity_log(created_at);
CREATE INDEX idx_login_attempts_username ON login_attempts(username);
CREATE INDEX idx_login_attempts_ip ON login_attempts(ip_address);
CREATE INDEX idx_sessions_user ON sessions(user_id);

-- Crear vistas útiles
CREATE VIEW active_users AS
SELECT u.*, r.name as role_name
FROM users u
JOIN roles r ON u.role_id = r.id
WHERE u.is_active = 1;

CREATE VIEW user_activity_summary AS
SELECT 
    u.username,
    u.email,
    r.name as role_name,
    COUNT(a.id) as total_activities,
    MAX(a.created_at) as last_activity
FROM users u
LEFT JOIN activity_log a ON u.id = a.user_id
JOIN roles r ON u.role_id = r.id
GROUP BY u.id;

-- Triggers para logging
DELIMITER //

CREATE TRIGGER after_user_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF OLD.is_active != NEW.is_active THEN
        INSERT INTO activity_log (user_id, action, details)
        VALUES (NEW.id, 'user_status_changed', 
                CONCAT('Status changed from ', OLD.is_active, ' to ', NEW.is_active));
    END IF;
    
    IF OLD.role_id != NEW.role_id THEN
        INSERT INTO activity_log (user_id, action, details)
        VALUES (NEW.id, 'role_changed',
                CONCAT('Role changed from ', OLD.role_id, ' to ', NEW.role_id));
    END IF;
END//

CREATE TRIGGER after_user_login
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.last_login != OLD.last_login THEN
        INSERT INTO activity_log (user_id, action, details)
        VALUES (NEW.id, 'user_login', 'User logged in');
    END IF;
END//

DELIMITER ;
-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS login_system;
USE login_system;

-- Tabla de roles
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    can_register_users BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

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
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Insertar rol de administrador
INSERT INTO roles (name, description, can_register_users) 
VALUES ('Administrator', 'Full system access with all privileges', TRUE);

-- Insertar usuario administrador (password: Admin132**)
INSERT INTO users (username, email, password, first_name, last_name, role_id) 
VALUES (
    'admin',
    'admin@system.com',
    '$2y$10$YourHashedPasswordHere',  -- Asegúrate de generar un hash real
    'System',
    'Administrator',
    1
);
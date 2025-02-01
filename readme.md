# Sistema de Login

Sistema de autenticación y gestión de usuarios con roles desarrollado en PHP.

## Estructura del Proyecto

```
login_system/
├── assets/
│   ├── css/
│   │   ├── style.css          # Estilos personalizados
│   │   ├── admin.css          # Estilos del panel admin
│   │   └── bootstrap.min.css  # Bootstrap CSS
│   ├── js/
│   │   ├── main.js           # JavaScript personalizado
│   │   ├── admin.js          # JavaScript del panel admin
│   │   └── bootstrap.bundle.min.js  # Bootstrap JS
│   └── img/                  # Imágenes
├── includes/
│   ├── auth.php             # Autenticación
│   ├── functions.php        # Funciones auxiliares
│   └── session.php          # Manejo de sesiones
├── admin/
│   ├── dashboard.php        # Panel principal
│   ├── users.php           # Gestión de usuarios
│   └── roles.php           # Gestión de roles
├── classes/
│   ├── User.php            # Clase de usuarios
│   ├── Role.php            # Clase de roles
│   └── Database.php        # Clase de base de datos
├── index.php               # Página de inicio
├── login.php              # Login
├── register.php           # Registro
└── logout.php             # Logout
```

## Características

- Sistema de login seguro
- Panel de administración con gestión de usuarios y roles
- Diseño responsive usando Bootstrap 5
- Validación de formularios con JavaScript
- Gestión de sesiones segura
- Protección contra inyección SQL usando PDO

## Credenciales por defecto

- Usuario: admin
- Contraseña: Admin132**

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/Nginx
- Bootstrap 5

## Instalación

1. Clonar el repositorio
2. Importar la base de datos desde el archivo SQL proporcionado
3. Configurar la conexión a la base de datos en classes/Database.php
4. El sistema estará listo para usar con las credenciales por defecto

## Archivos CSS y JavaScript

- style.css: Estilos generales del sistema
- admin.css: Estilos específicos del panel de administración
- main.js: Funciones JavaScript generales
- admin.js: Funciones JavaScript para el panel de administración
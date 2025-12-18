<?php
// Evita el acceso directo a este archivo
if (!defined('ACCESO_PERMITIDO')) {
    die('Acceso denegado.');
}

// Configuración de la BD
define('DB_HOST', 'sql102.infinityfree.com');  
define('DB_USER', 'if0_40651945');   
define('DB_PASS', 'hImKkqISNX6TqW'); 
define('DB_NAME', 'if0_40651945_inscripciones');   

// Credenciales de Acceso al Panel de Administración 
define('ADMIN_USER', 'Viviana');
define('ADMIN_PASS', '1234'); 

// Función de Conexión a la BD
function obtenerConexion() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Error de conexión a la base de datos. Intente más tarde.");
    }

    // Establecer codificación UTF8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Función de Autenticación 
function esAdminAutenticado() {
    // Si la sesión no existe o no tiene 'admin_logueado', no está autenticado
    return isset($_SESSION['admin_logueado']) && $_SESSION['admin_logueado'] === true;
}

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
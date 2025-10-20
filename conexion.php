<?php
// ===========================================
// Archivo: conexion.php
// ===========================================

// ** 1. Configuración de la Base de Datos **
// Modifica estos valores con tus credenciales
$host = 'localhost';         // Servidor local
$db   = 'morbido_ropas'; // Debes crear esta base de datos
$user = 'root';              // Usuario por defecto de MySQL
$pass = '';                  // Contraseña (vacía por defecto en XAMPP/WAMP)
$charset = 'utf8mb4';        // Recomendado para caracteres especiales

// Cadena de Conexión (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    // Lanza excepciones en caso de error (muy útil para depurar)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Devuelve los resultados como arrays asociativos (clave => valor)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Deshabilita la emulación de consultas preparadas para mayor seguridad
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // Intenta establecer la conexión
     $pdo = new PDO($dsn, $user, $pass, $options);
     // La variable $pdo ya está lista para usarse en otras páginas
} catch (\PDOException $e) {
     // Si falla la conexión, muestra un error seguro
     die("Error de Conexión: " . $e->getMessage());
}
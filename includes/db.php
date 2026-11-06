
<?php
$host = 'localhost';
$db   = 'sistema_servidores';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Centralización de administradores del sistema
define('ADMIN_EMAILS', [
    'mlopez@ies21.edu.ar',
    // Agrega aquí los emails de tus otros 3 administradores:
    'rasis@ies21.edu.ar',
    'fahumada@ies21.edu.ar',
    'memoretti@ies21.edu.ar'
    ]);

/**
 * Función global para verificar si la sesión actual es de un Administrador
 */
function esAdmin($sessionUser = null) {
    if (!$sessionUser) {
        if (!isset($_SESSION['user'])) return false;
        $sessionUser = $_SESSION['user'];
    }
    
    $email = is_array($sessionUser) ? ($sessionUser['email'] ?? '') : $sessionUser;
    return in_array($email, ADMIN_EMAILS);
}
?>
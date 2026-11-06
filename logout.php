<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $nombre = $user['name'] ?? ($user['nombre'] ?? 'Usuario');
    $email = $user['email'] ?? 'Desconocido';
    $ip = $_SERVER['REMOTE_ADDR'];

    // Insertar evento de auditoría LOGOUT
    $stmtLogout = $pdo->prepare("INSERT INTO historial_accesos (usuario, email, accion, ip) VALUES (?, ?, 'LOGOUT', ?)");
    $stmtLogout->execute([$nombre, $email, $ip]);
}

// Destruir todas las variables de sesión
$_SESSION = array();
session_destroy();

// Redirigir al login
header('Location: index.php');
exit();
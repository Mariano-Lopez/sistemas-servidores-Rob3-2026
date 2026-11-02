<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void
{
    if (!isset($_SESSION['usuario'])) {
        header('Location: index.php');
        exit;
    }
}

function currentUser(): ?string
{
    return $_SESSION['usuario'] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Monitoreo</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header class="topbar">
        <div class="brand">Monitoreo de Servidores</div>

        <?php if (isset($_SESSION['usuario'])): ?>
            <nav class="nav" aria-label="Navegación principal">
                <a href="dashboard.php">Dashboard</a>
                <a href="configuracion.php">Configuración</a>
                <a href="log_temperaturas.php">Histórico</a>
            </nav>

            <div class="user">
                <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <a href="logout.php">Cerrar sesión</a>
            </div>
        <?php endif; ?>
    </header>

    <main class="container">

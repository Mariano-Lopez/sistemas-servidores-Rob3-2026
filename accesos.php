<?php
session_start();
require_once 'includes/db.php';

// Validación de seguridad centralizada
if (!esAdmin()) {
    header('Location: dashboard.php');
    exit();
}

// Consultar los últimos 50 accesos registrados
$stmt = $pdo->query("SELECT * FROM historial_accesos ORDER BY fecha_hora DESC LIMIT 50");
$accesos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría de Accesos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand">Monitoreo de Sala de Servidores</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="configuracion.php" class="btn btn-outline-light btn-sm me-2">Configuración</a>
            <a href="log_temperaturas.php" class="btn btn-outline-light btn-sm me-2">Historial de Temperaturas</a>
            <?php if (esAdmin()): ?>
                <!-- Solo visible para los 4 administradores -->
                <a href="accesos.php" class="btn btn-light btn-sm me-2">Auditoría de Accesos</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
        </div>
    </nav>
    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($accesos) > 0): ?>
                                <?php foreach ($accesos as $log): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i:s', strtotime($log['fecha_hora'])) ?></td>
                                        <td><strong><?= htmlspecialchars($log['usuario']) ?></strong></td>
                                        <td><?= htmlspecialchars($log['email']) ?></td>
                                        <td>
                                            <?php if ($log['accion'] === 'LOGIN'): ?>
                                                <span class="badge bg-success">Ingreso</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Salida</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay registros de acceso acumulados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
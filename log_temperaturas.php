<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
require_once 'includes/db.php';

// Obtener los últimos 100 registros ordenados por fecha descendente
$logs = $pdo->query("SELECT * FROM historial_temperaturas ORDER BY fecha_hora DESC LIMIT 100")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Logs - Control de Servidores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand">Monitoreo de Sala de Servidores</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="configuracion.php" class="btn btn-outline-light btn-sm me-2">Configuración</a>
            <a href="log_temperaturas.php" class="btn btn-light btn-sm me-2">Historial de Temperaturas</a>
            <?php if (esAdmin()): ?>
                <!-- Solo visible para los 4 administradores -->
                <a href="accesos.php" class="btn btn-outline-light btn-sm me-2">Auditoría de Accesos</a>
            <?php endif; ?>   
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Historial de Temperaturas</h2>
        <div class="card shadow p-4 mt-3">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Temperatura (°C)</th>
                            <th>Humedad (%)</th>
                            <th>Estado Lógico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($logs) > 0): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i:s', strtotime($log['fecha_hora'])) ?></td>
                                    <td><?= htmlspecialchars($log['temperatura']) ?> °C</td>
                                    <td><?= $log['humedad'] ? htmlspecialchars($log['humedad']) . ' %' : '--' ?></td>
                                    <td>
                                        <?php if ($log['temperatura'] >= 30): ?>
                                            <span class="badge bg-danger">Peligro - Apagado</span>
                                        <?php elseif ($log['temperatura'] >= 26): ?>
                                            <span class="badge bg-warning text-dark">Alerta Alta</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No hay registros de temperatura aún.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
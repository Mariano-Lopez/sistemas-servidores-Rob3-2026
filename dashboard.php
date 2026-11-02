<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
require_once 'includes/db.php';
$servidores = $pdo->query("SELECT * FROM servidores")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Control de Servidores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand">Monitoreo de Sala de Servidores</span>
        <div>
            <a href="dashboard.php" class="btn btn-light btn-sm me-2">Dashboard</a>
            <a href="configuracion.php" class="btn btn-outline-light btn-sm me-2">Configuración</a>
            <a href="log_temperaturas.php" class="btn btn-outline-light btn-sm me-2">Logs</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow p-3 mb-3 text-center">
                    <h5>Temperatura Actual</h5>
                    <!-- Se agregaron IDs para que JavaScript los encuentre -->
                    <h2 id="val_temp" class="text-muted">-- °C</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow p-3 mb-3 text-center">
                    <h5>Humedad</h5>
                    <h2 id="val_hum" class="text-info">-- %</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow p-3 mb-3 text-center">
                    <h5>Estado Climatización</h5>
                    <h2 id="val_estado" class="text-muted">Cargando...</h2>
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mt-2">
            <h4>Estado de los Servidores</h4>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Servidor</th>
                        <th>Dirección IP</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($servidores) > 0): ?>
                        <?php foreach ($servidores as $srv): ?>
                            <tr>
                                <td><?= htmlspecialchars($srv['nombre']) ?></td>
                                <td><?= htmlspecialchars($srv['ip']) ?></td>
                                <td>
                                    <?php if (strtolower($srv['estado']) === 'activo'): ?>
                                        <span class="badge bg-success">Prendido</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Apagado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center">No hay servidores registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script de actualización en tiempo real -->
    <script>
        function actualizarDashboard() {
            fetch('api_dashboard.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) return;
                    
                    const tempEl = document.getElementById('val_temp');
                    tempEl.textContent = data.temperatura + ' °C';
                    tempEl.className = data.color;

                    document.getElementById('val_hum').textContent = data.humedad + ' %';
                    
                    const estadoEl = document.getElementById('val_estado');
                    estadoEl.textContent = data.estado;
                    estadoEl.className = data.color;
                })
                .catch(error => console.error('Error al actualizar datos:', error));
        }

        // Ejecutar inmediatamente al cargar y luego cada 15 segundos
        actualizarDashboard();
        setInterval(actualizarDashboard, 15000);
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
require_once 'includes/db.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar_umbrales') {
        $temp_alta = $_POST['temp_alta'];
        $temp_apagado = $_POST['temp_apagado'];
        $stmt = $pdo->prepare("UPDATE configuracion_sistema SET temp_alta = ?, temp_apagado_servers = ? WHERE id = 1");
        $stmt->execute([$temp_alta, $temp_apagado]);
        $mensaje = "Umbrales actualizados.";
    }

    if ($accion === 'guardar_intervalos') {
        $temp_valor = (int)$_POST['temp_valor'];
        $temp_unidad = $_POST['temp_unidad'];
        $ping_valor = (int)$_POST['ping_valor'];
        $ping_unidad = $_POST['ping_unidad'];

        $stmt = $pdo->prepare("UPDATE configuracion_sistema SET 
            intervalo_temp_valor = ?, 
            intervalo_temp_unidad = ?, 
            intervalo_ping_valor = ?, 
            intervalo_ping_unidad = ? 
            WHERE id = 1");
        $stmt->execute([$temp_valor, $temp_unidad, $ping_valor, $ping_unidad]);
        $mensaje = "Intervalos de tiempo actualizados.";
    }

    if ($accion === 'agregar_servidor') {
        $nombre = $_POST['nombre_servidor'];
        $ip = $_POST['ip_servidor'];
        $stmt = $pdo->prepare("INSERT INTO servidores (nombre, ip) VALUES (?, ?)");
        $stmt->execute([$nombre, $ip]);
        $mensaje = "Servidor agregado.";
    }

    if ($accion === 'editar_servidor') {
        $id = $_POST['id_servidor'];
        $nombre = $_POST['nombre_servidor'];
        $ip = $_POST['ip_servidor'];
        $estado = $_POST['estado_servidor'];
        $stmt = $pdo->prepare("UPDATE servidores SET nombre = ?, ip = ?, estado = ? WHERE id = ?");
        $stmt->execute([$nombre, $ip, $estado, $id]);
        $mensaje = "Servidor actualizado.";
    }

    if ($accion === 'eliminar_servidor') {
        $id = $_POST['id_servidor'];
        $stmt = $pdo->prepare("DELETE FROM servidores WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje = "Servidor eliminado.";
    }

    if ($accion === 'agregar_contacto') {
        $nombre = $_POST['nombre_contacto'];
        $telefono = $_POST['telefono_contacto'];
        $email = $_POST['email_contacto'];
        $stmt = $pdo->prepare("INSERT INTO contactos (nombre, telefono, email) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $telefono, $email]);
        $mensaje = "Contacto agregado.";
    }

    if ($accion === 'editar_contacto') {
        $id = $_POST['id_contacto'];
        $nombre = $_POST['nombre_contacto'];
        $telefono = $_POST['telefono_contacto'];
        $email = $_POST['email_contacto'];
        $stmt = $pdo->prepare("UPDATE contactos SET nombre = ?, telefono = ?, email = ? WHERE id = ?");
        $stmt->execute([$nombre, $telefono, $email, $id]);
        $mensaje = "Contacto actualizado.";
    }

    if ($accion === 'eliminar_contacto') {
        $id = $_POST['id_contacto'];
        $stmt = $pdo->prepare("DELETE FROM contactos WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje = "Contacto eliminado.";
    }
}

$config = $pdo->query("SELECT * FROM configuracion_sistema WHERE id = 1")->fetch();
$servidores = $pdo->query("SELECT * FROM servidores")->fetchAll();
$contactos = $pdo->query("SELECT * FROM contactos")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración - Control de Servidores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand">Monitoreo de Sala de Servidores</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="configuracion.php" class="btn btn-light btn-sm me-2">Configuración</a>
            <a href="log_temperaturas.php" class="btn btn-outline-light btn-sm me-2">Historial de Temperaturas</a>
            <?php if (esAdmin()): ?>
                <a href="accesos.php" class="btn btn-outline-light btn-sm me-2">Auditoría de Accesos</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Panel de Configuración</h2>
        <?php if ($mensaje): ?>
            <div class="alert alert-success mt-3"><?= $mensaje ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Umbrales Térmicos -->
            <div class="col-md-6">
                <div class="card shadow p-4 my-3">
                    <h4>Umbrales Térmicos de Seguridad</h4>
                    <form method="POST" class="row g-3 mt-1">
                        <input type="hidden" name="accion" value="guardar_umbrales">
                        <div class="col-md-6">
                            <label class="form-label">Alerta Alta (°C)</label>
                            <input type="number" step="0.1" name="temp_alta" class="form-control" value="<?= $config['temp_alta'] ?? 26 ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apagado (°C)</label>
                            <input type="number" step="0.1" name="temp_apagado" class="form-control" value="<?= $config['temp_apagado_servers'] ?? 30 ?>" required>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary w-100">Guardar Umbrales</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Frecuencia de Medición -->
            <div class="col-md-6">
                <div class="card shadow p-4 my-3">
                    <h4>Intervalos de Frecuencia</h4>
                    <form method="POST" class="row g-3 mt-1">
                        <input type="hidden" name="accion" value="guardar_intervalos">
                        <div class="col-md-6">
                            <label class="form-label">Medición Temp.</label>
                            <input type="number" name="temp_valor" min="1" class="form-control" value="<?= $config['intervalo_temp_valor'] ?? 15 ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unidad</label>
                            <select name="temp_unidad" class="form-select">
                                <option value="segundos" <?= ($config['intervalo_temp_unidad'] ?? '') === 'segundos' ? 'selected' : '' ?>>Segundos</option>
                                <option value="minutos" <?= ($config['intervalo_temp_unidad'] ?? '') === 'minutos' ? 'selected' : '' ?>>Minutos</option>
                                <option value="horas" <?= ($config['intervalo_temp_unidad'] ?? '') === 'horas' ? 'selected' : '' ?>>Horas</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pings a Servidores</label>
                            <input type="number" name="ping_valor" min="1" class="form-control" value="<?= $config['intervalo_ping_valor'] ?? 30 ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unidad</label>
                            <select name="ping_unidad" class="form-select">
                                <option value="segundos" <?= ($config['intervalo_ping_unidad'] ?? '') === 'segundos' ? 'selected' : '' ?>>Segundos</option>
                                <option value="minutos" <?= ($config['intervalo_ping_unidad'] ?? '') === 'minutos' ? 'selected' : '' ?>>Minutos</option>
                                <option value="horas" <?= ($config['intervalo_ping_unidad'] ?? '') === 'horas' ? 'selected' : '' ?>>Horas</option>
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary w-100">Guardar Intervalos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- ABM Servidores -->
            <div class="col-md-6">
                <div class="card shadow p-4 mb-4">
                    <h4>Gestión de Servidores</h4>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="accion" value="agregar_servidor">
                        <div class="mb-2">
                            <input type="text" name="nombre_servidor" class="form-control" placeholder="Nombre del Servidor" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="ip_servidor" class="form-control" placeholder="Dirección IP" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">Agregar Servidor</button>
                    </form>
                    <hr>
                    <ul class="list-group">
                        <?php foreach ($servidores as $srv): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong><?= htmlspecialchars($srv['nombre']) ?></strong> <br><small><?= htmlspecialchars($srv['ip']) ?> (<?= htmlspecialchars($srv['estado']) ?>)</small></span>
                                <div>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editSrv<?= $srv['id'] ?>">Editar</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="accion" value="eliminar_servidor">
                                        <input type="hidden" name="id_servidor" value="<?= $srv['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">X</button>
                                    </form>
                                </div>
                            </li>

                            <!-- Modal Editar Servidor -->
                            <div class="modal fade" id="editSrv<?= $srv['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Servidor</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="accion" value="editar_servidor">
                                                <input type="hidden" name="id_servidor" value="<?= $srv['id'] ?>">
                                                <div class="mb-2">
                                                    <label>Nombre</label>
                                                    <input type="text" name="nombre_servidor" class="form-control" value="<?= htmlspecialchars($srv['nombre']) ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label>IP</label>
                                                    <input type="text" name="ip_servidor" class="form-control" value="<?= htmlspecialchars($srv['ip']) ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Estado</label>
                                                    <select name="estado_servidor" class="form-control">
                                                        <option value="Activo" <?= strtolower($srv['estado']) === 'activo' ? 'selected' : '' ?>>Activo</option>
                                                        <option value="Inactivo" <?= strtolower($srv['estado']) === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- ABM Contactos -->
            <div class="col-md-6">
                <div class="card shadow p-4 mb-4">
                    <h4>Gestión de Contactos</h4>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="accion" value="agregar_contacto">
                        <div class="mb-2">
                            <input type="text" name="nombre_contacto" class="form-control" placeholder="Nombre" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="telefono_contacto" class="form-control" placeholder="Teléfono" required>
                        </div>
                        <div class="mb-2">
                            <input type="email" name="email_contacto" class="form-control" placeholder="Correo" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">Agregar Contacto</button>
                    </form>
                    <hr>
                    <ul class="list-group">
                        <?php foreach ($contactos as $c): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong><?= htmlspecialchars($c['nombre']) ?></strong> <br><small><?= htmlspecialchars($c['telefono']) ?></small></span>
                                <div>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCont<?= $c['id'] ?>">Editar</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="accion" value="eliminar_contacto">
                                        <input type="hidden" name="id_contacto" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">X</button>
                                    </form>
                                </div>
                            </li>

                            <!-- Modal Editar Contacto -->
                            <div class="modal fade" id="editCont<?= $c['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Contacto</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="accion" value="editar_contacto">
                                                <input type="hidden" name="id_contacto" value="<?= $c['id'] ?>">
                                                <div class="mb-2">
                                                    <label>Nombre</label>
                                                    <input type="text" name="nombre_contacto" class="form-control" value="<?= htmlspecialchars($c['nombre']) ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Teléfono</label>
                                                    <input type="text" name="telefono_contacto" class="form-control" value="<?= htmlspecialchars($c['telefono']) ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label>Correo</label>
                                                    <input type="email" name="email_contacto" class="form-control" value="<?= htmlspecialchars($c['email']) ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
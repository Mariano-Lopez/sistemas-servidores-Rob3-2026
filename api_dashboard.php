<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}
require_once 'includes/db.php';

$ultima = $pdo->query("SELECT * FROM historial_temperaturas ORDER BY fecha_hora DESC LIMIT 1")->fetch();
$config = $pdo->query("SELECT * FROM configuracion_sistema WHERE id = 1")->fetch();

$temp_alta = $config['temp_alta'] ?? 26.00;
$temp_apagado = $config['temp_apagado_servers'] ?? 30.00;

$temp_actual = $ultima ? $ultima['temperatura'] : '--';
$humedad_actual = ($ultima && $ultima['humedad'] !== null) ? $ultima['humedad'] : '--';

$estado_clima = 'Normal';
$color_clima = 'text-success';

if ($temp_actual !== '--') {
    if ($temp_actual >= $temp_apagado) {
        $estado_clima = 'PELIGRO - Apagando';
        $color_clima = 'text-danger';
    } elseif ($temp_actual >= $temp_alta) {
        $estado_clima = 'Alerta Alta';
        $color_clima = 'text-warning';
    }
}

// ... tus consultas anteriores (temperatura actual, humedad, etc.)

// Obtener la fecha de la última medición de temperatura
$queryTemp = $pdo->query("SELECT MAX(fecha_hora) as ultima_temp FROM historial_temperaturas");
$ultima_temp = $queryTemp ? ($queryTemp->fetchColumn() ?: 'Sin registros') : 'Error BD';

// Obtener la fecha del último ping a los servidores
$queryPing = $pdo->query("SELECT MAX(ultima_verificacion) as ultimo_ping FROM servidores");
$ultimo_ping = $queryPing ? ($queryPing->fetchColumn() ?: 'Sin registros') : 'Error BD';

$servidores = $pdo->query("SELECT id, nombre, ip, estado FROM servidores")->fetchAll(PDO::FETCH_ASSOC);

// Agregar los nuevos datos al JSON de respuesta
echo json_encode([
    'temperatura' => $temp_actual,
    'humedad' => $humedad_actual,
    'estado' => $estado_clima,
    'color' => $color_clima,
    'servidores' => $servidores,
    'ultima_temp' => $ultima_temp, // Nuevo dato
    'ultimo_ping' => $ultimo_ping  // Nuevo dato
]);




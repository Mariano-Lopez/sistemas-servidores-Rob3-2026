<?php
session_start();
// Si ya inició sesión, lo mandamos al dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_POST) {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    // Credenciales de prueba temporales (luego las pasamos a la base de datos)
    if ($usuario === 'admin' && $password === '1234') {
        $_SESSION['user'] = $usuario;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Control de Servidores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow p-4" style="width: 350px;">
        <h3 class="text-center mb-3">Acceso al Sistema</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger p-2"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Usuario</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>
</body>
</html>
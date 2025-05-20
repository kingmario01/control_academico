<?php
session_start();
require_once '../config/database.php';

$mensaje = "";

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        // Redirigir según rol
        if ($usuario['rol'] === 'administrador') {
            header("Location: admin_panel.php");
        } elseif ($usuario['rol'] === 'docente') {
            header("Location: docente_panel.php");
        } else {
            $mensaje = "<div class='alert alert-danger'>Rol desconocido.</div>";
        }
        exit;
    } else {
        $mensaje = "<div class='alert alert-danger'>Correo o contraseña incorrectos.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px;">
    <div class="text-center">
        <img src="img/escudo.jpeg" alt="Escudo IE Mariscal Sucre" style="width: 150px; margin-bottom: 20px;">
        <h3 class="mb-3">🔐 Iniciar Sesión</h3>
    </div>

    <?= $mensaje ?>

    <form method="POST" class="card card-body shadow-sm border">
        <div class="mb-3">
            <label>Correo:</label>
            <input type="email" name="correo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Contraseña:</label>
            <input type="password" name="contraseña" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
    </form>
</div>
</body>
</html>

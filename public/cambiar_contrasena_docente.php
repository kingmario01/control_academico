<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../config/database.php';


if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'docente') {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['actual'] ?? '';
    $nueva = $_POST['nueva'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    // Obtener contraseña actual
    $stmt = $pdo->prepare("SELECT contraseña FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($actual, $usuario['contraseña'])) {
        $mensaje = "<div class='alert alert-danger'>❌ Contraseña actual incorrecta.</div>";
    } elseif ($nueva !== $confirmar) {
        $mensaje = "<div class='alert alert-warning'>⚠️ La nueva contraseña no coincide.</div>";
    } else {
        $nueva_hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
        $stmt->execute([$nueva_hash, $usuario_id]);
        $mensaje = "<div class='alert alert-success'>✅ Contraseña actualizada correctamente.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <h4 class="mb-4">🔒 Cambiar Contraseña</h4>

    <?= $mensaje ?>

    <form method="POST" class="card card-body">
        <div class="mb-3">
            <label>Contraseña actual:</label>
            <input type="password" name="actual" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Nueva contraseña:</label>
            <input type="password" name="nueva" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Confirmar nueva contraseña:</label>
            <input type="password" name="confirmar" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>

    <a href="docente_panel.php" class="btn btn-secondary mt-4">⬅️ Volver al Panel</a>
</div>
</body>
</html>

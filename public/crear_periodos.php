<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

// Crear periodo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_periodo'])) {
    $nombre = $_POST['nombre'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    $stmt = $pdo->prepare("INSERT INTO periodos (nombre, fecha_inicio, fecha_fin) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $fecha_inicio, $fecha_fin]);
    header("Location: crear_periodos.php");
    exit;
}

// Eliminar periodo
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM periodos WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
    header("Location: crear_periodos.php");
    exit;
}

// Consultar periodos existentes
$periodos = $pdo->query("SELECT * FROM periodos ORDER BY fecha_inicio DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Periodos Académicos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">🗓 Crear Periodos Académicos</h2>

    <!-- Formulario crear periodo -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="nombre" class="form-control" placeholder="Ej: Primer Trimestre" required>
        </div>
        <div class="col-md-3">
            <input type="date" name="fecha_inicio" class="form-control" required>
        </div>
        <div class="col-md-3">
            <input type="date" name="fecha_fin" class="form-control" required>
        </div>
        <div class="col-md-2">
            <button type="submit" name="crear_periodo" class="btn btn-primary w-100">Crear</button>
        </div>
    </form>

    <!-- Tabla de periodos -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($periodos as $periodo): ?>
            <tr>
                <td><?= htmlspecialchars($periodo['nombre']) ?></td>
                <td><?= htmlspecialchars($periodo['fecha_inicio']) ?></td>
                <td><?= htmlspecialchars($periodo['fecha_fin']) ?></td>
                <td>
                    <a href="?eliminar=<?= $periodo['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('¿Eliminar este periodo?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin_panel.php" class="btn btn-secondary mt-4">⬅️ Volver al Panel</a>
</div>
</body>
</html>

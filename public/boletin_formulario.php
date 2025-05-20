<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'], ['administrador', 'docente'])) {
    header("Location: login.php");
    exit;
}

$estudiantes = $pdo->query("SELECT e.id, e.nombre, g.nombre AS grado FROM estudiantes e JOIN grados g ON e.grado_id = g.id ORDER BY e.nombre")->fetchAll();
$periodos = $pdo->query("SELECT * FROM periodos ORDER BY fecha_inicio DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletín por Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3>📄 Generar Boletín Individual</h3>

    <form method="POST" action="generar_reporte_pdf.php" target="_blank" class="row g-3">
        <div class="col-md-6">
            <label>Estudiante:</label>
            <select name="estudiante_id" class="form-select" required>
                <option value="">Seleccione</option>
                <?php foreach ($estudiantes as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?> (<?= $e['grado'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Periodo Académico:</label>
            <select name="periodo_id" class="form-select" required>
                <option value="todos">📘 Todos los periodos</option>
                <?php foreach ($periodos as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-primary w-100">📥 Generar PDF</button>
        </div>
    </form>

    <a href="admin_panel.php" class="btn btn-secondary mt-4">⬅️ Volver al Panel</a>
</div>
</body>
</html>

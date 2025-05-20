<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'docente') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener ID del docente
$stmt = $pdo->prepare("SELECT id FROM docentes WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$docente_id = $stmt->fetchColumn();

// Obtener materias asignadas
$stmt = $pdo->prepare("
    SELECT md.id, g.id AS grado_id, g.nombre AS grado, a.id AS asignatura_id, a.nombre AS asignatura
    FROM materias_docente md
    JOIN grados g ON md.grado_id = g.id
    JOIN asignaturas a ON md.asignatura_id = a.id
    WHERE md.docente_id = ?
    ORDER BY g.nombre, a.nombre
");
$stmt->execute([$docente_id]);
$materias = $stmt->fetchAll();

$periodos = $pdo->query("SELECT * FROM periodos ORDER BY fecha_inicio DESC")->fetchAll();
$reporte = [];
$mensaje = "";

// Procesar filtro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grado_id = $_POST['grado_id'];
    $asignatura_id = $_POST['asignatura_id'];
    $periodo_id = $_POST['periodo_id'];

    $stmt = $pdo->prepare("
        SELECT e.nombre,
               n.nota1, n.nota2, n.nota3, n.nota4, n.nota5, n.promedio, n.estado,
               (SELECT COUNT(*) FROM asistencias WHERE estudiante_id = e.id AND asignatura_id = ? AND estado = 'asistió') AS asistencias,
               (SELECT COUNT(*) FROM asistencias WHERE estudiante_id = e.id AND asignatura_id = ? AND estado = 'falta') AS faltas,
               (SELECT COUNT(*) FROM asistencias WHERE estudiante_id = e.id AND asignatura_id = ? AND estado = 'excusa') AS excusas
        FROM estudiantes e
        LEFT JOIN notas n ON n.estudiante_id = e.id 
            AND n.asignatura_id = ? 
            AND n.periodo_id = ?
        WHERE e.grado_id = ?
        ORDER BY e.nombre
    ");
    $stmt->execute([
        $asignatura_id, $asignatura_id, $asignatura_id,
        $asignatura_id, $periodo_id, $grado_id
    ]);
    $reporte = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte del Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3>📊 Reporte Académico - Docente</h3>

    <!-- Formulario -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <label>Asignatura / Grado:</label>
            <select name="asignatura_id" class="form-select" required>
                <option value="">Seleccione</option>
                <?php foreach ($materias as $m): ?>
                    <option value="<?= $m['asignatura_id'] ?>"
                        <?= isset($_POST['asignatura_id']) && $_POST['asignatura_id'] == $m['asignatura_id'] ? 'selected' : '' ?>>
                        <?= $m['grado'] ?> - <?= $m['asignatura'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Grado:</label>
            <select name="grado_id" class="form-select" required>
                <option value="">Seleccione</option>
                <?php foreach ($materias as $m): ?>
                    <option value="<?= $m['grado_id'] ?>"
                        <?= isset($_POST['grado_id']) && $_POST['grado_id'] == $m['grado_id'] ? 'selected' : '' ?>>
                        <?= $m['grado'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Periodo:</label>
            <select name="periodo_id" class="form-select" required>
                <option value="">Seleccione</option>
                <?php foreach ($periodos as $p): ?>
                    <option value="<?= $p['id'] ?>"
                        <?= isset($_POST['periodo_id']) && $_POST['periodo_id'] == $p['id'] ? 'selected' : '' ?>>
                        <?= $p['nombre'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 align-self-end">
            <button class="btn btn-primary w-100">Consultar</button>
        </div>
    </form>

    <?php if (!empty($reporte)): ?>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th>N1</th><th>N2</th><th>N3</th><th>N4</th><th>N5</th>
                    <th>Promedio</th><th>Estado</th>
                    <th>Asistió</th><th>Faltó</th><th>Excusa</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($reporte as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nombre']) ?></td>
                    <td><?= $r['nota1'] ?? '-' ?></td>
                    <td><?= $r['nota2'] ?? '-' ?></td>
                    <td><?= $r['nota3'] ?? '-' ?></td>
                    <td><?= $r['nota4'] ?? '-' ?></td>
                    <td><?= $r['nota5'] ?? '-' ?></td>
                    <td><?= $r['promedio'] ?? '-' ?></td>
                    <td class="<?= ($r['estado'] ?? '') === 'reprobado' ? 'text-danger' : 'text-success' ?>">
                        <?= $r['estado'] ?? '-' ?>
                    </td>
                    <td><?= $r['asistencias'] ?? 0 ?></td>
                    <td><?= $r['faltas'] ?? 0 ?></td>
                    <td><?= $r['excusas'] ?? 0 ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="alert alert-warning">No hay registros para esta combinación.</div>
    <?php endif; ?>

    <a href="docente_panel.php" class="btn btn-secondary mt-4">⬅️ Volver al Panel</a>
</div>
</body>
</html>

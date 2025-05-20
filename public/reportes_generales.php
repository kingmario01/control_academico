<?php/*
 * Sistema de Registro Académico
 * © 2025 [Mario Alvarez Ramos]
 * Licencia: Propietaria. Uso no autorizado prohibido.
 * Contacto: alvarezramosmario37@gmail.com
 */

session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

// Listas para filtros
$grados = $pdo->query("SELECT * FROM grados ORDER BY nombre")->fetchAll();
$asignaturas = $pdo->query("SELECT * FROM asignaturas ORDER BY nombre")->fetchAll();
$periodos = $pdo->query("SELECT * FROM periodos ORDER BY fecha_inicio DESC")->fetchAll();

$reporte = [];
$filtros = [
    'grado_id' => '',
    'asignatura_id' => '',
    'periodo_id' => ''
];

// Procesar consulta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grado_id'], $_POST['asignatura_id'], $_POST['periodo_id'])) {
    $filtros['grado_id'] = $_POST['grado_id'];
    $filtros['asignatura_id'] = $_POST['asignatura_id'];
    $filtros['periodo_id'] = $_POST['periodo_id'];

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
        $filtros['asignatura_id'],
        $filtros['asignatura_id'],
        $filtros['asignatura_id'],
        $filtros['asignatura_id'],
        $filtros['periodo_id'],
        $filtros['grado_id']
    ]);

    $reporte = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes Generales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">📊 Reporte General de Notas y Asistencias</h2>

    <!-- Formulario de filtros -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <select name="grado_id" class="form-select" required>
                <option value="">Seleccione Grado</option>
                <?php foreach ($grados as $grado): ?>
                    <option value="<?= $grado['id'] ?>" <?= ($filtros['grado_id'] == $grado['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($grado['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select name="asignatura_id" class="form-select" required>
                <option value="">Seleccione Asignatura</option>
                <?php foreach ($asignaturas as $asig): ?>
                    <option value="<?= $asig['id'] ?>" <?= ($filtros['asignatura_id'] == $asig['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($asig['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="periodo_id" class="form-select" required>
                <option value="">Seleccione Periodo</option>
                <?php foreach ($periodos as $periodo): ?>
                    <option value="<?= $periodo['id'] ?>" <?= ($filtros['periodo_id'] == $periodo['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($periodo['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Consultar</button>
        </div>
    </form>

    <?php if (!empty($reporte)): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th>N1</th><th>N2</th><th>N3</th><th>N4</th><th>N5</th>
                    <th>Promedio</th>
                    <th>Estado</th>
                    <th>Asistencias</th>
                    <th>Faltas</th>
                    <th>Excusas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte as $fila): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['nombre']) ?></td>
                        <td><?= $fila['nota1'] ?? '-' ?></td>
                        <td><?= $fila['nota2'] ?? '-' ?></td>
                        <td><?= $fila['nota3'] ?? '-' ?></td>
                        <td><?= $fila['nota4'] ?? '-' ?></td>
                        <td><?= $fila['nota5'] ?? '-' ?></td>
                        <td><strong><?= $fila['promedio'] ?? '-' ?></strong></td>
                        <td class="<?= $fila['estado'] === 'reprobado' ? 'text-danger' : 'text-success' ?>">
                            <?= $fila['estado'] ?? '-' ?>
                        </td>
                        <td><?= $fila['asistencias'] ?? 0 ?></td>
                        <td><?= $fila['faltas'] ?? 0 ?></td>
                        <td><?= $fila['excusas'] ?? 0 ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p class="alert alert-warning">⚠️ No se encontraron resultados para los filtros seleccionados.</p>
    <?php endif; ?>

    <a href="admin_panel.php" class="btn btn-secondary mt-4">⬅️ Volver al Panel</a>
</div>
</body>
</html>
<?php
// Cerrar conexión
$pdo = null;
// Fin del archivo

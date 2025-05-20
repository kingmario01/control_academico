<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

// Asignar materia al docente
if (isset($_POST['asignar'])) {
    $docente_id = $_POST['docente_id'];
    $asignatura_id = $_POST['asignatura_id'];
    $grado_id = $_POST['grado_id'];

    $stmt = $pdo->prepare("INSERT INTO materias_docente (docente_id, asignatura_id, grado_id) VALUES (?, ?, ?)");
    $stmt->execute([$docente_id, $asignatura_id, $grado_id]);
    header("Location: asignar_materias.php");
    exit;
}

// Eliminar asignación
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM materias_docente WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
    header("Location: asignar_materias.php");
    exit;
}

// Obtener docentes
$docentes = $pdo->query("
    SELECT d.id, u.nombre
    FROM docentes d
    JOIN usuarios u ON d.usuario_id = u.id
    ORDER BY u.nombre
")->fetchAll();

// Obtener asignaturas
$asignaturas = $pdo->query("SELECT * FROM asignaturas ORDER BY nombre")->fetchAll();

// Obtener grados
$grados = $pdo->query("SELECT * FROM grados ORDER BY nombre")->fetchAll();

// Obtener asignaciones actuales
$asignaciones = $pdo->query("
    SELECT md.id, u.nombre AS docente, a.nombre AS asignatura, g.nombre AS grado
    FROM materias_docente md
    JOIN docentes d ON md.docente_id = d.id
    JOIN usuarios u ON d.usuario_id = u.id
    JOIN asignaturas a ON md.asignatura_id = a.id
    JOIN grados g ON md.grado_id = g.id
    ORDER BY u.nombre, g.nombre
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Materias a Docentes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Asignar Materias a Docentes</h2>

    <!-- Formulario de asignación -->
    <form method="POST" class="row mb-4">
        <div class="col-md-3">
            <select name="docente_id" class="form-select" required>
                <option value="">Seleccione Docente</option>
                <?php foreach ($docentes as $docente): ?>
                    <option value="<?= $docente['id'] ?>"><?= htmlspecialchars($docente['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select name="asignatura_id" class="form-select" required>
                <option value="">Seleccione Asignatura</option>
                <?php foreach ($asignaturas as $asignatura): ?>
                    <option value="<?= $asignatura['id'] ?>"><?= htmlspecialchars($asignatura['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="grado_id" class="form-select" required>
                <option value="">Seleccione Grado</option>
                <?php foreach ($grados as $grado): ?>
                    <option value="<?= $grado['id'] ?>"><?= htmlspecialchars($grado['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" name="asignar" class="btn btn-success w-100">Asignar</button>
        </div>
    </form>

    <!-- Lista de asignaciones -->
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Docente</th>
            <th>Asignatura</th>
            <th>Grado</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($asignaciones as $fila): ?>
            <tr>
                <td><?= htmlspecialchars($fila['docente']) ?></td>
                <td><?= htmlspecialchars($fila['asignatura']) ?></td>
                <td><?= htmlspecialchars($fila['grado']) ?></td>
                <td>
                    <a href="?eliminar=<?= $fila['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('¿Eliminar esta asignación?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin_panel.php" class="btn btn-secondary mt-3">⬅️ Volver al Panel</a>
</div>
</body>
</html>
<?php
// Fin del archivo
// Cerrar la conexión a la base de datos
$pdo = null;
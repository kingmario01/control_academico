<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'docente') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

$docente_id = $pdo->prepare("SELECT id FROM docentes WHERE usuario_id = ?");
$docente_id->execute([$_SESSION['usuario_id']]);
$docente_id = $docente_id->fetchColumn();

$grado_id = $_GET['grado'] ?? null;
$asignatura_id = $_GET['asignatura'] ?? null;

if (!$grado_id || !$asignatura_id) {
    echo "Faltan parámetros de grado o asignatura.";
    exit;
}

// Obtener nombre del grado y asignatura
$grado = $pdo->query("SELECT nombre FROM grados WHERE id = $grado_id")->fetchColumn();
$asignatura = $pdo->query("SELECT nombre FROM asignaturas WHERE id = $asignatura_id")->fetchColumn();

// Guardar asistencias
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fecha'])) {
    $fecha = $_POST['fecha'];
    $asistencias = $_POST['asistencia'];

    foreach ($asistencias as $estudiante_id => $estado) {
        // Verificar si ya existe para esa fecha
        $stmt = $pdo->prepare("SELECT id FROM asistencias WHERE estudiante_id = ? AND asignatura_id = ? AND fecha = ?");
        $stmt->execute([$estudiante_id, $asignatura_id, $fecha]);
        $existe = $stmt->fetchColumn();

        if ($existe) {
            // Actualizar
            $stmt = $pdo->prepare("UPDATE asistencias SET estado = ? WHERE id = ?");
            $stmt->execute([$estado, $existe]);
        } else {
            // Insertar
            $stmt = $pdo->prepare("INSERT INTO asistencias (estudiante_id, fecha, estado, asignatura_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$estudiante_id, $fecha, $estado, $asignatura_id]);
        }
    }

    echo "<div class='alert alert-success'>✅ Asistencias guardadas correctamente.</div>";
}

// Obtener estudiantes del grado
$estudiantes = $pdo->prepare("SELECT id, nombre FROM estudiantes WHERE grado_id = ? ORDER BY nombre");
$estudiantes->execute([$grado_id]);
$estudiantes = $estudiantes->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Asistencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3>Registro de Asistencias</h3>
    <p><strong>Grado:</strong> <?= htmlspecialchars($grado) ?> | <strong>Asignatura:</strong> <?= htmlspecialchars($asignatura) ?></p>

    <form method="POST">
        <div class="mb-3">
            <label for="fecha" class="form-label">Fecha de asistencia:</label>
            <input type="date" name="fecha" id="fecha" class="form-control" required>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Asistencia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estudiantes as $est): ?>
                    <tr>
                        <td><?= htmlspecialchars($est['nombre']) ?></td>
                        <td>
                            <select name="asistencia[<?= $est['id'] ?>]" class="form-select" required>
                                <option value="asistió">Asistió</option>
                                <option value="falta">Falta</option>
                                <option value="excusa">Excusa</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Guardar Asistencias</button>
    </form>

    <a href="docente_panel.php" class="btn btn-secondary mt-4">⬅️ Volver al Panel</a>
</div>
</body>
</html>
<?php
// Cerrar conexión
$pdo = null;
// Fin del archivo
<?php/*
 * Sistema de Registro Académico
 * © 2025 [Mario Alvarez Ramos]
 * Licencia: Propietaria. Uso no autorizado prohibido.
 * Contacto: alvarezramosmario37@gmail.com
 */

session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'docente') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

$usuario_id = $_SESSION['usuario_id'];
$nombre_docente = $_SESSION['nombre'];

$stmt = $pdo->prepare("SELECT id FROM docentes WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$docente_id = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT md.id, g.id AS grado_id, g.nombre AS grado, a.id AS asignatura_id, a.nombre AS asignatura
    FROM materias_docente md
    JOIN grados g ON md.grado_id = g.id
    JOIN asignaturas a ON md.asignatura_id = a.id
    WHERE md.docente_id = ?
    ORDER BY g.nombre, a.nombre
");
$stmt->execute([$docente_id]);
$asignaciones = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>👨‍🏫 Bienvenido, <?= htmlspecialchars($nombre_docente) ?></h2>

    <?php if (empty($asignaciones)): ?>
        <div class="alert alert-warning mt-4">No tienes asignaturas asignadas aún.</div>
    <?php else: ?>
        <h4 class="mt-4">📚 Tus asignaturas:</h4>

        <?php foreach ($asignaciones as $asig): ?>
            <div class="card my-3">
                <div class="card-header">
                    <strong><?= htmlspecialchars($asig['grado']) ?> - <?= htmlspecialchars($asig['asignatura']) ?></strong>
                </div>
                <div class="card-body">
                    <a href="registro_notas.php?grado=<?= $asig['grado_id'] ?>&asignatura=<?= $asig['asignatura_id'] ?>" class="btn btn-success me-2">✏️ Registrar Notas</a>
                    <a href="registro_asistencias.php?grado=<?= $asig['grado_id'] ?>&asignatura=<?= $asig['asignatura_id'] ?>" class="btn btn-primary me-2">📅 Registrar Asistencias</a>
                    <a href="reportes_docente.php?grado=<?= $asig['grado_id'] ?>&asignatura=<?= $asig['asignatura_id'] ?>" class="btn btn-info me-2">📊 Ver Reporte</a>
                    <a href="boletin_formulario.php" class="btn btn-outline-secondary">📄 Ver Boletín</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    
            
    <a href="logout.php" class="btn btn-danger mt-4">Cerrar Sesión</a>
    <a href="cambiar_contrasena_docente.php" class="btn btn-outline-warning mt-3">
    🔒 Cambiar Contraseña
</a>

</div>
</body>
</html>

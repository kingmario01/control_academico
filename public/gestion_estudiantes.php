<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
$mensaje = "";

// Listas para formularios
$grados = $pdo->query("SELECT * FROM grados ORDER BY nombre")->fetchAll();
$niveles = $pdo->query("SELECT * FROM niveles ORDER BY nombre")->fetchAll();

// Crear estudiante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $nombre = $_POST['nombre'];
    $documento = $_POST['documento'];
    $grado_id = $_POST['grado_id'];
    $nivel_id = $_POST['nivel_id'];

    // Verificar documento duplicado
    $stmt = $pdo->prepare("SELECT id FROM estudiantes WHERE documento_identidad = ?");
    $stmt->execute([$documento]);
    if ($stmt->rowCount() > 0) {
        $mensaje = "<div class='alert alert-warning'>⚠️ Ya existe un estudiante con ese documento.</div>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO estudiantes (nombre, documento_identidad, grado_id, nivel_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $documento, $grado_id, $nivel_id]);
        $mensaje = "<div class='alert alert-success'>✅ Estudiante creado correctamente.</div>";
    }
}

// Editar estudiante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $documento = $_POST['documento'];
    $grado_id = $_POST['grado_id'];
    $nivel_id = $_POST['nivel_id'];

    // Verificar si el documento ya está en uso por otro
    $stmt = $pdo->prepare("SELECT id FROM estudiantes WHERE documento_identidad = ? AND id != ?");
    $stmt->execute([$documento, $id]);

    if ($stmt->rowCount() > 0) {
        $mensaje = "<div class='alert alert-warning'>⚠️ Otro estudiante ya usa ese documento.</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE estudiantes SET nombre = ?, documento_identidad = ?, grado_id = ?, nivel_id = ? WHERE id = ?");
        $stmt->execute([$nombre, $documento, $grado_id, $nivel_id, $id]);
        $mensaje = "<div class='alert alert-success'>✅ Estudiante actualizado correctamente.</div>";
    }
}

// Eliminar estudiante
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM estudiantes WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
    header("Location: gestion_estudiantes.php");
    exit;
}

// Cargar datos para editar
$modo_edicion = false;
if (isset($_GET['editar'])) {
    $modo_edicion = true;
    $id_editar = $_GET['editar'];

    $stmt = $pdo->prepare("SELECT * FROM estudiantes WHERE id = ?");
    $stmt->execute([$id_editar]);
    $estudiante_edit = $stmt->fetch();
}

// Obtener estudiantes
$stmt = $pdo->query("
    SELECT e.id, e.nombre, e.documento_identidad, g.nombre AS grado, n.nombre AS nivel
    FROM estudiantes e
    LEFT JOIN grados g ON e.grado_id = g.id
    LEFT JOIN niveles n ON e.nivel_id = n.id
    ORDER BY e.nombre
");
$estudiantes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Gestión de Estudiantes</h2>

    <?= $mensaje ?>

    <!-- Formulario crear o editar -->
    <form method="POST" class="card card-body mb-4">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre completo"
                       value="<?= $modo_edicion ? htmlspecialchars($estudiante_edit['nombre']) : '' ?>" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="documento" class="form-control" placeholder="Documento"
                       value="<?= $modo_edicion ? htmlspecialchars($estudiante_edit['documento_identidad']) : '' ?>" required>
            </div>
            <div class="col-md-2">
                <select name="grado_id" class="form-select" required>
                    <option value="">Grado</option>
                    <?php foreach ($grados as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $modo_edicion && $g['id'] == $estudiante_edit['grado_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="nivel_id" class="form-select" required>
                    <option value="">Nivel</option>
                    <?php foreach ($niveles as $n): ?>
                        <option value="<?= $n['id'] ?>" <?= $modo_edicion && $n['id'] == $estudiante_edit['nivel_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($n['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <?php if ($modo_edicion): ?>
                    <input type="hidden" name="id" value="<?= $estudiante_edit['id'] ?>">
                    <button type="submit" name="actualizar" class="btn btn-primary w-100">Editar</button>
                <?php else: ?>
                    <button type="submit" name="crear" class="btn btn-success w-100">Crear</button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Lista de estudiantes -->
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Grado</th>
            <th>Nivel</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($estudiantes as $est): ?>
            <tr>
                <td><?= htmlspecialchars($est['nombre']) ?></td>
                <td><?= htmlspecialchars($est['documento_identidad']) ?></td>
                <td><?= htmlspecialchars($est['grado']) ?></td>
                <td><?= htmlspecialchars($est['nivel']) ?></td>
                <td>
                    <a href="?editar=<?= $est['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="?eliminar=<?= $est['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('¿Eliminar este estudiante?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin_panel.php" class="btn btn-secondary mt-4">⬅️ Volver al Panel</a>
</div>
</body>
</html>

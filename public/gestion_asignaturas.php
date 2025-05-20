<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

// Crear asignatura
if (isset($_POST['crear_asignatura'])) {
    $nombre = $_POST['nombre_asignatura'];
    $nivel_id = $_POST['nivel_id'];
    $stmt = $pdo->prepare("INSERT INTO asignaturas (nombre, nivel_id) VALUES (?, ?)");
    $stmt->execute([$nombre, $nivel_id]);
    header("Location: gestion_asignaturas.php");
    exit;
}

// Eliminar asignatura
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM asignaturas WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
    header("Location: gestion_asignaturas.php");
    exit;
}

// Obtener niveles para el combo
$niveles = $pdo->query("SELECT * FROM niveles ORDER BY nombre")->fetchAll();

// Obtener asignaturas existentes con su nivel
$stmt = $pdo->query("
    SELECT a.id, a.nombre AS asignatura, n.nombre AS nivel
    FROM asignaturas a
    JOIN niveles n ON a.nivel_id = n.id
    ORDER BY n.nombre, a.nombre
");
$asignaturas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Asignaturas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Gestión de Asignaturas</h2>

        <!-- Formulario crear asignatura -->
        <form method="POST" class="row mb-4">
            <div class="col-md-4">
                <input type="text" name="nombre_asignatura" class="form-control" placeholder="Nombre de la asignatura" required>
            </div>
            <div class="col-md-4">
                <select name="nivel_id" class="form-select" required>
                    <option value="">Seleccione nivel</option>
                    <?php foreach ($niveles as $nivel): ?>
                        <option value="<?= $nivel['id'] ?>"><?= htmlspecialchars($nivel['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" name="crear_asignatura" class="btn btn-primary w-100">Crear Asignatura</button>
            </div>
        </form>

        <!-- Tabla de asignaturas -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Nivel</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asignaturas as $asignatura): ?>
                    <tr>
                        <td><?= htmlspecialchars($asignatura['asignatura']) ?></td>
                        <td><?= htmlspecialchars($asignatura['nivel']) ?></td>
                        <td>
                            <a href="?eliminar=<?= $asignatura['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Eliminar esta asignatura?')">Eliminar</a>
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
// Fin del script
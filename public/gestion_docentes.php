<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
$mensaje = "";

// Crear docente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contraseña_plana = $_POST['contraseña'];
    $especialidad = $_POST['especialidad'];

    // Verificar si ya existe el correo
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);

    if ($stmt->rowCount() > 0) {
        $mensaje = "<div class='alert alert-warning'>⚠️ Ya existe un usuario con ese correo.</div>";
    } else {
        $contraseña = password_hash($contraseña_plana, PASSWORD_DEFAULT);

        // Insertar en usuarios
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES (?, ?, ?, 'docente')");
        $stmt->execute([$nombre, $correo, $contraseña]);
        $usuario_id = $pdo->lastInsertId();

        // Insertar en docentes
        $stmt = $pdo->prepare("INSERT INTO docentes (usuario_id, especialidad) VALUES (?, ?)");
        $stmt->execute([$usuario_id, $especialidad]);

        $mensaje = "<div class='alert alert-success'>✅ Docente creado correctamente.</div>";
    }
}

// Eliminar docente
if (isset($_GET['eliminar'])) {
    $usuario_id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    header("Location: gestion_docentes.php");
    exit;
}

// Obtener todos los docentes
$stmt = $pdo->query("
    SELECT u.id, u.nombre, u.correo, d.especialidad
    FROM usuarios u
    JOIN docentes d ON u.id = d.usuario_id
    ORDER BY u.nombre
");
$docentes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Docentes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Gestión de Docentes</h2>

    <?= $mensaje ?>

    <!-- Formulario para crear docente -->
    <form method="POST" class="card card-body mb-4">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre completo" required>
            </div>
            <div class="col-md-3">
                <input type="email" name="correo" class="form-control" placeholder="Correo electrónico" required>
            </div>
            <div class="col-md-2">
                <input type="password" name="contraseña" class="form-control" placeholder="Contraseña" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="especialidad" class="form-control" placeholder="Especialidad" required>
            </div>
            <div class="col-md-1">
                <button type="submit" name="crear" class="btn btn-success w-100">Crear</button>
            </div>
        </div>
    </form>

    <!-- Lista de docentes -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Especialidad</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($docentes as $docente): ?>
                <tr>
                    <td><?= htmlspecialchars($docente['nombre']) ?></td>
                    <td><?= htmlspecialchars($docente['correo']) ?></td>
                    <td><?= htmlspecialchars($docente['especialidad']) ?></td>
                    <td>
                        <a href="?eliminar=<?= $docente['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('¿Eliminar este docente?')">Eliminar</a>
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
// Cerrar la conexión a la base de datos        
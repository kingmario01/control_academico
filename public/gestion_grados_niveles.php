<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

// Crear nivel
if (isset($_POST['crear_nivel'])) {
    $nombre = $_POST['nombre_nivel'];
    $stmt = $pdo->prepare("INSERT INTO niveles (nombre) VALUES (?)");
    $stmt->execute([$nombre]);
    header("Location: gestion_grados_niveles.php");
    exit;
}

// Crear grado
if (isset($_POST['crear_grado'])) {
    $nombre = $_POST['nombre_grado'];
    $stmt = $pdo->prepare("INSERT INTO grados (nombre) VALUES (?)");
    $stmt->execute([$nombre]);
    header("Location: gestion_grados_niveles.php");
    exit;
}

// Eliminar nivel
if (isset($_GET['eliminar_nivel'])) {
    $stmt = $pdo->prepare("DELETE FROM niveles WHERE id = ?");
    $stmt->execute([$_GET['eliminar_nivel']]);
    header("Location: gestion_grados_niveles.php");
    exit;
}

// Eliminar grado
if (isset($_GET['eliminar_grado'])) {
    $stmt = $pdo->prepare("DELETE FROM grados WHERE id = ?");
    $stmt->execute([$_GET['eliminar_grado']]);
    header("Location: gestion_grados_niveles.php");
    exit;
}

// Consultar niveles y grados existentes
$niveles = $pdo->query("SELECT * FROM niveles ORDER BY nombre")->fetchAll();
$grados = $pdo->query("SELECT * FROM grados ORDER BY nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Grados y Niveles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Gestión de Grados y Niveles</h2>

        <div class="row mb-4">
            <!-- Formulario de niveles -->
            <div class="col-md-6">
                <h4>➕ Crear Nivel</h4>
                <form method="POST">
                    <div class="input-group">
                        <input type="text" name="nombre_nivel" class="form-control" placeholder="Ej: Primaria" required>
                        <button type="submit" name="crear_nivel" class="btn btn-primary">Crear</button>
                    </div>
                </form>

                <table class="table table-bordered table-sm mt-3">
                    <thead>
                        <tr><th>Niveles</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($niveles as $nivel): ?>
                        <tr>
                            <td><?= htmlspecialchars($nivel['nombre']) ?></td>
                            <td>
                                <a href="?eliminar_nivel=<?= $nivel['id'] ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('¿Eliminar este nivel?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Formulario de grados -->
            <div class="col-md-6">
                <h4>➕ Crear Grado</h4>
                <form method="POST">
                    <div class="input-group">
                        <input type="text" name="nombre_grado" class="form-control" placeholder="Ej: 1° Grado" required>
                        <button type="submit" name="crear_grado" class="btn btn-primary">Crear</button>
                    </div>
                </form>

                <table class="table table-bordered table-sm mt-3">
                    <thead>
                        <tr><th>Grados</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grados as $grado): ?>
                        <tr>
                            <td><?= htmlspecialchars($grado['nombre']) ?></td>
                            <td>
                                <a href="?eliminar_grado=<?= $grado['id'] ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('¿Eliminar este grado?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="admin_panel.php" class="btn btn-secondary">⬅️ Volver al Panel</a>
    </div>
</body>
</html>
<?php 
// Fin del archivo
// gestion_grado_niveles.php
// Este archivo permite gestionar los grados y niveles de la aplicación.
// Se pueden crear nuevos grados y niveles, así como eliminarlos.
// Se utiliza Bootstrap para el diseño y PDO para la conexión a la base de datos.
// Se asume que la base de datos y las tablas necesarias ya están creadas. 
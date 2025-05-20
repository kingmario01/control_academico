<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

$nombre = $_SESSION['nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Panel Administrativo - Bienvenido, <?php echo htmlspecialchars($nombre); ?></h2>

        <div class="list-group">
            <a href="gestion_docentes.php" class="list-group-item list-group-item-action">📋 Gestión/Crear Docentes</a>
            <a href="gestion_estudiantes.php" class="list-group-item list-group-item-action">🎓 Gestión de Estudiantes</a>
            <a href="gestion_grados_niveles.php" class="list-group-item list-group-item-action">🏫 Gestión de Grados y Niveles</a>
            <a href="gestion_asignaturas.php" class="list-group-item list-group-item-action">📚 Gestión de Asignaturas</a>
            <a href="asignar_materias.php" class="list-group-item list-group-item-action">👨‍🏫 Asignar Materias a Docentes</a>
            <a href="crear_periodos.php" class="list-group-item list-group-item-action">🗓️ Crear Periodos Académicos</a>
            <a href="reportes_generales.php" class="list-group-item list-group-item-action">📈 Reportes Generales (Notas y Asistencias)</a>
            <a href="boletin_formulario.php" class="list-group-item list-group-item-action">|📄 Generar Boletín por Estudiante
</a>

        </div>

        <div class="mt-4">
           
           
            <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
            <a href="contactame.php" class="btn btn-danger">CONTACTAME</a>
            
        </div>
    </div>
</body>
</html>
<?php
// Fin del archivo
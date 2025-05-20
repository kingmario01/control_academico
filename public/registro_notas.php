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

// Obtener ID del docente
$stmt = $pdo->prepare("SELECT id FROM docentes WHERE usuario_id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$docente_id = $stmt->fetchColumn();

$grado_id = $_GET['grado'] ?? null;
$asignatura_id = $_GET['asignatura'] ?? null;

if (!$grado_id || !$asignatura_id) {
    echo "Faltan parámetros de grado o asignatura.";
    exit;
}

// Obtener nombre del grado y asignatura
$grado = $pdo->query("SELECT nombre FROM grados WHERE id = $grado_id")->fetchColumn();
$asignatura = $pdo->query("SELECT nombre FROM asignaturas WHERE id = $asignatura_id")->fetchColumn();

// Obtener periodos académicos
$periodos = $pdo->query("SELECT * FROM periodos ORDER BY fecha_inicio DESC")->fetchAll();

// Guardar notas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['periodo_id'])) {
    $periodo_id = $_POST['periodo_id'];
    foreach ($_POST['notas'] as $estudiante_id => $notas) {
        // Validar valores
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($notas["nota$i"])) $notas["nota$i"] = 0;
        }

        // Verificar si ya existe
        $stmt = $pdo->prepare("SELECT id FROM notas WHERE estudiante_id = ? AND asignatura_id = ? AND periodo_id = ?");
        $stmt->execute([$estudiante_id, $asignatura_id, $periodo_id]);
        $existe = $stmt->fetchColumn();

        if ($existe) {
            // Actualizar
            $stmt = $pdo->prepare("
                UPDATE notas 
                SET nota1 = ?, nota2 = ?, nota3 = ?, nota4 = ?, nota5 = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $notas['nota1'], $notas['nota2'], $notas['nota3'], $notas['nota4'], $notas['nota5'], $existe
            ]);
        } else {
            // Insertar
            $stmt = $pdo->prepare("
                INSERT INTO notas 
                (estudiante_id, asignatura_id, periodo_id, nota1, nota2, nota3, nota4, nota5)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $estudiante_id, $asignatura_id, $periodo_id,
                $notas['nota1'], $notas['nota2'], $notas['nota3'], $notas['nota4'], $notas['nota5']
            ]);
        }
    }

    echo "<div class='alert alert-success'>✅ Notas guardadas correctamente.</div>";
}

// Obtener estudiantes
$estudiantes = $pdo->prepare("SELECT id, nombre FROM estudiantes WHERE grado_id = ? ORDER BY nombre");
$estudiantes->execute([$grado_id]);
$estudiantes = $estudiantes->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Notas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3>✏️ Registro de Notas</h3>
    <p><strong>Grado:</strong> <?= htmlspecialchars($grado) ?> | <strong>Asignatura:</strong> <?= htmlspecialchars($asignatura) ?></p>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="periodo_id">Periodo Académico:</label>
                <select name="periodo_id" id="periodo_id" class="form-select" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>N1</th>
                    <th>N2</th>
                    <th>N3</th>
                    <th>N4</th>
                    <th>N5</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estudiantes as $est): ?>
                    <tr>
                        <td><?= htmlspecialchars($est['nombre']) ?></td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <td>
                                <input type="number" name="notas[<?= $est['id'] ?>][nota<?= $i ?>]"
                                       step="0.01" min="0" max="20" class="form-control" required>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Guardar Notas</button>
    </form>

    <a href="docente_panel.php" class="btn btn-secondary mt-4">⬅️ Volver al Panel</a>
</div>
</body>
</html>

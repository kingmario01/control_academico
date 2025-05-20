<?php/*
 * Sistema de Registro Académico
 * © 2025 [Mario Alvarez Ramos]
 * Licencia: Propietaria. Uso no autorizado prohibido.
 * Contacto: alvarezramosmario37@gmail.com
 */

require_once '../vendor/autoload.php';
require_once '../config/database.php';
use Dompdf\Dompdf;

$estudiante_id = $_POST['estudiante_id'] ?? null;
$periodo_id = $_POST['periodo_id'] ?? null;

if (!$estudiante_id || !$periodo_id) {
    exit("Faltan parámetros.");
}

$estudiante = $pdo->prepare("
    SELECT e.nombre, e.documento_identidad, g.nombre AS grado, n.nombre AS nivel
    FROM estudiantes e
    JOIN grados g ON e.grado_id = g.id
    JOIN niveles n ON e.nivel_id = n.id
    WHERE e.id = ?
");
$estudiante->execute([$estudiante_id]);
$estudiante = $estudiante->fetch();

if (!$estudiante) exit("Estudiante no encontrado.");

$condicion_periodo = "";
$parametros = [$estudiante_id];

if ($periodo_id !== "todos") {
    $condicion_periodo = "AND n.periodo_id = ?";
    $parametros[] = $periodo_id;
}

// Obtener todas las notas por asignatura y periodo
$stmt = $pdo->prepare("
    SELECT a.nombre AS asignatura, p.nombre AS periodo,
           n.nota1, n.nota2, n.nota3, n.nota4, n.nota5, n.promedio, n.estado,
           (SELECT COUNT(*) FROM asistencias 
            WHERE estudiante_id = n.estudiante_id 
              AND asignatura_id = n.asignatura_id 
              AND estado = 'asistió') AS asistencias,
           (SELECT COUNT(*) FROM asistencias 
            WHERE estudiante_id = n.estudiante_id 
              AND asignatura_id = n.asignatura_id 
              AND estado = 'falta') AS faltas,
           (SELECT COUNT(*) FROM asistencias 
            WHERE estudiante_id = n.estudiante_id 
              AND asignatura_id = n.asignatura_id 
              AND estado = 'excusa') AS excusas
    FROM notas n
    JOIN asignaturas a ON n.asignatura_id = a.id
    JOIN periodos p ON n.periodo_id = p.id
    WHERE n.estudiante_id = ?
    $condicion_periodo
    ORDER BY p.fecha_inicio, a.nombre
");
$stmt->execute($parametros);
$registros = $stmt->fetchAll();

ob_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f2f2f2; }
        h3, h4 { margin: 0; }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <img src="img/escudo.jpeg" width="100" style="margin-bottom: 10px;">
        <h3>INSTITUCIÓN EDUCATIVA MARISCAL SUCRE</h3>
        <h4>Boletín Académico</h4>
    </div>

    <p><strong>Estudiante:</strong> <?= htmlspecialchars($estudiante['nombre']) ?><br>
       <strong>Documento:</strong> <?= htmlspecialchars($estudiante['documento_identidad']) ?><br>
       <strong>Grado:</strong> <?= htmlspecialchars($estudiante['grado']) ?> |
       <strong>Nivel:</strong> <?= htmlspecialchars($estudiante['nivel']) ?></p>

    <table>
        <thead>
            <tr>
                <th>Periodo</th>
                <th>Asignatura</th>
                <th>N1</th><th>N2</th><th>N3</th><th>N4</th><th>N5</th>
                <th>Promedio</th>
                <th>Estado</th>
                <th>Asistió</th>
                <th>Faltó</th>
                <th>Excusa</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['periodo']) ?></td>
                <td><?= htmlspecialchars($r['asignatura']) ?></td>
                <td><?= $r['nota1'] ?></td>
                <td><?= $r['nota2'] ?></td>
                <td><?= $r['nota3'] ?></td>
                <td><?= $r['nota4'] ?></td>
                <td><?= $r['nota5'] ?></td>
                <td><?= $r['promedio'] ?></td>
                <td><?= ucfirst($r['estado']) ?></td>
                <td><?= $r['asistencias'] ?? 0 ?></td>
                <td><?= $r['faltas'] ?? 0 ?></td>
                <td><?= $r['excusas'] ?? 0 ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$nombre_pdf = "boletin_" . preg_replace("/[^a-zA-Z0-9]/", "_", $estudiante['nombre']) . ".pdf";
$dompdf->stream($nombre_pdf, ["Attachment" => false]);

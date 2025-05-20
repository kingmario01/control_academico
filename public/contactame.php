<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contáctame</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 600px;">
    <h3>📬 Contáctame</h3>
    <p>¿Tienes preguntas, necesitas soporte o deseas adquirir una licencia?</p>
<p>PROYECTO UNIVERSITARIO REALIZADO EL 20/05/2025 BAJO LA LICENCIA MIT</p>

    <div class="card p-4">
        <p><strong>Desarrollador:</strong> Mario De Jesus Alvarez Ramos</p>
        <p><strong>Email:</strong> <a href="alvarezramosmario37@gmail.com">alvarezramosmario37.com</a></p>
        <p><strong>WhatsApp:</strong> <a href="+57 3113502045" target="_blank">+57 3113502045</a></p>
    </div>

    <a href="<?= $_SESSION['rol'] === 'administrador' ? 'admin_panel.php' : 'docente_panel.php' ?>" class="btn btn-secondary mt-4">
        ⬅️ Volver al Panel
    </a>
</div>
</body>
</html>

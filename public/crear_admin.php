<?php
require_once '../config/database.php';

$nombre = 'Admin';
$correo = 'admin@colegio.com';
$contraseña = password_hash("123456", PASSWORD_DEFAULT);  // Esto encripta la contraseña
$rol = 'administrador';

$stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES (?, ?, ?, ?)");
$stmt->execute([$nombre, $correo, $contraseña, $rol]);

echo "✅ Usuario creado correctamente.";
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
$stmt->execute([$correo]);
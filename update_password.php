<?php
// Move-App/update_password.php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$token || strlen($password) < 6) {
        die("<div class='alert alert-danger text-center'>Datos no válidos.</div>");
    }

    // Verificar token
    $stmt = $pdo->prepare("SELECT user_id FROM reset_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("<div class='alert alert-danger text-center'>El token es inválido o ha expirado.</div>");
    }

    // Actualizar contraseña
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $data['user_id']]);

    // Eliminar token
    $pdo->prepare("DELETE FROM reset_tokens WHERE user_id = ?")->execute([$data['user_id']]);

    echo "<div class='alert alert-success text-center'>Contraseña actualizada correctamente. <a href='login.php'>Inicia sesión</a>.</div>";
}
?>

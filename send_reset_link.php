<?php
// Move-App/send_reset_link.php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<div class='alert alert-danger text-center'>Correo inválido</div>");
    }

    // Buscar usuario
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("<div class='alert alert-warning text-center'>No se encontró ninguna cuenta con ese correo.</div>");
    }

    // Generar token único
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Guardar token en tabla reset_tokens (si no existe, créala)
    $pdo->prepare("CREATE TABLE IF NOT EXISTS reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )")->execute();

    // Borrar tokens previos del usuario
    $pdo->prepare("DELETE FROM reset_tokens WHERE user_id = ?")->execute([$user['id']]);

    // Insertar nuevo token
    $stmt = $pdo->prepare("INSERT INTO reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $token, $expires]);

    // Enlace de restablecimiento
    $resetLink = "http://localhost/Move-App/reset_password.php?token=$token";

    // Configurar correo
    $subject = "Recuperar contraseña - Move-App";
    $message = "
    <h3>Hola, {$user['name']}</h3>
    <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:</p>
    <p><a href='$resetLink'>$resetLink</a></p>
    <p>Este enlace expirará en 1 hora.</p>
    <br><p>Si no solicitaste este cambio, ignora este mensaje.</p>";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Move-App <no-reply@moveapp.local>";

    // Enviar correo
    if (mail($email, $subject, $message, $headers)) {
        echo "<div class='alert alert-success text-center'>📨 Se ha enviado un enlace de recuperación a tu correo.</div>";
    } else {
        echo "<div class='alert alert-danger text-center'>Error al enviar el correo. Revisa la configuración de PHP mail().</div>";
    }
}
?>

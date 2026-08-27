<?php
// Move-App/reset_password.php
require 'config.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("<div class='alert alert-danger text-center'>Token no válido.</div>");
}

$stmt = $pdo->prepare("SELECT user_id, expires_at FROM reset_tokens WHERE token = ?");
$stmt->execute([$token]);
$tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tokenData || strtotime($tokenData['expires_at']) < time()) {
    die("<div class='alert alert-danger text-center'>El enlace ha expirado o es inválido.</div>");
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Restablecer contraseña - Move-App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container">
  <div class="card mx-auto shadow-sm" style="max-width: 400px;">
    <div class="card-body p-4">
      <h4 class="text-center mb-3">Restablecer contraseña</h4>
      <form method="POST" action="update_password.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="mb-3">
          <label class="form-label">Nueva contraseña</label>
          <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary w-100">Actualizar contraseña</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>

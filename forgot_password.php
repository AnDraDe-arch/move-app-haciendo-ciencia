<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Recuperar contraseña - Move_App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="card shadow-sm mx-auto" style="max-width: 400px; border-radius: 1rem;">
    <div class="card-body p-4">
      <h4 class="text-center mb-3">🔐 Recuperar contraseña</h4>
      <p class="text-muted text-center">Ingresa tu correo para enviarte un enlace de restablecimiento.</p>
      <form action="send_reset_link.php" method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Correo electrónico</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Enviar enlace</button>
      </form>
      <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none text-muted small">Volver al inicio de sesión</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>

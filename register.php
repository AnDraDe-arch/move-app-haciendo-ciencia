<?php
session_start();
require 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- NUEVA VALIDACIÓN DETALLADA ---
    if (empty($name)) {
        $errors['name'] = 'El nombre es obligatorio.';
    }
    if (empty($email)) {
        $errors['email'] = 'El correo es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'El correo no tiene un formato válido.';
    } else {
        // Validar si el correo ya existe
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Este correo electrónico ya está registrado.';
        }
    }
    if (empty($password)) {
        $errors['password'] = 'La contraseña es obligatoria.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);
        // Redirigir al login con un mensaje de éxito (opcional)
        header('Location: login.php?registered=success');
        exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - Move_App</title>
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 1rem;
        }
    </style>
</head>
<body>
<div class="container register-container">
    <div class="col-md-7 col-lg-6 col-xl-5">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Crear Cuenta en Move_App </h3>
                    <p class="text-muted">Empieza a registrar tu progreso hoy</p>
                </div>
                
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre Completo</label>
                        <input type="text" name="name" id="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($name ?? '') ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" id="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($email ?? '') ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success">Registrarme</button>
                    </div>
                    <div class="text-center mt-3">
                        <small>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>

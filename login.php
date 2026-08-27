<?php
session_start();
require 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors['email'] = 'El correo es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'El formato del correo no es válido.';
    }

    if (empty($password)) {
        $errors['password'] = 'La contraseña es obligatoria.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors['general'] = 'Las credenciales proporcionadas son incorrectas.';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - Move_App</title>
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
        .login-container {
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
<div class="container login-container">
    <div class="col-md-6 col-lg-5 col-xl-4">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Move_App 🏃‍♂️</h3>
                    <p class="text-muted">Bienvenido de nuevo</p>
                </div>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger small p-2 text-center"><?= htmlspecialchars($errors['general']) ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
    <div class="mb-3">
        <label for="email" class="form-label">Correo Electrónico</label>
        <input type="email" name="email" id="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" required>
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
        <button type="submit" class="btn btn-primary">Entrar</button>
    </div>

    <!-- 🔽 Enlace de recuperación -->
    <div class="text-center mt-3">
        <a href="forgot_password.php" class="text-decoration-none text-muted small">
            ¿Olvidaste tu contraseña?
        </a>
    </div>

    <div class="text-center mt-3">
        <small>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></small>
    </div>
</form>

               
            </div>
        </div>
    </div>
</div>
</body>
</html>

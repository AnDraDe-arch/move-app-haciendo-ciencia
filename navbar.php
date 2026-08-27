<?php
// Move-App/navbar.php
if (session_status() === PHP_SESSION_NONE) session_start();
$user_name = $_SESSION['user_name'] ?? null;
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="dashboard.php">Move-App</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="report_view.php">Reportes</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Perfil</a></li>
      </ul>
      <div class="d-flex align-items-center">
        <?php if ($user_name): ?>
          <span class="me-2">👋 <?=htmlspecialchars($user_name)?></span>
          <a class="btn btn-outline-danger btn-sm" href="logout.php">Salir</a>
        <?php else: ?>
          <a class="btn btn-outline-primary btn-sm me-2" href="login.php">Ingresar</a>
          <a class="btn btn-primary btn-sm" href="register.php">Registro</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

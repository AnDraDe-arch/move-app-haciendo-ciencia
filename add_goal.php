<?php
// Move-App/add_goal.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $goal_type = trim($_POST['goal_type'] ?? '');
  $target_value = floatval($_POST['target_value'] ?? 0);
  $unit = trim($_POST['unit'] ?? '');
  
  if ($goal_type && $target_value > 0) {
    $stmt = $conn->prepare("
      INSERT INTO goals (user_id, goal_type, target_value, current_progress, unit, created_at)
      VALUES (?, ?, ?, 0, ?, NOW())
    ");
    $stmt->bind_param("isds", $user_id, $goal_type, $target_value, $unit);
    
    if ($stmt->execute()) {
      $message = "<div class='alert alert-success'>✅ Meta registrada correctamente.</div>";
    } else {
      $message = "<div class='alert alert-danger'>❌ Error al guardar la meta.</div>";
    }
    $stmt->close();
  } else {
    $message = "<div class='alert alert-warning'>Por favor, completa todos los campos correctamente.</div>";
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Nueva Meta - Move-App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .card { border: none; border-radius: 0.75rem; }
  </style>
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="container py-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="fw-bold text-primary mb-3"><i class="bi bi-bullseye"></i> Nueva Meta</h4>

        <?= $message ?>

        <form method="post" class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Tipo de meta</label>
            <select name="goal_type" class="form-select" required>
              <option value="">Selecciona una opción...</option>
              <option value="Correr">Correr</option>
              <option value="Caminar">Caminar</option>
              <option value="Pasos">Pasos</option>
              <option value="Calorías quemadas">Calorías quemadas</option>
              <option value="Tiempo activo">Tiempo activo</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Objetivo (número)</label>
            <input type="number" step="0.1" name="target_value" class="form-control" placeholder="Ej. 10" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Unidad</label>
            <select name="unit" class="form-select" required>
              <option value="km">Kilómetros</option>
              <option value="pasos">Pasos</option>
              <option value="minutos">Minutos</option>
              <option value="kcal">Calorías</option>
            </select>
          </div>

          <div class="col-12 mt-3">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Guardar Meta</button>
            <a href="report_view.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>

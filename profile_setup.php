<?php
session_start();
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Si el formulario se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = $_POST['age'];
    $activity = $_POST['activity_level'];
    $time = $_POST['available_time'];
    $goal = $_POST['goal'];

    // Eliminar perfil anterior si existe
    $conn->query("DELETE FROM user_profile WHERE user_id = $user_id");

    $stmt = $conn->prepare("INSERT INTO user_profile (user_id, age, activity_level, available_time, goal) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $age, $activity, $time, $goal);
    $stmt->execute();

    header("Location: profile_feedback.php");
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Personalizar perfil - Move-App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php include 'navbar.php'; ?>
  <div class="container py-5">
    <div class="card shadow-sm p-4">
      <h4 class="text-primary mb-3">🧍 Personaliza tu perfil de actividad</h4>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Edad</label>
          <input type="number" name="age" class="form-control" min="30" max="99" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Nivel de actividad física</label>
          <select name="activity_level" class="form-select" required>
            <option value="bajo">Bajo (poco movimiento diario)</option>
            <option value="moderado">Moderado (camina o se ejercita ocasionalmente)</option>
            <option value="alto">Alto (ejercicio frecuente)</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Tiempo disponible por día</label>
          <select name="available_time" class="form-select" required>
            <option value="15-30">15 a 30 minutos</option>
            <option value="30-60">30 a 60 minutos</option>
            <option value="60+">Más de una hora</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Objetivo principal</label>
          <select name="goal" class="form-select" required>
            <option value="mantener">Mantenerme activo</option>
            <option value="bajar_peso">Bajar de peso</option>
            <option value="mejorar_movilidad">Mejorar movilidad o flexibilidad</option>
            <option value="ganar_fuerza">Ganar fuerza</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Guardar y ver recomendaciones</button>
      </form>
    </div>
  </div>
</body>
</html>

<?php 
// Move-App/profile.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/config.php';
$user_id = $_SESSION['user_id'];

// Obtener datos del usuario
$user = $conn->query("SELECT name, email FROM users WHERE id = $user_id")->fetch_assoc();

// Obtener datos de perfil personalizado (si existen)
$profile = $conn->query("SELECT * FROM user_profile WHERE user_id = $user_id")->fetch_assoc();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Perfil - Move-App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>

<div class="container py-4">
  <h3 class="mb-4 text-primary">👤 Mi perfil</h3>

  <!-- 🔹 DATOS DE CUENTA -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">Datos de cuenta</h5>
      <form method="POST" action="update_profile.php">
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Correo electrónico</label>
          <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
       <!-- <div class="mb-3">
          <label class="form-label">Nueva contraseña (opcional)</label>
          <input name="password" type="password" class="form-control">
        </div>-->
        <button class="btn btn-primary"> Guardar cambios</button>
      </form>
    </div>
  </div>

  <!-- 🔹 PERSONALIZACIÓN DE ACTIVIDAD -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title"> Personaliza tu perfil de actividad</h5>
      <form method="POST" action="save_profile_activity.php">
        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label">Edad</label>
            <input type="number" name="age" class="form-control" value="<?= htmlspecialchars($profile['age'] ?? '') ?>" min="30" max="99" required>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Nivel de actividad</label>
            <select name="activity_level" class="form-select" required>
              <option value="">Selecciona...</option>
              <option value="bajo" <?= isset($profile['activity_level']) && $profile['activity_level']=='bajo' ? 'selected' : '' ?>>Bajo</option>
              <option value="moderado" <?= isset($profile['activity_level']) && $profile['activity_level']=='moderado' ? 'selected' : '' ?>>Moderado</option>
              <option value="alto" <?= isset($profile['activity_level']) && $profile['activity_level']=='alto' ? 'selected' : '' ?>>Alto</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Tiempo disponible</label>
            <select name="available_time" class="form-select" required>
              <option value="">Selecciona...</option>
              <option value="15-30" <?= isset($profile['available_time']) && $profile['available_time']=='15-30' ? 'selected' : '' ?>>15-30 min</option>
              <option value="30-60" <?= isset($profile['available_time']) && $profile['available_time']=='30-60' ? 'selected' : '' ?>>30-60 min</option>
              <option value="60+" <?= isset($profile['available_time']) && $profile['available_time']=='60+' ? 'selected' : '' ?>>Más de 1 hora</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label">Objetivo</label>
            <select name="goal" class="form-select" required>
              <option value="">Selecciona...</option>
              <option value="mantener" <?= isset($profile['goal']) && $profile['goal']=='mantener' ? 'selected' : '' ?>>Mantenerme activo</option>
              <option value="bajar_peso" <?= isset($profile['goal']) && $profile['goal']=='bajar_peso' ? 'selected' : '' ?>>Bajar de peso</option>
              <option value="mejorar_movilidad" <?= isset($profile['goal']) && $profile['goal']=='mejorar_movilidad' ? 'selected' : '' ?>>Mejorar movilidad</option>
              <option value="ganar_fuerza" <?= isset($profile['goal']) && $profile['goal']=='ganar_fuerza' ? 'selected' : '' ?>>Ganar fuerza</option>
            </select>
          </div>
        </div>
        <button class="btn btn-success mt-2"> Guardar personalización</button>
      </form>
    </div>
  </div>

  <!-- 🔹 RETROALIMENTACIÓN AUTOMÁTICA -->
  <?php if ($profile): ?>
  <?php
    $feedback = [];

    switch ($profile['activity_level']) {
        case 'bajo':
            $feedback[] = "🚶 Comienza con caminatas suaves de 15–20 minutos diarios.";
            $feedback[] = "🧘 Agrega ejercicios de estiramiento y movilidad articular.";
            break;
        case 'moderado':
            $feedback[] = "💪 Alterna caminatas y ejercicios de fuerza ligera con bandas o pesas pequeñas.";
            $feedback[] = "🕒 Procura ejercitarte 4–5 veces por semana para mantener tu ritmo.";
            break;
        case 'alto':
            $feedback[] = "🏋️ Varía tus rutinas con cardio, fuerza y flexibilidad.";
            $feedback[] = "🔥 Prueba ejercicios funcionales o natación si tienes acceso.";
            break;
    }

    switch ($profile['goal']) {
        case 'bajar_peso':
            $feedback[] = "🥗 Cuida tu alimentación y prioriza comidas balanceadas.";
            $feedback[] = "🚴 Agrega 30–45 min de ejercicio aeróbico al menos 4 veces por semana.";
            break;
        case 'mejorar_movilidad':
            $feedback[] = "🧘 Dedica 10–15 minutos al día a estiramientos suaves.";
            $feedback[] = "🚶 Camina regularmente para mantener la fluidez del movimiento.";
            break;
        case 'ganar_fuerza':
            $feedback[] = "🏋️ Enfócate en ejercicios de resistencia progresiva.";
            $feedback[] = "😴 El descanso es parte del progreso: duerme bien y mantén buena hidratación.";
            break;
    }

    switch ($profile['available_time']) {
        case '15-30':
            $feedback[] = "⏱️ Rutinas cortas pero constantes (20 min diarios) te darán grandes resultados.";
            break;
        case '30-60':
            $feedback[] = "⏱️ Con 30–60 min puedes dividir entre calentamiento, cardio y fuerza.";
            break;
        case '60+':
            $feedback[] = "💪 Con más de una hora, incluye variedad: fuerza, caminatas largas y estiramientos.";
            break;
    }
  ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title text-primary">💡 Consejos personalizados</h5>
      <?php foreach ($feedback as $tip): ?>
        <div class="alert alert-info mb-2"><?= htmlspecialchars($tip) ?></div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>
</body>
</html>

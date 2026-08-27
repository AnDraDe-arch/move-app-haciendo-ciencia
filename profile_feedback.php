<?php
session_start();
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

$profile = $conn->query("SELECT * FROM user_profile WHERE user_id = $user_id")->fetch_assoc();
if (!$profile) {
    header('Location: profile_setup.php');
    exit;
}

$feedback = [];

// Consejos según el nivel de actividad
switch ($profile['activity_level']) {
    case 'bajo':
        $feedback[] = "🚶 Comienza con caminatas suaves de 15–20 minutos diarios.";
        $feedback[] = "🧘 Incluye ejercicios de movilidad articular o yoga para principiantes.";
        break;
    case 'moderado':
        $feedback[] = "💪 Puedes alternar caminatas y ejercicios de fuerza ligera (pesas o bandas).";
        $feedback[] = "🕒 Mantén constancia: 4–5 días por semana es ideal.";
        break;
    case 'alto':
        $feedback[] = "🏋️ Varía tu rutina: combina fuerza, cardio y flexibilidad.";
        $feedback[] = "🔥 Prueba intervalos HIIT o natación para mantener la energía.";
        break;
}

// Consejos según el objetivo
switch ($profile['goal']) {
    case 'bajar_peso':
        $feedback[] = "🥗 Cuida tu alimentación: prioriza verduras, frutas y proteínas.";
        $feedback[] = "🚴 Agrega sesiones de cardio de 30–45 minutos 4 veces por semana.";
        break;
    case 'mejorar_movilidad':
        $feedback[] = "🧘 Realiza estiramientos suaves 10–15 minutos al día.";
        $feedback[] = "🚶 Caminar regularmente ayuda a mantener tus articulaciones activas.";
        break;
    case 'ganar_fuerza':
        $feedback[] = "🏋️ Haz ejercicios de resistencia progresiva con tu propio peso o pesas ligeras.";
        $feedback[] = "🛌 Descansa bien: el músculo se recupera mientras duermes.";
        break;
}

// Consejos según tiempo disponible
switch ($profile['available_time']) {
    case '15-30':
        $feedback[] = "⏱️ Con poco tiempo, enfócate en rutinas cortas pero consistentes (20 min diarios).";
        break;
    case '30-60':
        $feedback[] = "⏱️ Excelente tiempo: puedes dividir entre calentamiento, cardio y fuerza.";
        break;
    case '60+':
        $feedback[] = "💪 Tienes gran margen: incluye ejercicios variados y caminatas largas.";
        break;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Consejos personalizados - Move-App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php include 'navbar.php'; ?>
  <div class="container py-5">
    <h4 class="text-primary mb-4">💡 Consejos personalizados para ti</h4>
    <div class="card shadow-sm p-4">
      <?php foreach ($feedback as $tip): ?>
        <div class="alert alert-info mb-2"><?= htmlspecialchars($tip) ?></div>
      <?php endforeach; ?>
    </div>
    <a href="profile_setup.php" class="btn btn-outline-secondary mt-3">Editar mi perfil</a>
  </div>
</body>
</html>

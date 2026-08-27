<?php
// Move-App/report_view.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';
$user_id = $_SESSION['user_id'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reportes - Move-App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
      body {
          font-family: 'Poppins', sans-serif;
          background-color: #f0f2f5;
      }
      .table th {
          background-color: #0d6efd;
          color: white;
      }
      .card {
          border: none;
          border-radius: 0.75rem;
      }
      h3 i {
          margin-right: 8px;
      }
      .progress {
          height: 20px;
          border-radius: 10px;
      }
      .progress-bar {
          font-size: 0.8rem;
          font-weight: 600;
      }
  </style>
</head>
<body>
  
  <?php include 'navbar.php'; ?>

  <div class="container py-4">
    <h3 class="fw-bold mb-4 text-primary"><i class="bi bi-graph-up-arrow"></i> Reportes Generales</h3>

    <!-- ✅ Mensaje de éxito -->
    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success text-center">
        <?php
          if ($_GET['msg'] == 'actividad_eliminada') echo " Actividad eliminada correctamente.";
          if ($_GET['msg'] == 'meta_eliminada') echo "Meta eliminada correctamente.";
        ?>
      </div>
    <?php endif; ?>

    <!-- 🏃 ACTIVIDADES -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title"> Actividades recientes</h5>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-primary">
              <tr>
                <th>Tipo</th>
                <th>Duración (min)</th>
                <th>Calorías</th>
                <th>Notas</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $activities = $conn->query("
                SELECT 
                    id,
                    activity_type AS tipo, 
                    duration_minutes AS minutos, 
                    calories AS calorias,
                    notes AS notas,
                    activity_date AS fecha 
                FROM activities 
                WHERE user_id = $user_id 
                ORDER BY id DESC 
                LIMIT 10
              ");

              if ($activities && $activities->num_rows > 0):
                  while($row = $activities->fetch_assoc()):
              ?>
                <tr>
                  <td><?= htmlspecialchars($row['tipo']) ?></td>
                  <td><?= htmlspecialchars($row['minutos']) ?></td>
                  <td><?= htmlspecialchars($row['calorias']) ?></td>
                  <td><?= htmlspecialchars($row['notas']) ?></td>
                  <td><?= htmlspecialchars($row['fecha']) ?></td>
                  <td>
                    <a href="delete_activity.php?id=<?= $row['id'] ?>" 
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('¿Seguro que deseas eliminar esta actividad?');">
                        Eliminar
                    </a>
                  </td>
                </tr>
              <?php 
                  endwhile;
              else:
              ?>
                <tr>
                  <td colspan="6" class="text-center text-muted">No hay actividades registradas</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 🎯 METAS -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title"> Metas</h5>
        <a href="add_goal.php" class="btn btn-success mb-3">Nueva Meta</a>

        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead class="table-primary">
              <tr>
                <th>Meta</th>
                <th>Progreso</th>
                <th>Objetivo</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $goals = $conn->query("
                SELECT id, goal_type AS nombre, current_progress AS progreso, target_value AS meta, unit
                FROM goals 
                WHERE user_id = $user_id
              ");

              if ($goals && $goals->num_rows > 0):
                  while($row = $goals->fetch_assoc()):
                      $porcentaje = 0;
                      if ($row['meta'] > 0) {
                          $porcentaje = round(($row['progreso'] / $row['meta']) * 100, 1);
                          if ($porcentaje > 100) $porcentaje = 100;
                      }
                      $completada = $porcentaje >= 100;
              ?>
                <tr>
                  <td><?= htmlspecialchars($row['nombre']) ?></td>
                  <td style="width: 40%;">
                    <div class="progress">
                      <div class="progress-bar <?= $completada ? 'bg-success' : 'bg-primary' ?>" 
                           role="progressbar"
                           style="width: <?= $porcentaje ?>%;">
                        <?= $porcentaje ?>%
                      </div>
                    </div>
                    <small class="text-muted">
                      <?= htmlspecialchars($row['progreso']) . ' / ' . htmlspecialchars($row['meta']) . ' ' . htmlspecialchars($row['unit']) ?>
                    </small>
                  </td>
                  <td><?= htmlspecialchars($row['meta']) . ' ' . htmlspecialchars($row['unit']) ?></td>
                  <td>
                    <?php if ($completada): ?>
                      <span class="badge bg-success"> Completada</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">En progreso</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="delete_goal.php?id=<?= $row['id'] ?>" 
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('¿Seguro que deseas eliminar esta meta?');">
                        Eliminar
                    </a>
                  </td>
                </tr>
              <?php 
                  endwhile;
              else:
              ?>
                <tr>
                  <td colspan="5" class="text-center text-muted">No hay metas registradas</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 🏆 LOGROS -->
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">🏆 Logros</h5>
        <ul class="list-group list-group-flush">
          <?php
        $achievements = $conn->query("
  SELECT a.title AS titulo, a.description AS descripcion, ua.achieved_at AS fecha
  FROM user_achievements ua
  INNER JOIN achievements a ON ua.achievement_id = a.id
  WHERE ua.user_id = $user_id
  ORDER BY ua.achieved_at DESC
");

         if ($achievements && $achievements->num_rows > 0): ?>
            <?php while($row = $achievements->fetch_assoc()): ?>
            <li class="list-group-item">
            <strong><?= htmlspecialchars($row['titulo']) ?></strong> — 
            <?= htmlspecialchars($row['descripcion']) ?><br>
            <small class="text-muted">Logrado el <?= htmlspecialchars($row['fecha']) ?></small>
        </li>
    <?php endwhile; ?>
<?php else: ?>
    <li class="list-group-item text-center text-muted">No hay logros registrados</li>
<?php endif; ?>

        </ul>
      </div>
    </div>

  </div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'config.php';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Actividad - Move_App</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .card { border: none; border-radius: 0.75rem; }
        .progress { height: 8px; border-radius: 4px; }
        .progress-bar { transition: width 0.6s ease; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4">
    <div class="row g-4">

        <!-- PANEL IZQUIERDO -->
        <div class="col-lg-4">

            <!-- 🔵 SUBIR DATOS DE MI BAND -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <h5 class="card-title"><i class="bi bi-watch"></i> Sincronizar Mi Band</h5>
                    <p class="text-muted">Sube el archivo exportado desde tu app <b>Mi Fitness</b> o <b>Zepp Life</b> (pasos, calorías, ritmo cardíaco).</p>
                    <form id="uploadBandForm" enctype="multipart/form-data">
                        <input type="file" name="band_file" id="band_file" accept=".zip,.csv,.json" class="form-control mb-3" required>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload"></i> Subir y sincronizar</button>
                    </form>
                    <div class="mt-3">
                        <div class="progress d-none" id="uploadProgress"><div class="progress-bar bg-primary" style="width: 0%;"></div></div>
                        <div id="bandStatus" class="mt-2 text-muted small"></div>
                    </div>
                </div>
            </div>

            <!-- FORMULARIO MANUAL -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="bi bi-plus-circle"></i> Registrar Actividad</h5>
                    <form id="activityForm">
                        <div class="mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="activity_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de actividad</label>
                            <input type="text" name="activity_type" class="form-control" placeholder="Ej: Caminar, Correr" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duración (minutos)</label>
                            <input type="number" name="duration_minutes" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Opcional"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Guardar</button>
                        <div id="msg" class="mt-3"></div>
                    </form>
                </div>
            </div>

            <!-- FEEDBACK -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="bi bi-lightbulb"></i> Retroalimentación</h5>
                    <p id="feedback" class="text-muted">Registra o sube tus datos para ver tu progreso.</p>
                </div>
            </div>
        </div>

        <!-- PANEL DERECHO -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="bi bi-graph-up"></i> Tu Progreso</h5>
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="bi bi-list-ul"></i> Historial</h5>
                    <div id="activitiesList">Cargando...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// --- BARRA DE PROGRESO ---
const progressBar = document.querySelector("#uploadProgress .progress-bar");
const progressContainer = document.getElementById("uploadProgress");
const bandStatus = document.getElementById("bandStatus");

// --- SUBIDA AUTOMÁTICA DE MI BAND ---
document.getElementById("uploadBandForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const file = document.getElementById("band_file").files[0];
  if (!file) return alert("Selecciona un archivo primero.");

  progressContainer.classList.remove("d-none");
  progressBar.style.width = "0%";
  bandStatus.textContent = "Subiendo archivo...";

  const formData = new FormData();
  formData.append("band_file", file);

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "upload_auto.php");
  xhr.upload.addEventListener("progress", (e) => {
    if (e.lengthComputable) {
      const percent = Math.round((e.loaded / e.total) * 100);
      progressBar.style.width = percent + "%";
    }
  });

  xhr.onload = async () => {
    const res = JSON.parse(xhr.responseText);
    if (res.success) {
      bandStatus.textContent = `✅ ${res.inserted} actividades importadas correctamente.`;
      progressBar.style.width = "100%";
      await fetchActivities();
    } else {
      bandStatus.textContent = "❌ " + res.error;
      progressContainer.classList.add("d-none");
    }
  };

  xhr.onerror = () => {
    bandStatus.textContent = "❌ Error al subir el archivo.";
    progressContainer.classList.add("d-none");
  };

  xhr.send(formData);
});

// --- CARGAR Y GRAFICAR ACTIVIDADES ---
let activityChart;
async function fetchActivities() {
  const res = await fetch("fetch_activities.php");
  const data = await res.json();
  updateActivitiesList(data);
  updateChart(data);
  updateFeedback(data);
}

function updateActivitiesList(data) {
  const list = document.getElementById("activitiesList");
  if (!data.length) {
    list.innerHTML = '<p class="text-center text-muted mt-3">No hay actividades aún.</p>';
    return;
  }
  list.innerHTML = data.map(a => `
    <div class="border-bottom py-2 d-flex justify-content-between">
      <div>
        <strong>${a.activity_type}</strong><br>
        <small class="text-muted">${a.activity_date} · ${a.notes || ''}</small>
      </div>
      <span class="badge bg-primary">${a.duration_minutes} min</span>
    </div>
  `).join('');
}

function updateChart(data) {
  const ctx = document.getElementById("activityChart");
  if (activityChart) activityChart.destroy();
  const labels = data.map(a => a.activity_date);
  const durations = data.map(a => a.duration_minutes);
  activityChart = new Chart(ctx, {
    type: "line",
    data: { labels, datasets: [{ label: "Duración (min)", data: durations, borderColor: "#007bff", backgroundColor: "rgba(0,123,255,0.2)", fill: true, tension: 0.3 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });
}

function updateFeedback(data) {
  const f = document.getElementById("feedback");
  if (data.length >= 5) f.textContent = "¡Excelente! Mantén tu ritmo, ya tienes constancia 💪";
  else if (data.length > 0) f.textContent = "¡Vas bien! Un poco cada día suma 👏";
  else f.textContent = "Registra o sube tu primera actividad.";
}

// --- GUARDAR ACTIVIDAD MANUAL ---
document.getElementById("activityForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const form = new FormData(e.target);
  const res = await fetch("save_activity.php", { method: "POST", body: form });
  const json = await res.json();
  const msg = document.getElementById("msg");
  if (json.success) {
    msg.innerHTML = '<div class="alert alert-success">Actividad guardada correctamente.</div>';
    await fetchActivities();
  } else {
    msg.innerHTML = '<div class="alert alert-danger">' + json.error + '</div>';
  }
});

fetchActivities();
</script>
</body>
</html>

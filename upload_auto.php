<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$user_id = $_SESSION['user_id'];

// --- VALIDAR ARCHIVO ---
if (!isset($_FILES['band_file']) || $_FILES['band_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No se recibió ningún archivo válido.']);
    exit;
}

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$filename = basename($_FILES['band_file']['name']);
$targetPath = $uploadDir . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
move_uploaded_file($_FILES['band_file']['tmp_name'], $targetPath);

// --- DETECTAR TIPO ---
$ext = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
$activities = [];

try {
    if ($ext === 'zip') {
        $zip = new ZipArchive;
        if ($zip->open($targetPath) === TRUE) {
            $extractDir = $uploadDir . uniqid('extract_');
            mkdir($extractDir);
            $zip->extractTo($extractDir);
            $zip->close();

            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractDir));
            foreach ($files as $f) {
                if ($f->isFile() && preg_match('/\.(csv|json)$/i', $f->getFilename())) {
                    $activities = array_merge($activities, parseFile($f->getPathname()));
                }
            }
        }
    } else {
        $activities = parseFile($targetPath);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al leer los datos: ' . $e->getMessage()]);
    exit;
}

// --- GUARDAR EN BD ---
if (empty($activities)) {
    echo json_encode(['success' => false, 'error' => 'No se encontraron datos reconocibles en el archivo.']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO activities (user_id, activity_date, activity_type, duration_minutes, notes) VALUES (?, ?, ?, ?, ?)");
$count = 0;
foreach ($activities as $a) {
    $stmt->execute([$user_id, $a['date'], $a['type'], $a['duration'], $a['notes']]);
    $count++;
}

echo json_encode(['success' => true, 'inserted' => $count]);


/**
 * Detecta y parsea un archivo (CSV o JSON)
 */
function parseFile($path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $results = [];

    if ($ext === 'csv') {
        $handle = fopen($path, 'r');
        if (!$handle) return [];
        $header = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            // Detectar formato de Mi Band o Zepp
            $date = $data['Date'] ?? $data['fecha'] ?? $data['time'] ?? date('Y-m-d');
            $steps = $data['Steps'] ?? $data['steps'] ?? 0;
            $calories = $data['Calories'] ?? $data['calories'] ?? 0;
            $duration = round(($steps / 100)); // estimación
            $notes = "Pasos: $steps, Calorías: $calories";

            $results[] = [
                'date' => substr($date, 0, 10),
                'type' => 'Mi Band (importado)',
                'duration' => $duration,
                'notes' => $notes
            ];
        }
        fclose($handle);
    } elseif ($ext === 'json') {
        $content = json_decode(file_get_contents($path), true);
        if (is_array($content)) {
            foreach ($content as $entry) {
                $date = $entry['date'] ?? $entry['time'] ?? date('Y-m-d');
                $steps = $entry['steps'] ?? 0;
                $calories = $entry['calories'] ?? 0;
                $notes = "Pasos: $steps, Calorías: $calories";
                $duration = round(($steps / 100));
                $results[] = [
                    'date' => substr($date, 0, 10),
                    'type' => 'Mi Band (importado)',
                    'duration' => $duration,
                    'notes' => $notes
                ];
            }
        }
    }

    return $results;
}
?>

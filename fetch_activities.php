<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])){
    echo json_encode([]);
    exit;
}

require 'config.php';
$user_id = $_SESSION['user_id'];

// --- 1️⃣ Actividades manuales ---
$stmt1 = $pdo->prepare("
    SELECT 
        id, 
        activity_date, 
        activity_type, 
        duration_minutes, 
        calories, 
        notes,
        'manual' AS source
    FROM activities 
    WHERE user_id = ? 
");

// --- 2️⃣ Datos automáticos (de pulsera o CSV) ---
$stmt2 = $pdo->prepare("
    SELECT 
        id, 
        date_recorded AS activity_date,
        'Sensor (Pulsera)' AS activity_type,
        ROUND(steps / 100) AS duration_minutes,  -- estimación de minutos
        calories,
        CONCAT('Pasos: ', steps, ', FC: ', heart_rate) AS notes,
        'auto' AS source
    FROM sensor_data 
    WHERE user_id = ?
");

$stmt1->execute([$user_id]);
$stmt2->execute([$user_id]);

$manual = $stmt1->fetchAll(PDO::FETCH_ASSOC);
$auto = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// --- 3️⃣ Fusionar y ordenar ---
$merged = array_merge($manual, $auto);

usort($merged, function($a, $b) {
    return strtotime($b['activity_date']) - strtotime($a['activity_date']);
});

// --- 4️⃣ Limitar a los últimos 100 ---
$merged = array_slice($merged, 0, 100);

echo json_encode($merged);
?>

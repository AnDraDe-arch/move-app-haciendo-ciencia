<?php
// Edita estos valores con tus credenciales
$DB_HOST = 'localhost';
$DB_NAME = 'actividad_fisica';
$DB_USER = 'root';
$DB_PASS = '';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME}", $DB_USER, $DB_PASS, $options);
} catch (Exception $e) {
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}

// ✅ Agregamos conexión MySQLi para compatibilidad con report_view.php
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die('Error de conexión MySQLi: ' . $conn->connect_error);
}
?>

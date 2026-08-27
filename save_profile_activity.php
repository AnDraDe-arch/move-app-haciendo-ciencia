<?php
session_start();
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$age = $_POST['age'];
$activity = $_POST['activity_level'];
$time = $_POST['available_time'];
$goal = $_POST['goal'];

// Borra perfil anterior
$conn->query("DELETE FROM user_profile WHERE user_id = $user_id");

$stmt = $conn->prepare("INSERT INTO user_profile (user_id, age, activity_level, available_time, goal) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $user_id, $age, $activity, $time, $goal);
$stmt->execute();

header("Location: profile.php");
exit;
?>

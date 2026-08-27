<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $activity_id = intval($_GET['id']);

    // Eliminar solo si pertenece al usuario logueado
    $stmt = $conn->prepare("DELETE FROM activities WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $activity_id, $user_id);

    if ($stmt->execute()) {
        header("Location: report_view.php?msg=actividad_eliminada");
        exit;
    } else {
        echo "Error al eliminar la actividad.";
    }
} else {
    echo "ID de actividad no especificado.";
}

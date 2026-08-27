<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $goal_id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM goals WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $goal_id, $user_id);

    if ($stmt->execute()) {
        header("Location: report_view.php?msg=meta_eliminada");
        exit;
    } else {
        echo "Error al eliminar la meta.";
    }
} else {
    echo "ID de meta no especificado.";
}

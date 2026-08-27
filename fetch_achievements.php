<?php
session_start();
header('Content-Type: application/json');
require 'config.php';

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'No autorizado']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Obtener todos los logros
    $stmt = $pdo->query("
        SELECT a.id, a.title, a.description, a.icon,
               CASE WHEN ua.achievement_id IS NOT NULL THEN 1 ELSE 0 END AS unlocked,
               ua.achieved_at
        FROM achievements a
        LEFT JOIN user_achievements ua
            ON a.id = ua.achievement_id AND ua.user_id = $user_id
        ORDER BY a.id ASC
    ");
    $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'achievements' => $achievements]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

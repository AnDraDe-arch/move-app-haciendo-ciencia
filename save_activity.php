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

    // Verificar si vienen datos automáticos (por ejemplo desde CSV o API)
    $is_auto = isset($_POST['source']) && $_POST['source'] === 'auto';

    $activity_date = $_POST['activity_date'] ?? date('Y-m-d');
    $activity_type = trim($_POST['activity_type'] ?? '');
    $duration = intval($_POST['duration_minutes'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $steps = intval($_POST['steps'] ?? 0);
    $heart_rate = floatval($_POST['heart_rate'] ?? 0);
    $calories = floatval($_POST['calories'] ?? 0);

    if (!$is_auto && (!$activity_date || !$activity_type || $duration <= 0)) {
        echo json_encode(['success' => false, 'error' => 'Campos incompletos o duración inválida.']);
        exit;
    }

    // --- Estimación de calorías (solo si es manual y no se pasa el valor) ---
    if (!$is_auto && $calories <= 0) {
        switch (strtolower($activity_type)) {
            case 'correr': $calories = $duration * 10; break;
            case 'caminar': $calories = $duration * 5; break;
            case 'bicicleta': $calories = $duration * 8; break;
            case 'yoga': $calories = $duration * 4; break;
            default: $calories = $duration * 6; break;
        }
    }

    // --- Guardar en tabla correspondiente ---
    if ($is_auto) {
        // 📡 Datos provenientes de pulsera o archivo CSV
        $stmt = $pdo->prepare("
            INSERT INTO sensor_data (user_id, steps, calories, heart_rate, date_recorded)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $steps, $calories, $heart_rate, $activity_date]);
        $feedbackMessage = "📲 Datos automáticos registrados correctamente.";
    } else {
        // ✍️ Actividad manual
        $stmt = $pdo->prepare("
            INSERT INTO activities (user_id, activity_date, activity_type, duration_minutes, calories, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $activity_date, $activity_type, $duration, $calories, $notes]);

        // --- Retroalimentación básica ---
        $feedbackMessage = '¡Actividad registrada! 💪';
        $stmt = $pdo->prepare("
            SELECT duration_minutes FROM activities 
            WHERE user_id = ? ORDER BY activity_date DESC, id DESC LIMIT 2
        ");
        $stmt->execute([$user_id]);
        $last = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($last) === 2) {
            $curr = $last[0]['duration_minutes'];
            $prev = $last[1]['duration_minutes'];
            if ($curr > $prev) $feedbackMessage = "¡Excelente! Aumentaste tu tiempo en " . ($curr - $prev) . " minutos.";
            elseif ($curr == $prev) $feedbackMessage = "¡Constante! Mantienes el mismo tiempo 👏";
            else $feedbackMessage = "¡Bien hecho! La constancia es clave 💪";
        } elseif (count($last) === 1) {
            $feedbackMessage = "¡Primera actividad registrada! 🎉";
        }

        // --- Actualizar metas ---
        $stmt = $pdo->prepare("
            SELECT id, goal_type, target_value, current_progress, completed
            FROM goals
            WHERE user_id = ? AND goal_type = ? AND completed = 0
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$user_id, $activity_type]);
        $goal = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($goal) {
            $newProgress = $goal['current_progress'] + $duration;
            $completed = ($newProgress >= $goal['target_value']) ? 1 : 0;

            if ($completed) {
                $feedbackMessage .= " 🎯 ¡Felicidades! Has alcanzado tu meta de {$goal['target_value']} minutos.";
                $newProgress = $goal['target_value'];
            }

            $update = $pdo->prepare("UPDATE goals SET current_progress = ?, completed = ? WHERE id = ?");
            $update->execute([$newProgress, $completed, $goal['id']]);
        }

        // --- Logros automáticos ---
        function checkAchievements($pdo, $user_id)
        {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_sessions, SUM(duration_minutes) as total_minutes 
                FROM activities WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            $total_sessions = intval($stats['total_sessions']);
            $total_minutes = intval($stats['total_minutes']);

            $achievements = $pdo->query("SELECT * FROM achievements")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($achievements as $a) {
                $earned = false;
                switch ($a['condition_type']) {
                    case 'first_activity':
                        if ($total_sessions >= 1) $earned = true;
                        break;
                    case 'total_sessions':
                        if ($total_sessions >= $a['condition_value']) $earned = true;
                        break;
                    case 'total_minutes':
                        if ($total_minutes >= $a['condition_value']) $earned = true;
                        break;
                }

                if ($earned) {
                    $check = $pdo->prepare("SELECT COUNT(*) FROM user_achievements WHERE user_id = ? AND achievement_id = ?");
                    $check->execute([$user_id, $a['id']]);
                    if ($check->fetchColumn() == 0) {
                        $insert = $pdo->prepare("INSERT INTO user_achievements (user_id, achievement_id) VALUES (?, ?)");
                        $insert->execute([$user_id, $a['id']]);
                    }
                }
            }
        }

        checkAchievements($pdo, $user_id);
    }

    // ✅ Respuesta final
    echo json_encode([
        'success' => true,
        'feedback' => $feedbackMessage,
        'calories' => $calories,
        'source' => $is_auto ? 'auto' : 'manual'
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()]);
}
?>

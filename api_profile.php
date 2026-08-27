<?php
// Move-App/api_profile.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/config.php'; // debe definir $conn (mysqli)

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'No autenticado']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT id, name, email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        echo json_encode(['status'=>'ok','data'=>$user]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Usuario no encontrado']);
    }
    $stmt->close();
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if ($name === '' || $email === '') {
        echo json_encode(['status'=>'error','message'=>'Nombre y correo requeridos']);
        exit;
    }

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name=?, email=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $hash, $user_id);
    } else {
        $sql = "UPDATE users SET name=?, email=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $email, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['user_name'] = $name; // actualiza nombre en navbar
        echo json_encode(['status'=>'ok','message'=>'Perfil actualizado']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Error al actualizar']);
    }
    $stmt->close();
    exit;
}

echo json_encode(['status'=>'error','message'=>'Método no permitido']);
exit;
?>

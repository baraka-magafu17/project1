<?php
header('Content-Type: application/json');
session_start();
require_once 'db_config.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "error" => "Weka email na password"]);
    exit;
}

$stmt = $conn->prepare("SELECT user_id, name, password FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($user_id, $name, $hash);

if ($stmt->fetch()) {
    $stmt->close();
    $is_valid = false;

    // Kwa sasa tunalinganisha plain text
    if ($password === $hash) {
        $is_valid = true;
    }

    if ($is_valid) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user_id;
        $_SESSION['name'] = $name;
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Password si sahihi"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Mtumiaji hajapatikana"]);
}
$conn->close();
?>

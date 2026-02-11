<?php
header('Content-Type: application/json');
session_start();
require_once 'db_config.php';

$user_id = $_SESSION['user_id'] ?? null;
$name = trim($_POST['name'] ?? '');
$desc = trim($_POST['description'] ?? '');

if (!$user_id) {
    echo json_encode(["success" => false, "error" => "Hauna login"]);
    exit;
}
if ($name === '') {
    echo json_encode(["success" => false, "error" => "Weka jina la group"]);
    exit;
}

// Tumia table sahihi groups_table
$stmt = $conn->prepare("INSERT INTO groups_table (name, description, created_by) VALUES (?, ?, ?)");
$stmt->bind_param('ssi', $name, $desc, $user_id);

if ($stmt->execute()) {
    $group_id = $stmt->insert_id;
    $stmt->close();

    // Muundaji aingizwe kwenye group_members
    $stmt2 = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
    $stmt2->bind_param('ii', $group_id, $user_id);
    $stmt2->execute();
    $stmt2->close();

    echo json_encode(["success" => true, "group_id" => $group_id]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}
$conn->close();
?>

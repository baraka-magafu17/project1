<?php
header('Content-Type: application/json');
session_start();
require_once 'db_config.php';

$user_id = $_SESSION['user_id'] ?? null;
$group_id = intval($_POST['group_id'] ?? 0);

if (!$user_id) {
    echo json_encode(["success" => false, "error" => "Hauna login"]);
    exit;
}
if ($group_id <= 0) {
    echo json_encode(["success" => false, "error" => "ID ya group sio sahihi"]);
    exit;
}

// 🔸 Kagua kama group lipo kwenye groups_table
$stmt = $conn->prepare("SELECT group_id FROM groups_table WHERE group_id = ? LIMIT 1");
$stmt->bind_param('i', $group_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    echo json_encode(["success" => false, "error" => "Group halipo"]);
    exit;
}
$stmt->close();

// Muunganishe
$stmt2 = $conn->prepare("INSERT IGNORE INTO group_members (group_id, user_id) VALUES (?, ?)");
$stmt2->bind_param('ii', $group_id, $user_id);
if ($stmt2->execute()) {
    $affected = $stmt2->affected_rows;
    $stmt2->close();
    if ($affected > 0) {
        echo json_encode(["success" => true, "joined" => true]);
    } else {
        echo json_encode(["success" => true, "joined" => false, "message" => "Ulishajiunga tayari"]);
    }
} else {
    echo json_encode(["success" => false, "error" => $stmt2->error]);
}
$conn->close();
?>

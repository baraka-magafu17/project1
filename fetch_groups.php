<?php
header('Content-Type: application/json');
session_start();
require_once 'db_config.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["success" => false, "error" => "Hauna login"]);
    exit;
}

$sql = "SELECT g.group_id, g.name,
        (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.group_id) AS member_count
        FROM groups_table g
        JOIN group_members m ON g.group_id = m.group_id
        WHERE m.user_id = ?
        ORDER BY g.group_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$groups = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(["success" => true, "groups" => $groups]);
$conn->close();
?>

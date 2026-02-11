<?php
// fetch_messages.php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['error'=>'Not authenticated']); exit; }
$group_id = intval($_GET['group_id'] ?? 0);
$limit = intval($_GET['limit'] ?? 50);
if ($group_id <= 0) { echo json_encode(['error'=>'Invalid group']); exit; }

// permission check
$chk = $conn->prepare("SELECT 1 FROM group_members WHERE group_id = ? AND user_id = ?");
$chk->bind_param('ii', $group_id, $_SESSION['user_id']);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) { echo json_encode(['error'=>'Not a group member']); exit; }
$chk->close();

$stmt = $conn->prepare("SELECT m.message_id, m.content, m.sender_id, u.name as sender_name, m.sent_time FROM messages m LEFT JOIN users u ON m.sender_id = u.user_id WHERE m.group_id = ? ORDER BY m.sent_time DESC LIMIT ?");
$stmt->bind_param('ii', $group_id, $limit);
$stmt->execute();
$res = $stmt->get_result();
$messages = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success'=>true, 'messages'=>array_reverse($messages)]); // reverse so oldest first
$stmt->close();
$conn->close();

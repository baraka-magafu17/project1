<?php
// send_message.php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Use POST']);
    exit;
}

$group_id = intval($_POST['group_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($group_id <= 0 || $content === '') {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$sender = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO messages (content, sender_id, group_id) VALUES (?, ?, ?)");
$stmt->bind_param('sii', $content, $sender, $group_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message_id' => $stmt->insert_id]);
} else {
    echo json_encode(['error' => 'Could not send message']);
}
$stmt->close();
$conn->close();

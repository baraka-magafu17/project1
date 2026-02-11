<?php
// fetch_files.php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['error'=>'Not authenticated']); exit; }
$group_id = intval($_GET['group_id'] ?? 0);
if ($group_id <= 0) { echo json_encode(['error'=>'Invalid group']); exit; }

// simple permission check: ensure user is member
$chk = $conn->prepare("SELECT 1 FROM group_members WHERE group_id = ? AND user_id = ?");
$chk->bind_param('ii', $group_id, $_SESSION['user_id']);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) { echo json_encode(['error'=>'Not a group member']); exit; }
$chk->close();

$stmt = $conn->prepare("SELECT file_id, file_name, original_name, uploaded_by, upload_date, file_size, mime_type FROM files WHERE group_id = ? ORDER BY upload_date DESC");
$stmt->bind_param('i', $group_id);
$stmt->execute();
$res = $stmt->get_result();
$files = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success'=>true, 'files'=>$files]);
$stmt->close();
$conn->close();

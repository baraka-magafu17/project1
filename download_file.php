<?php
// download_file.php
session_start();
require_once 'db_config.php';

$file_id = intval($_GET['file_id'] ?? 0);
if ($file_id <= 0) { http_response_code(400); echo 'Invalid file'; exit; }

// fetch file record
$stmt = $conn->prepare("SELECT file_name, original_name, group_id FROM files WHERE file_id = ?");
$stmt->bind_param('i', $file_id);
$stmt->execute();
$stmt->bind_result($file_name, $original_name, $group_id);
if (!$stmt->fetch()) { http_response_code(404); echo 'Not found'; exit; }
$stmt->close();

// permission check: is user member?
$chk = $conn->prepare("SELECT 1 FROM group_members WHERE group_id = ? AND user_id = ?");
$chk->bind_param('ii', $group_id, $_SESSION['user_id'] ?? 0);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) { http_response_code(403); echo 'Forbidden'; exit; }
$chk->close();

$path = __DIR__ . '/uploads/' . $group_id . '/' . $file_name;
if (!file_exists($path)) { http_response_code(404); echo 'File missing'; exit; }

$mime = mime_content_type($path);
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($original_name) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;

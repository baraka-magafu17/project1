<?php
// upload_file.php
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
if ($group_id <= 0) {
    echo json_encode(['error' => 'Invalid group']);
    exit;
}
if (!isset($_FILES['file'])) {
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$maxSize = 50 * 1024 * 1024; // 50MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Upload error: ' . $file['error']]);
    exit;
}
if ($file['size'] > $maxSize) {
    echo json_encode(['error' => 'File too large. Max 50MB']);
    exit;
}

// optional: restrict file types (example allow common docs/images/pdf)
$allowed = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'image/png',
    'image/jpeg',
    'image/gif',
    'text/plain',
    'application/zip'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

// Allow if unknown mime (skip strict check) or is in allowed
// If you want strict: uncomment check below
// if (!in_array($mime, $allowed)) { echo json_encode(['error'=>'File type not allowed']); exit; }

$uploadsDir = __DIR__ . '/uploads/' . $group_id . '/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$storedName = time() . '_' . bin2hex(random_bytes(6)) . ($ext ? '.' . $ext : '');
$target = $uploadsDir . $storedName;

if (move_uploaded_file($file['tmp_name'], $target)) {
    $original = $file['name'];
    $size = $file['size'];
    $uploaded_by = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO files (file_name, original_name, uploaded_by, group_id, file_size, mime_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssiiis', $storedName, $original, $uploaded_by, $group_id, $size, $mime);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'file_id' => $stmt->insert_id, 'stored_name' => $storedName]);
    } else {
        // cleanup file if DB fails
        unlink($target);
        echo json_encode(['error' => 'DB error saving file record']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Failed to move uploaded file']);
}
$conn->close();

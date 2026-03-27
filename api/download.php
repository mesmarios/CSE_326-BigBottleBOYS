<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

require_once '../database/db.php';

$userId = (int)$_SESSION['user_id'];
$appId  = (int)($_GET['app_id'] ?? 0);
$type   = $_GET['type'] ?? '';
$idx    = (int)($_GET['idx'] ?? 0);

if (!$appId || !$type) {
    http_response_code(400);
    exit('Bad request');
}

// Fetch the application – must belong to this user
$stmt = $pdo->prepare("
    SELECT form_data FROM candidate_applications
    WHERE id = ? AND candidate_id = ?
");
$stmt->execute([$appId, $userId]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(403);
    exit('Forbidden');
}

$fd = $row['form_data'] ? json_decode($row['form_data'], true) : [];

$storedPath = null;
if ($type === 'cv')  $storedPath = $fd['cv_path'] ?? null;
elseif ($type === 'cl') $storedPath = $fd['cl_path'] ?? null;
elseif ($type === 'sup') {
    $paths = $fd['sup_paths'] ?? [];
    $storedPath = $paths[$idx] ?? null;
}

if (!$storedPath) {
    http_response_code(404);
    exit('File not found');
}

// Build absolute path – storedPath is relative to project root
$absPath = dirname(__DIR__) . '/' . $storedPath;

// Prevent path traversal
$realPath = realpath($absPath);
$uploadRoot = realpath(dirname(__DIR__) . '/uploads/');
if (!$realPath || !$uploadRoot || strpos($realPath, $uploadRoot) !== 0) {
    http_response_code(403);
    exit('Forbidden');
}

if (!is_file($realPath)) {
    http_response_code(404);
    exit('File not found');
}

$ext      = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
$mimeMap  = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];
$mime = $mimeMap[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($realPath) . '"');
header('Content-Length: ' . filesize($realPath));
header('Cache-Control: private, max-age=3600');
readfile($realPath);
exit;

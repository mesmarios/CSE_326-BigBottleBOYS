<?php
// API guide (EL/EN):
// Secure file download endpoint with ownership and safe-path checks.
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
    SELECT app_cv_path, app_cl_path,
           app_sup_path_1, app_sup_path_2, app_sup_path_3, app_sup_path_4, app_sup_path_5
    FROM candidate_applications
    WHERE id = ? AND candidate_id = ?
");
// Candidate ownership check before serving any stored file path.
$stmt->execute([$appId, $userId]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(403);
    exit('Forbidden');
}

$storedPath = null;
if ($type === 'cv') {
    $storedPath = $row['app_cv_path'] ?? null;
} elseif ($type === 'cl') {
    $storedPath = $row['app_cl_path'] ?? null;
} elseif ($type === 'sup') {
    $supCols = [
        $row['app_sup_path_1'],
        $row['app_sup_path_2'],
        $row['app_sup_path_3'],
        $row['app_sup_path_4'],
        $row['app_sup_path_5'],
    ];
    // Build flat array of non-empty paths, then pick by index
    $supPaths = array_values(array_filter($supCols));
    $storedPath = $supPaths[$idx] ?? null;
}

if (!$storedPath) {
    http_response_code(404);
    exit('File not found');
}

// Build absolute path – storedPath is relative to project root
$absPath = dirname(__DIR__) . '/' . $storedPath;

// Prevent path traversal
$realPath   = realpath($absPath);
$uploadRoot = realpath(dirname(__DIR__) . '/uploads/');
// Path traversal defense: real file must resolve inside uploads root.
if (!$realPath || !$uploadRoot || strpos($realPath, $uploadRoot) !== 0) {
    http_response_code(403);
    exit('Forbidden');
}

if (!is_file($realPath)) {
    http_response_code(404);
    exit('File not found');
}

$ext     = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
$mimeMap = [
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

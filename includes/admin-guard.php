<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPath = ltrim(str_replace('\\', '/', (string)($_SERVER['PHP_SELF'] ?? 'modules/admin/index.php')), '/');
$projectName = basename(dirname(__DIR__));
$projectPrefix = $projectName . '/';

if (str_starts_with($currentPath, $projectPrefix)) {
    $currentPath = substr($currentPath, strlen($projectPrefix));
}

$loginUrl = '../../login.php?admin=1&redirect=' . urlencode($currentPath);

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $loginUrl);
    exit;
}

if (($_SESSION['role'] ?? null) !== 'admin') {
    $_SESSION['auth_error'] = 'Χρειάζεστε λογαριασμό διαχειριστή για πρόσβαση στο Admin UI.';
    header('Location: ' . $loginUrl);
    exit;
}

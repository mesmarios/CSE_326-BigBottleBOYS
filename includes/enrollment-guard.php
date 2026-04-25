<?php
declare(strict_types=1);

require_once __DIR__ . '/role-access.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPath = ltrim(str_replace('\\', '/', (string)($_SERVER['PHP_SELF'] ?? 'modules/enrollmentModule/index.php')), '/');
$projectName = basename(dirname(__DIR__));
$projectPrefix = $projectName . '/';

if (str_starts_with($currentPath, $projectPrefix)) {
    $currentPath = substr($currentPath, strlen($projectPrefix));
}

$loginUrl = '../../login.php?module=enrollment&redirect=' . urlencode($currentPath);

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $loginUrl);
    exit;
}

$currentRole = normalizeAppRole((string)($_SESSION['role'] ?? ''));
$_SESSION['role'] = $currentRole;

if (!roleCanAccessModule($currentRole, 'enrollment')) {
    $_SESSION['auth_error'] = 'Ο λογαριασμός σας δεν έχει πρόσβαση στο Enrollment Module.';
    header('Location: ../../' . defaultDashboardPathForRole($currentRole));
    exit;
}

if (($enrollmentRequireManager ?? false) && !roleHasEnrollmentManagerAccess($currentRole)) {
    $_SESSION['enrollment_flash'] = [
        'type' => 'warning',
        'message' => 'Η ενότητα αυτή είναι διαθέσιμη μόνο σε Admin και HR Manager.',
    ];
    header('Location: index.php');
    exit;
}


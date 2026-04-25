<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/role-access.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$role = normalizeAppRole((string)($_SESSION['role'] ?? ''));
$_SESSION['role'] = $role;

header('Location: ../' . defaultDashboardPathForRole($role));
exit;


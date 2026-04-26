<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $enrollment_allowed must be defined by the including page before this file is required.
if (!isset($enrollment_allowed)) {
    $enrollment_allowed = ['admin', 'hr', 'ee_hired'];
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$_enrollment_role = (string)($_SESSION['role'] ?? '');

if (!in_array($_enrollment_role, $enrollment_allowed, true)) {
    $_SESSION['auth_error'] = 'Δεν έχετε πρόσβαση σε αυτή τη σελίδα.';
    header('Location: ../login.php');
    exit;
}

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

if (!function_exists('enrollResolveAvatarSrc')) {
    function enrollResolveAvatarSrc(PDO $pdo, int $userId): string
    {
        $fallback = '../assets/images/avatar.png';
        if ($userId <= 0) {
            return $fallback;
        }
        try {
            $stmt = $pdo->prepare('SELECT profilepic, profilepic_mime FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $row = $stmt->fetch();
            if (is_array($row) && !empty($row['profilepic'])) {
                $mime = in_array((string)($row['profilepic_mime'] ?? ''), ['image/jpeg','image/png','image/gif','image/webp'], true)
                    ? (string)$row['profilepic_mime'] : 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode((string)$row['profilepic']);
            }
        } catch (Throwable $e) {}
        $matches = glob(dirname(__DIR__, 2) . '/uploads/profile_pics/user_' . $userId . '.*');
        if (is_array($matches) && $matches !== []) {
            return '../uploads/profile_pics/' . rawurlencode(basename($matches[0])) . '?v=' . (int)@filemtime($matches[0]);
        }
        return $fallback;
    }
}

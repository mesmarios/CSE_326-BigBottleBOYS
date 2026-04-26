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
            $stmt = $pdo->prepare('SELECT profilepic_path FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $row = $stmt->fetch();
            $storedPath = ltrim(str_replace('\\', '/', (string)($row['profilepic_path'] ?? '')), '/');
            if ($storedPath !== '' && str_starts_with($storedPath, 'uploads/profile_pics/')) {
                $avatarBase = realpath(dirname(__DIR__, 2) . '/uploads/profile_pics');
                $avatarAbs = realpath(dirname(__DIR__, 2) . '/' . $storedPath);
                if ($avatarBase !== false && $avatarAbs !== false && is_file($avatarAbs)
                    && strpos($avatarAbs, $avatarBase . DIRECTORY_SEPARATOR) === 0) {
                    $encodedPath = implode('/', array_map('rawurlencode', explode('/', $storedPath)));
                    return '../' . $encodedPath . '?v=' . ((int)@filemtime($avatarAbs) ?: time());
                }
            }
        } catch (Throwable $e) {}
        $matches = glob(dirname(__DIR__, 2) . '/uploads/profile_pics/user_' . $userId . '.*');
        if (is_array($matches) && $matches !== []) {
            return '../uploads/profile_pics/' . rawurlencode(basename($matches[0])) . '?v=' . (int)@filemtime($matches[0]);
        }
        return $fallback;
    }
}

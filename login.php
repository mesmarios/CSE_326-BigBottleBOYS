<?php
session_start();
require_once 'database/db.php';
require_once __DIR__ . '/includes/role-access.php';

function sanitizeLocalRedirect(?string $target): ?string
{
    if ($target === null) {
        return null;
    }

    $target = trim($target);
    if ($target === '' || str_contains($target, '://') || str_starts_with($target, '//')) {
        return null;
    }

    $target = ltrim($target, '/');
    return $target !== '' ? $target : null;
}

$errors     = [];
$registered = isset($_GET['registered']) && $_GET['registered'] == 1;
$legacyAdminFlag = (isset($_GET['admin']) && $_GET['admin'] === '1')
    || (isset($_POST['require_admin']) && $_POST['require_admin'] === '1');
$requestedModule = normalizeRequestedModule(
    $_POST['requested_module'] ?? $_GET['module'] ?? ($legacyAdminFlag ? 'admin' : null)
);
$redirectTo = sanitizeLocalRedirect($_POST['redirect_to'] ?? $_GET['redirect'] ?? null);
if ($redirectTo !== null) {
    if (str_starts_with($redirectTo, 'modules/admin/')) {
        $requestedModule = 'admin';
    } elseif (str_starts_with($redirectTo, 'modules/enrollmentModule/')) {
        $requestedModule = 'enrollment';
    } elseif (str_starts_with($redirectTo, 'modules/recruitmentModule/')) {
        $requestedModule = 'recruitment';
    }
}

$requestedModuleLabel = $requestedModule ? moduleLabel($requestedModule) : 'Σύστημα';
$requestedModuleSubtitle = match ($requestedModule) {
    'admin' => 'Σύνδεση διαχειριστή για το Admin Module.',
    'enrollment' => 'Σύνδεση για πρόσβαση στο Enrollment Module.',
    'recruitment' => 'Σύνδεση για πρόσβαση στο Recruitment Module.',
    default => 'Καλώς ήρθατε πίσω.',
};

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    $sessionRole = normalizeAppRole((string)$_SESSION['role']);
    $_SESSION['role'] = $sessionRole;

    if ($redirectTo !== null) {
        if (
            (str_starts_with($redirectTo, 'modules/admin/') && !roleCanAccessModule($sessionRole, 'admin'))
            || (str_starts_with($redirectTo, 'modules/recruitmentModule/') && !roleCanAccessModule($sessionRole, 'recruitment'))
            || (str_starts_with($redirectTo, 'modules/enrollmentModule/') && !roleCanAccessModule($sessionRole, 'enrollment'))
        ) {
            $redirectTo = null;
        }
    }

    header('Location: ' . ($redirectTo ?? resolveDashboardPathForRole($sessionRole, $requestedModule)));
    exit;
}

if (isset($_SESSION['auth_error'])) {
    $errors[] = $_SESSION['auth_error'];
    unset($_SESSION['auth_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $postRequestedModule = normalizeRequestedModule($_POST['requested_module'] ?? ($legacyAdminFlag ? 'admin' : null));
    $requestedModule = $postRequestedModule ?? $requestedModule;

    if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Λανθασμένα στοιχεία σύνδεσης.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :e');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $userRole = normalizeAppRole((string)($user['role'] ?? 'candidate'));

            if ($requestedModule !== null && !roleCanAccessModule($userRole, $requestedModule)) {
                $errors[] = 'Ο λογαριασμός σας δεν έχει πρόσβαση στο ' . moduleLabel($requestedModule) . '.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['role']       = $userRole;
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name']  = $user['last_name'];
                $_SESSION['email']      = $user['email'];

                $target = $redirectTo;
                if ($target !== null) {
                    if (
                        (str_starts_with($target, 'modules/admin/') && !roleCanAccessModule($userRole, 'admin'))
                        || (str_starts_with($target, 'modules/recruitmentModule/') && !roleCanAccessModule($userRole, 'recruitment'))
                        || (str_starts_with($target, 'modules/enrollmentModule/') && !roleCanAccessModule($userRole, 'enrollment'))
                    ) {
                        $target = null;
                    }
                }

                header('Location: ' . ($target ?? resolveDashboardPathForRole($userRole, $requestedModule)));
                exit;
            }
        } else {
            $errors[] = 'Λανθασμένα στοιχεία σύνδεσης.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Σύνδεση — Σύστημα Διαχείρισης ΕΕ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <?php $authCssVersion = @filemtime(__DIR__ . '/authent.css') ?: time(); ?>
    <link href="authent.css?v=<?= $authCssVersion ?>" rel="stylesheet">
</head>
<body class="auth-page">

<a href="index.php" class="auth-home-btn" aria-label="Επιστροφή στην αρχική">
    <i class="bi bi-house-door-fill"></i>Αρχική
</a>

<div class="auth-wrapper">

    <!-- Left Panel (ίδιο με register) -->
    <div class="auth-left">
        <div class="auth-left-logo">
            <img src="assets/images/17780_100tepak-logo.png" alt="ΤΕΠΑΚ Logo">
        </div>
        <header class="auth-left-content">
            <div class="auth-left-stars">
                <span class="stars">★★★★★</span>
                <span>5.0 · από 200+ χρήστες</span>
            </div>
            <h1>Διαχείριση<br>Ειδικών Επιστημόνων<br>ΤΕΠΑΚ</h1>
            <p>Δημιουργήστε λογαριασμό και αποκτήστε πρόσβαση στο σύστημα υποβολής αιτήσεων.</p>
        </header>
    </div>

    <!-- Right Panel -->
    <div class="auth-right auth-right-login">
        <div class="auth-logo">
            <img src="assets/images/17780_100tepak-logo.png" alt="ΤΕΠΑΚ">
        </div>
        <h2>Σύνδεση</h2>
        <p class="auth-subtitle">
            <?= htmlspecialchars($requestedModuleSubtitle, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <?php if ($requestedModule !== null): ?>
            <div class="auth-success" style="background:#eef6ff;color:#174ea6;border-color:#cfe2ff;">
                <i class="bi bi-grid-1x2-fill me-2"></i>Επιλεγμένο module: <?= htmlspecialchars($requestedModuleLabel, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($registered): ?>
            <div class="auth-success">
                <i class="bi bi-check-circle-fill me-2"></i>Ο λογαριασμός σας δημιουργήθηκε! Συνδεθείτε τώρα.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <ul class="auth-errors">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <input type="hidden" name="require_admin" value="<?= $requestedModule === 'admin' ? '1' : '0' ?>">
            <input type="hidden" name="requested_module" value="<?= htmlspecialchars($requestedModule ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirectTo ?? '') ?>">
            <div class="mb-4">
                <label class="form-label login-label">Email <span class="required">*</span></label>
                <input type="email" name="email" class="form-control form-control-login"
                       placeholder="email@παράδειγμα.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="mb-5">
                <label class="form-label login-label">Κωδικός <span class="required">*</span></label>
                <input type="password" name="password" class="form-control form-control-login"
                       placeholder="Εισάγετε τον κωδικό σας">
            </div>

            <button type="submit" name="login" class="btn-auth btn-auth-login mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                <?= htmlspecialchars($requestedModule ? 'Σύνδεση στο ' . $requestedModuleLabel : 'Σύνδεση Χρήστη', ENT_QUOTES, 'UTF-8') ?>
            </button>

            <?php if ($requestedModule !== 'admin'): ?>
                <a href="login.php?module=admin" class="btn-auth-secondary btn-auth-secondary-login d-inline-flex justify-content-center align-items-center text-decoration-none">
                    <i class="bi bi-shield-lock me-2"></i>Σύνδεση Διαχειριστή
                </a>
            <?php endif; ?>
        </form>

        <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center" style="font-size:.88rem;">
            <a href="login.php?module=recruitment" class="text-decoration-none">Recruitment</a>
            <span class="text-secondary">|</span>
            <a href="login.php?module=enrollment" class="text-decoration-none">Enrollment</a>
            <span class="text-secondary">|</span>
            <a href="login.php?module=admin" class="text-decoration-none">Admin</a>
        </div>

        <p class="auth-login-link">
            Δεν έχεις λογαριασμό; <a href="register.php">Εγγραφή</a>
        </p>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

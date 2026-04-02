<?php
session_start();
require_once 'database/db.php';

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
$requireAdmin = (isset($_GET['admin']) && $_GET['admin'] === '1')
    || (isset($_POST['require_admin']) && $_POST['require_admin'] === '1');
$redirectTo = sanitizeLocalRedirect($_POST['redirect_to'] ?? $_GET['redirect'] ?? null);

if (isset($_SESSION['auth_error'])) {
    $errors[] = $_SESSION['auth_error'];
    unset($_SESSION['auth_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $isAdmin  = isset($_POST['go_admin']) || $requireAdmin;

    if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Λανθασμένα στοιχεία σύνδεσης.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :e');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($isAdmin && $user['role'] !== 'admin') {
                $errors[] = 'Δεν έχετε δικαιώματα διαχειριστή.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['role']       = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name']  = $user['last_name'];
                $_SESSION['email']      = $user['email'];

                $defaultTarget = $user['role'] === 'admin'
                    ? 'modules/admin/index.php'
                    : 'modules/recruitmentModule/index.php';
                $target = $redirectTo;

                if ($user['role'] !== 'admin' && $target !== null && str_starts_with($target, 'modules/admin/')) {
                    $target = null;
                }

                header('Location: ' . ($target ?? $defaultTarget));
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
            <?= $requireAdmin ? 'Σύνδεση διαχειριστή με έγκυρα στοιχεία.' : 'Καλώς ήρθατε πίσω.' ?>
        </p>

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
            <input type="hidden" name="require_admin" value="<?= $requireAdmin ? '1' : '0' ?>">
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

            <?php if ($requireAdmin): ?>
                <button type="submit" name="go_admin" class="btn-auth btn-auth-login mb-3">
                    <i class="bi bi-shield-lock me-2"></i>Σύνδεση Διαχειριστή
                </button>
            <?php else: ?>
                <button type="submit" name="login" class="btn-auth btn-auth-login mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Σύνδεση Χρήστη
                </button>
                <button type="submit" name="go_admin" class="btn-auth-secondary btn-auth-secondary-login">
                    <i class="bi bi-shield-lock me-2"></i>Σύνδεση Διαχειριστή
                </button>
            <?php endif; ?>
        </form>

        <p class="auth-login-link">
            Δεν έχεις λογαριασμό; <a href="register.php">Εγγραφή</a>
        </p>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

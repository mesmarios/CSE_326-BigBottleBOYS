<?php
// Auth flow (EL/EN): validate credentials, regenerate session id, then redirect by role.
session_start();
require_once 'database/db.php';
require_once 'includes/admin-branding.php';

$brandingContext = adminGetBrandingContext($pdo, 'assets/images');
$loginFavicon = $brandingContext['favicon'] ?? null;

function defaultDashboardForRole(string $role): string
{
    return match ($role) {
        'admin', 'hr'              => 'module-select.php',
        'evaluator', 'candidate'   => 'modules/recruitmentModule/index.php',
        'ee_hired'                 => 'enrollment/dashboard.php',
        default                    => 'login.php',
    };
}

$errors     = [];
$registered = isset($_GET['registered']) && $_GET['registered'] == 1;

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: ' . defaultDashboardForRole((string)$_SESSION['role']));
    exit;
}

if (isset($_SESSION['auth_error'])) {
    $errors[] = $_SESSION['auth_error'];
    unset($_SESSION['auth_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Input sanity check before DB lookup.
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Λανθασμένα στοιχεία σύνδεσης.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :e');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Session hardening: rotate ID after successful authentication.
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name']  = $user['last_name'];
            $_SESSION['email']      = $user['email'];

            header('Location: ' . defaultDashboardForRole((string)$user['role']));
            exit;
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
    <?php if (!empty($loginFavicon)): ?>
    <link rel="icon" href="<?= htmlspecialchars($loginFavicon, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <?php $authCssVersion = @filemtime(__DIR__ . '/authent.css') ?: time(); ?>
    <link href="authent.css?v=<?= $authCssVersion ?>" rel="stylesheet">
</head>
<body class="auth-page">

<div class="auth-wrapper">

    <!-- Left Panel -->
    <div class="auth-left">
        <div class="auth-left-logo">
            <img src="assets/images/17780_100tepak-logo.png" alt="ΤΕΠΑΚ Logo">
        </div>
        <header class="auth-left-content">
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
        <p class="auth-subtitle">Καλώς ήρθατε πίσω.</p>

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

            <button type="submit" class="btn-auth btn-auth-login mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Σύνδεση
            </button>
        </form>

        <p class="auth-login-link">
            Δεν έχεις λογαριασμό; <a href="register.php">Εγγραφή</a>
        </p>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

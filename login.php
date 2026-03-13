<?php
session_start();
require_once 'database/db.php';

$errors = [];
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $isAdminLogin = isset($_POST['go_admin']);

    if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Παρακαλώ εισάγετε έγκυρο email.';
    }

    if ($password === '') {
        $errors[] = 'Παρακαλώ εισάγετε κωδικό.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "SELECT
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.password_hash,
                u.status,
                GROUP_CONCAT(LOWER(REPLACE(r.name, ' ', '_')) SEPARATOR ',') AS role_keys
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE u.email = :email
            GROUP BY u.id
            LIMIT 1"
        );
        $stmt->execute([':email' => $emailInput]);
        $user = $stmt->fetch();

        $isValidPassword = $user && password_verify($password, (string)$user['password_hash']);

        if (!$isValidPassword) {
            $errors[] = 'Λάθος email ή κωδικός.';
        } elseif (($user['status'] ?? 'inactive') !== 'active') {
            $errors[] = 'Ο λογαριασμός σας δεν είναι ενεργός.';
        } else {
            $roleKeys = array_filter(explode(',', (string)($user['role_keys'] ?? '')));
            $isAdminUser = in_array('admin', $roleKeys, true);

            if ($isAdminLogin && !$isAdminUser) {
                $errors[] = 'Δεν έχετε δικαίωμα πρόσβασης στο Admin panel.';
            } else {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_email'] = (string)$user['email'];
                $_SESSION['user_name'] = trim((string)$user['first_name'] . ' ' . (string)$user['last_name']);
                $_SESSION['user_roles'] = $roleKeys;
                $_SESSION['is_admin'] = $isAdminUser;

                if ($isAdminLogin) {
                    header('Location: modules/admin/index.php');
                    exit;
                }

                header('Location: modules/recruitmentModule/index.php');
                exit;
            }
        }
    }
}

$registered = isset($_GET['registered']) && $_GET['registered'] == 1;
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Σύνδεση — Σύστημα Διαχείρισης ΕΕ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="authent.css" rel="stylesheet">
</head>
<body class="auth-page">

<div class="auth-wrapper">

    <!-- Left Panel (ίδιο με register) -->
    <div class="auth-left">
        <div class="auth-left-logo">
            <img src="assets/images/17780_100tepak-logo.png" alt="ΤΕΠΑΚ Logo">
            <img src="assets/images/netaniaxou.jpg" alt="ΤΕΠΑΚ" class="auth-left-photo">
        </div>
        <div class="auth-left-content">
            <div class="auth-left-stars">
                <span class="stars">★★★★★</span>
                <span>5.0 · από 200+ χρήστες</span>
            </div>
            <h1>Διαχείριση<br>Ειδικών Επιστημόνων<br>ΤΕΠΑΚ</h1>
            <p>Δημιουργήστε λογαριασμό και αποκτήστε πρόσβαση στο σύστημα υποβολής αιτήσεων.</p>
        </div>
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
                       value="<?= htmlspecialchars($emailInput) ?>">
            </div>

            <div class="mb-5">
                <label class="form-label login-label">Κωδικός <span class="required">*</span></label>
                <input type="password" name="password" class="form-control form-control-login"
                       placeholder="Εισάγετε τον κωδικό σας">
            </div>

            <button type="submit" name="login" class="btn-auth btn-auth-login mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Σύνδεση Χρήστη
            </button>
            <button type="submit" name="go_admin" class="btn-auth-secondary btn-auth-secondary-login">
                <i class="bi bi-shield-lock me-2"></i>Σύνδεση Διαχειριστή
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

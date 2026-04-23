<?php
require_once 'database/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    $email      = trim($_POST['email']      ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $address    = trim($_POST['address']    ?? '');
    $password   = $_POST['password']        ?? '';
    $confirm    = $_POST['confirm']         ?? '';

    // Validation
    if ($first_name === '') $errors[] = 'Το όνομα είναι υποχρεωτικό.';
    if ($last_name  === '') $errors[] = 'Το επώνυμο είναι υποχρεωτικό.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Μη έγκυρο email.';
    if ($phone !== '') {
        $phoneClean = preg_replace('/[\s\-]/', '', $phone);
        if (!preg_match('/^\+357\d{8}$/', $phoneClean)) {
            $errors[] = 'Το τηλέφωνο πρέπει να αρχίζει με +357 και να ακολουθούν 8 ψηφία (π.χ. +35799123456).';
        }
    }
    if (strlen($password) < 8) $errors[] = 'Κωδικός τουλάχιστον 8 χαρακτήρες.';
    if ($password !== $confirm) $errors[] = 'Οι κωδικοί δεν ταιριάζουν.';

    // Έλεγχος αν υπάρχει ήδη το email
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e');
        $stmt->execute([':e' => $email]);
        if ($stmt->fetch()) $errors[] = 'Το email χρησιμοποιείται ήδη.';
    }

    // Εγγραφή — role DEFAULT 'user' αυτόματα από τη βάση
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Auto-generate a unique username from first_name.last_name
        $baseUsername = strtolower(
            preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $first_name))
            . '.'
            . preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $last_name))
        );
        if ($baseUsername === '.') {
            $baseUsername = 'user';
        }
        $username = $baseUsername;
        $suffix = 1;
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE username = :u');
        while (true) {
            $checkStmt->execute([':u' => $username]);
            if (!$checkStmt->fetch()) break;
            $username = $baseUsername . $suffix++;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO users (username, first_name, last_name, email, phone, address, password_hash)
             VALUES (:u, :fn, :ln, :e, :ph, :ad, :h)'
        );
        $stmt->execute([
            ':u'  => $username,
            ':fn' => $first_name,
            ':ln' => $last_name,
            ':e'  => $email,
            ':ph' => $phone ?: null,
            ':ad' => $address ?: null,
            ':h'  => $hash,
        ]);
        header('Location: login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Εγγραφή — Σύστημα Διαχείρισης ΕΕ</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom auth styles -->
    <?php $authCssVersion = @filemtime(__DIR__ . '/authent.css') ?: time(); ?>
    <link href="authent.css?v=<?= $authCssVersion ?>" rel="stylesheet">
</head>
<body class="auth-page">

<div class="auth-wrapper">

    <!-- ── Left Panel ── -->
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

    <!-- ── Right Panel ── -->
    <div class="auth-right">
        <div class="auth-logo">
            <img src="assets/images/17780_100tepak-logo.png" alt="ΤΕΠΑΚ">
        </div>
        <h2>Εγγραφή</h2>
        <p class="auth-subtitle">Δημιουργήστε τον λογαριασμό σας.</p>

        <?php if (!empty($errors)): ?>
            <ul class="auth-errors">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <!-- Όνομα + Επώνυμο -->
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label">Όνομα <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-control"
                           placeholder="π.χ. Γιάννης"
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                </div>
                <div class="col-6">
                    <label class="form-label">Επώνυμο <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-control"
                           placeholder="π.χ. Παπαδόπουλος"
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                </div>
            </div>

            <!-- Email -->
            <div class="mb-2">
                <label class="form-label">Email <span class="required">*</span></label>
                <input type="email" name="email" class="form-control"
                       placeholder="email@παράδειγμα.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <!-- Τηλέφωνο -->
            <div class="mb-2">
                <label class="form-label">Τηλέφωνο</label>
                <input type="tel" name="phone" class="form-control"
                       placeholder="+35799123456"
                       pattern="\+357[0-9]{8}"
                       title="Αρχίστε με +357 και συμπληρώστε 8 ψηφία (π.χ. +35799123456)"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                <div class="form-hint">Προαιρετικό · Μορφή: +357 + 8 ψηφία (π.χ. +35799123456)</div>
            </div>

            <!-- Διεύθυνση -->
            <div class="mb-2">
                <label class="form-label">Διεύθυνση</label>
                <input type="text" name="address" class="form-control"
                       placeholder="π.χ. Λευκωσία, Κύπρος"
                       value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>

            <!-- Κωδικός -->
            <div class="mb-2">
                <label class="form-label">Κωδικός <span class="required">*</span></label>
                <input type="password" name="password" class="form-control"
                       placeholder="Δημιουργήστε κωδικό">
                <div class="form-hint">Τουλάχιστον 8 χαρακτήρες.</div>
            </div>

            <!-- Επιβεβαίωση -->
            <div class="mb-2">
                <label class="form-label">Επιβεβαίωση Κωδικού <span class="required">*</span></label>
                <input type="password" name="confirm" class="form-control"
                       placeholder="Επαναλάβετε τον κωδικό">
            </div>

            <button type="submit" class="btn-auth">
                <i class="bi bi-person-plus me-2"></i>Δημιουργία Λογαριασμού
            </button>
        </form>

        <p class="auth-login-link">
            Έχεις ήδη λογαριασμό; <a href="login.php">Σύνδεση</a>
        </p>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

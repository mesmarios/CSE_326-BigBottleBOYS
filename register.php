<?php
require_once 'database/db.php';
require_once 'includes/admin-branding.php';

$brandingContext = adminGetBrandingContext($pdo, 'assets/images');
$registerFavicon = $brandingContext['favicon'] ?? null;

$errors = [];

function isValidStrongPassword(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password) === 1
        && preg_match('/[a-z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1
        && preg_match('/[!@#$%^&*()_+\-=]/', $password) === 1;
}

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
    if (!isValidStrongPassword($password)) {
        $errors[] = 'Ο κωδικός πρέπει να καλύπτει όλες τις απαιτήσεις ασφαλείας.';
    }
    if ($password !== $confirm) $errors[] = 'Οι κωδικοί δεν ταιριάζουν.';

    // Έλεγχος αν υπάρχει ήδη το email
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e');
        $stmt->execute([':e' => $email]);
        if ($stmt->fetch()) $errors[] = 'Το email χρησιμοποιείται ήδη.';
    }

    // Εγγραφή — role DEFAULT 'candidate' αυτόματα από τη βάση
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
    <?php if (!empty($registerFavicon)): ?>
    <link rel="icon" href="<?= htmlspecialchars($registerFavicon, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom auth styles -->
    <?php $authCssVersion = @filemtime(__DIR__ . '/authent.css') ?: time(); ?>
    <link href="authent.css?v=<?= $authCssVersion ?>" rel="stylesheet">
</head>
<body class="auth-page register-page">

<div class="auth-wrapper">

    <!-- ── Left Panel ── -->
    <div class="auth-left">
        <div class="auth-left-logo">
            <img src="assets/images/17780_100tepak-logo.png" alt="ΤΕΠΑΚ Logo">
        </div>
        <header class="auth-left-content">
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

        <form method="POST" class="auth-form auth-form-register" id="registerForm">
            <!-- Όνομα + Επώνυμο -->
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label">Όνομα <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-control"
                           placeholder="π.χ. Γιάννης"
                           required
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                </div>
                <div class="col-6">
                    <label class="form-label">Επώνυμο <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-control"
                           placeholder="π.χ. Παπαδόπουλος"
                           required
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                </div>
            </div>

            <!-- Email -->
            <div class="mb-2">
                <label class="form-label">Email <span class="required">*</span></label>
                <input type="email" name="email" class="form-control"
                       placeholder="email@παράδειγμα.com"
                       required
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
                <div class="input-group">
                    <input type="password" name="password" class="form-control" id="registerPassword"
                           placeholder="Δημιουργήστε κωδικό"
                           oninput="checkPwdStrength(this.value)"
                           autocomplete="new-password"
                           required
                           aria-label="Δημιουργήστε κωδικό">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('registerPassword', this)" aria-label="Εμφάνιση κωδικού">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="mt-1" id="pwdStrengthWrap" style="visibility:hidden;">
                    <div class="progress" style="height:4px;">
                        <div class="progress-bar" id="pwdStrengthBar" style="width:0%"></div>
                    </div>
                    <small id="pwdStrengthText" class="text-secondary"></small>
                </div>
            </div>

            <!-- Επιβεβαίωση -->
            <div class="mb-2">
                <label class="form-label">Επιβεβαίωση Κωδικού <span class="required">*</span></label>
                <div class="input-group">
                    <input type="password" name="confirm" class="form-control" id="registerConfirm"
                           placeholder="Επαναλάβετε τον κωδικό"
                           autocomplete="new-password"
                           required
                           aria-label="Επαναλάβετε τον κωδικό">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('registerConfirm', this)" aria-label="Εμφάνιση επιβεβαίωσης κωδικού">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="password-requirements mb-2">
                <p class="small fw-semibold mb-2 text-secondary">Απαιτήσεις κωδικού:</p>
                <ul class="list-unstyled mb-0 small text-secondary" id="pwdReqs">
                    <li id="req-length"><i class="bi bi-circle me-2"></i>Τουλάχιστον 8 χαρακτήρες</li>
                    <li id="req-upper"><i class="bi bi-circle me-2"></i>Ένα κεφαλαίο γράμμα</li>
                    <li id="req-lower"><i class="bi bi-circle me-2"></i>Ένα πεζό γράμμα</li>
                    <li id="req-number"><i class="bi bi-circle me-2"></i>Έναν αριθμό</li>
                    <li id="req-special"><i class="bi bi-circle me-2"></i>Έναν ειδικό χαρακτήρα (!@#$%)</li>
                </ul>
                <div class="small mt-2" id="passwordMatchText" aria-live="polite"></div>
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
<script>
    function togglePwd(fieldId, btn) {
        var inp = document.getElementById(fieldId);
        var icon = btn.querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.className = 'bi bi-eye-slash';
            btn.setAttribute('aria-label', 'Απόκρυψη κωδικού');
        } else {
            inp.type = 'password';
            icon.className = 'bi bi-eye';
            btn.setAttribute('aria-label', 'Εμφάνιση κωδικού');
        }
    }

    function getPasswordScore(val) {
        return [
            val.length >= 8,
            /[A-Z]/.test(val),
            /[a-z]/.test(val),
            /[0-9]/.test(val),
            /[!@#$%^&*()_+\-=]/.test(val)
        ].filter(Boolean).length;
    }

    function checkPwdStrength(val) {
        var wrap = document.getElementById('pwdStrengthWrap');
        var bar  = document.getElementById('pwdStrengthBar');
        var txt  = document.getElementById('pwdStrengthText');
        if (!val) {
            wrap.style.visibility = 'hidden';
            resetPwdRequirements();
            updatePasswordMatch();
            return;
        }
        wrap.style.visibility = 'visible';

        var score = 0;
        var setReq = function (id, ok) {
            var el = document.getElementById(id);
            el.querySelector('i').className = ok ? 'bi bi-check-circle-fill me-2 text-success' : 'bi bi-circle me-2';
            el.className = ok ? 'text-success' : '';
            if (ok) score++;
        };

        setReq('req-length',  val.length >= 8);
        setReq('req-upper',   /[A-Z]/.test(val));
        setReq('req-lower',   /[a-z]/.test(val));
        setReq('req-number',  /[0-9]/.test(val));
        setReq('req-special', /[!@#$%^&*()_+\-=]/.test(val));

        var widths  = ['20%', '40%', '60%', '80%', '100%'];
        var colors  = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'];
        var labels  = ['Πολύ αδύναμος', 'Αδύναμος', 'Μέτριος', 'Ισχυρός', 'Πολύ ισχυρός'];

        bar.style.width = widths[score - 1] || '0%';
        bar.style.backgroundColor = colors[score - 1] || '#ef4444';
        txt.textContent = labels[score - 1] || '';
        txt.style.color = colors[score - 1] || '';
        updatePasswordMatch();
    }

    function resetPwdRequirements() {
        ['req-length', 'req-upper', 'req-lower', 'req-number', 'req-special'].forEach(function (id) {
            var el = document.getElementById(id);
            el.querySelector('i').className = 'bi bi-circle me-2';
            el.className = '';
        });
    }

    function updatePasswordMatch() {
        var pwd = document.getElementById('registerPassword').value;
        var conf = document.getElementById('registerConfirm').value;
        var matchText = document.getElementById('passwordMatchText');

        if (!conf) {
            matchText.textContent = '';
            matchText.className = 'small';
            return true;
        }

        if (pwd === conf) {
            matchText.textContent = 'Οι κωδικοί ταιριάζουν.';
            matchText.className = 'small text-success';
            return true;
        }

        matchText.textContent = 'Οι κωδικοί δεν ταιριάζουν.';
        matchText.className = 'small text-danger';
        return false;
    }

    document.getElementById('registerConfirm').addEventListener('input', updatePasswordMatch);

    document.getElementById('registerForm').addEventListener('submit', function (event) {
        var pwd = document.getElementById('registerPassword').value;
        var conf = document.getElementById('registerConfirm').value;
        checkPwdStrength(pwd);

        if (getPasswordScore(pwd) < 5 || pwd !== conf) {
            event.preventDefault();
            updatePasswordMatch();
            document.getElementById('registerPassword').focus();
        }
    });
</script>
</body>
</html>

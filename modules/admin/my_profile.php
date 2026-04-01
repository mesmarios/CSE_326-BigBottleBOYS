<?php
require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../includes/config.php';

$pdo = getDBConnection();
$userId = (int) $_SESSION['user_id'];
$errors = [];
$success = '';

function redirectWithMessage(string $message, string $type = 'success'): void
{
    header('Location: my_profile.php?msg=' . urlencode($message) . '&mtype=' . urlencode($type));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $username = trim($_POST['username'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($username === '') {
            $errors[] = 'Το username είναι υποχρεωτικό.';
        }
        if ($firstName === '') {
            $errors[] = 'Το όνομα είναι υποχρεωτικό.';
        }
        if ($lastName === '') {
            $errors[] = 'Το επώνυμο είναι υποχρεωτικό.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Το email δεν είναι έγκυρο.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE (email = :email OR username = :username) AND id <> :id');
            $stmt->execute([
                ':email' => $email,
                ':username' => $username,
                ':id' => $userId,
            ]);

            if ($stmt->fetch()) {
                $errors[] = 'Το email ή το username χρησιμοποιείται ήδη.';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET username = :username,
                     first_name = :first_name,
                     last_name = :last_name,
                     email = :email,
                     phone = :phone,
                     address = :address,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':username' => $username,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':phone' => $phone !== '' ? $phone : null,
                ':address' => $address !== '' ? $address : null,
                ':id' => $userId,
            ]);

            $_SESSION['username'] = $username;
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;

            redirectWithMessage('Τα στοιχεία του προφίλ αποθηκεύτηκαν επιτυχώς.');
        }
    }

    if ($action === 'password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $errors[] = 'Συμπληρώστε όλα τα πεδία κωδικού.';
        }
        if (strlen($newPassword) < 8) {
            $errors[] = 'Ο νέος κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Ο νέος κωδικός και η επιβεβαίωση δεν ταιριάζουν.';
        }

        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id');
        $stmt->execute([':id' => $userId]);
        $userPassword = $stmt->fetch();

        if (!$userPassword || !password_verify($currentPassword, $userPassword['password_hash'])) {
            $errors[] = 'Ο τρέχων κωδικός δεν είναι σωστός.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET password_hash = :password_hash,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                ':id' => $userId,
            ]);

            redirectWithMessage('Ο κωδικός πρόσβασης άλλαξε επιτυχώς.');
        }
    }
}

$stmt = $pdo->prepare(
    'SELECT id, username, first_name, last_name, email, phone, address, role, created_at
     FROM users
     WHERE id = :id'
);
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

$message = $_GET['msg'] ?? '';
$messageType = $_GET['mtype'] ?? 'success';

$stats = [
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'announcements' => (int) $pdo->query('SELECT COUNT(*) FROM job_announcements')->fetchColumn(),
    'departments' => (int) $pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn(),
];
?>
<!doctype html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <title>Admin | My Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/adminlte.css">
    <style>
        body { background: #f5f7fb; font-family: Arial, sans-serif; }
        .page-shell { max-width: 1180px; margin: 32px auto; padding: 0 16px; }
        .topbar, .card { background: #fff; border: 1px solid #dbe3ee; border-radius: 14px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); }
        .topbar { padding: 18px 22px; margin-bottom: 18px; display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .topbar a { text-decoration: none; font-weight: 700; color: #1d4ed8; margin-right: 14px; }
        .grid { display: grid; grid-template-columns: 320px 1fr; gap: 18px; }
        .card { padding: 22px; }
        .summary { text-align: center; }
        .avatar { width: 92px; height: 92px; border-radius: 50%; display: grid; place-items: center; background: #dbeafe; color: #1d4ed8; font-size: 2rem; font-weight: 700; margin: 0 auto 14px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 18px; }
        .stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
        .section-title { margin: 0 0 14px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 6px; font-weight: 700; }
        input, textarea { width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; }
        textarea { min-height: 110px; resize: vertical; }
        .btn { display: inline-block; padding: 12px 16px; border: 0; border-radius: 8px; cursor: pointer; font-weight: 700; text-decoration: none; }
        .btn-primary { background: #1d4ed8; color: #fff; }
        .btn-warning { background: #d97706; color: #fff; }
        .alert { padding: 14px 16px; border-radius: 10px; margin-bottom: 16px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .muted { color: #64748b; }
        @media (max-width: 900px) {
            .grid, .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="topbar">
            <div>
                <a href="index.php">Dashboard</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="manage_recruitment.php">Manage Recruitment</a>
                <a href="configure_system.php">Configure System</a>
                <a href="report.php">Reports</a>
            </div>
            <div>
                <a href="../../logout.php">Logout</a>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') === 'danger' ? 'danger' : 'success' ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid">
            <section class="card summary">
                <div class="avatar">
                    <?= htmlspecialchars(mb_strtoupper(mb_substr($user['first_name'], 0, 1, 'UTF-8') . mb_substr($user['last_name'], 0, 1, 'UTF-8'), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <h2><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="muted"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Role:</strong> <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Μέλος από:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8') ?></p>

                <div class="stats">
                    <div class="stat-box">
                        <div><strong><?= $stats['users'] ?></strong></div>
                        <div class="muted">Users</div>
                    </div>
                    <div class="stat-box">
                        <div><strong><?= $stats['announcements'] ?></strong></div>
                        <div class="muted">Announcements</div>
                    </div>
                    <div class="stat-box">
                        <div><strong><?= $stats['departments'] ?></strong></div>
                        <div class="muted">Departments</div>
                    </div>
                </div>
            </section>

            <div>
                <section class="card" style="margin-bottom: 18px;">
                    <h3 class="section-title">Στοιχεία Λογαριασμού</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="profile">
                        <div class="form-grid">
                            <div>
                                <label for="username">Username</label>
                                <input id="username" type="text" name="username" value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div>
                                <label for="email">Email</label>
                                <input id="email" type="email" name="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div>
                                <label for="first_name">Όνομα</label>
                                <input id="first_name" type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div>
                                <label for="last_name">Επώνυμο</label>
                                <input id="last_name" type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div>
                                <label for="phone">Τηλέφωνο</label>
                                <input id="phone" type="text" name="phone" value="<?= htmlspecialchars((string) ($user['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div>
                                <label for="role">Ρόλος</label>
                                <input id="role" type="text" value="<?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?>" disabled>
                            </div>
                            <div class="full">
                                <label for="address">Διεύθυνση</label>
                                <textarea id="address" name="address"><?= htmlspecialchars((string) ($user['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div class="full">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-floppy me-1"></i>Αποθήκευση Στοιχείων
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="card">
                    <h3 class="section-title">Αλλαγή Κωδικού Πρόσβασης</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="password">
                        <div class="form-grid">
                            <div>
                                <label for="current_password">Τρέχων Κωδικός</label>
                                <input id="current_password" type="password" name="current_password" required>
                            </div>
                            <div>
                                <label for="new_password">Νέος Κωδικός</label>
                                <input id="new_password" type="password" name="new_password" required>
                            </div>
                            <div>
                                <label for="confirm_password">Επιβεβαίωση Νέου Κωδικού</label>
                                <input id="confirm_password" type="password" name="confirm_password" required>
                            </div>
                            <div class="full">
                                <button class="btn btn-warning" type="submit">
                                    <i class="bi bi-lock me-1"></i>Αλλαγή Κωδικού
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</body>
</html>

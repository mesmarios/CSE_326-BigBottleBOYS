<?php
// Legacy auth/register (EL/EN): validates username/email/password and inserts user.

require_once __DIR__ . '/../includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '') {
        $errors[] = 'Το username είναι υποχρεωτικό.';
    }

    if ($email === '') {
        $errors[] = 'Το email είναι υποχρεωτικό.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Μη έγκυρη μορφή email.';
    }

    if ($password === '') {
        $errors[] = 'Το password είναι υποχρεωτικό.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Το password πρέπει να έχει τουλάχιστον 8 χαρακτήρες.';
    }

    if ($confirmPassword === '') {
        $errors[] = 'Η επιβεβαίωση password είναι υποχρεωτική.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Τα password δεν ταιριάζουν.';
    }

    if ($email !== '') {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e');
        $stmt->execute([':e' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Το email χρησιμοποιείται ήδη.';
        }
    }

    if ($username !== '') {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u');
        $stmt->execute([':u' => $username]);
        if ($stmt->fetch()) {
            $errors[] = 'Το username χρησιμοποιείται ήδη.';
        }
    }

    if ($errors === []) {
        // Password is hashed before storage.
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, role)
             VALUES (:username, :email, :password_hash, :role)'
        );
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':role' => 'user',
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
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 mb-4">Register</h1>

                    <?php if ($errors !== []): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-control"
                                value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Create Account</button>
                        <a href="login.php" class="btn btn-link">Login</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php

require_once __DIR__ . '/../includes/db.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$errors = [];
$formData = [
    'username' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['username'] = trim($_POST['username'] ?? '');
    $formData['first_name'] = trim($_POST['first_name'] ?? '');
    $formData['last_name'] = trim($_POST['last_name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($formData['username'] === '') {
        $errors[] = 'Το username είναι υποχρεωτικό.';
    }

    if ($formData['first_name'] === '') {
        $errors[] = 'Το όνομα είναι υποχρεωτικό.';
    }

    if ($formData['last_name'] === '') {
        $errors[] = 'Το επώνυμο είναι υποχρεωτικό.';
    }

    if ($formData['email'] === '') {
        $errors[] = 'Το email είναι υποχρεωτικό.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Το email δεν έχει έγκυρη μορφή.';
    }

    if ($password === '') {
        $errors[] = 'Ο κωδικός είναι υποχρεωτικός.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.';
    }

    if ($confirmPassword === '') {
        $errors[] = 'Η επιβεβαίωση κωδικού είναι υποχρεωτική.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Οι κωδικοί δεν ταιριάζουν.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e');
        $stmt->execute([':e' => $formData['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'Το email χρησιμοποιείται ήδη.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u');
        $stmt->execute([':u' => $formData['username']]);
        if ($stmt->fetch()) {
            $errors[] = 'Το username χρησιμοποιείται ήδη.';
        }
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, first_name, last_name, role)
             VALUES (:username, :email, :password_hash, :first_name, :last_name, :role)'
        );

        $stmt->execute([
            ':username' => $formData['username'],
            ':email' => $formData['email'],
            ':password_hash' => $passwordHash,
            ':first_name' => $formData['first_name'],
            ':last_name' => $formData['last_name'],
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
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        .container {
            max-width: 580px;
            margin: 48px auto;
            background: #ffffff;
            border: 1px solid #dbe3ee;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        h1 {
            margin-top: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        .errors {
            padding: 16px 20px;
            background: #fee2e2;
            border: 1px solid #fca5a5;
            border-radius: 10px;
            color: #991b1b;
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: 700;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 18px;
            border: 0;
            border-radius: 8px;
            background: #0f766e;
            color: #ffffff;
            cursor: pointer;
            font-weight: 700;
        }

        .secondary-link {
            color: #0f766e;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Register</h1>
        <p>Εγγραφή νέου χρήστη στη βασική ροή authentication του συστήματος.</p>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="grid">
                <div class="field-full">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" value="<?= e($formData['username']) ?>">
                </div>

                <div>
                    <label for="first_name">Όνομα</label>
                    <input id="first_name" type="text" name="first_name" value="<?= e($formData['first_name']) ?>">
                </div>

                <div>
                    <label for="last_name">Επώνυμο</label>
                    <input id="last_name" type="text" name="last_name" value="<?= e($formData['last_name']) ?>">
                </div>

                <div class="field-full">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="<?= e($formData['email']) ?>">
                </div>

                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password">
                </div>

                <div>
                    <label for="confirm_password">Confirm Password</label>
                    <input id="confirm_password" type="password" name="confirm_password">
                </div>
            </div>

            <button type="submit">Register</button>
        </form>

        <p><a class="secondary-link" href="login.php">Έχεις ήδη λογαριασμό; Σύνδεση</a></p>
    </main>
</body>
</html>

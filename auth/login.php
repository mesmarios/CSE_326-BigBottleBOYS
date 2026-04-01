<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if (isset($_SESSION['user_id'])) {
    header('Location: ../modules/dashboard.php');
    exit;
}

$error = '';
$email = '';
$registered = isset($_GET['registered']) && $_GET['registered'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Συμπληρώστε email και κωδικό.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $displayUsername = trim((string) ($user['username'] ?? ''));
            if ($displayUsername === '') {
                $displayUsername = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            }
            if ($displayUsername === '') {
                $displayUsername = $user['email'];
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $displayUsername;
            $_SESSION['first_name'] = $user['first_name'] ?? '';
            $_SESSION['last_name'] = $user['last_name'] ?? '';

            header('Location: ../modules/dashboard.php');
            exit;
        }

        $error = 'Λανθασμένα στοιχεία σύνδεσης.';
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        .container {
            max-width: 520px;
            margin: 48px auto;
            background: #ffffff;
            border: 1px solid #dbe3ee;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        .message {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .message-success {
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #166534;
        }

        .message-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        label {
            display: block;
            margin: 16px 0 6px;
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
            background: #1d4ed8;
            color: #ffffff;
            cursor: pointer;
            font-weight: 700;
        }

        a {
            color: #1d4ed8;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Login</h1>
        <p>Σύνδεση χρήστη με την κοινή βάση του συστήματος διαχείρισης ΕΕ.</p>

        <?php if ($registered): ?>
            <div class="message message-success">Η εγγραφή ολοκληρώθηκε. Μπορείτε τώρα να συνδεθείτε.</div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="message message-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= e($email) ?>">

            <label for="password">Password</label>
            <input id="password" type="password" name="password">

            <button type="submit">Login</button>
        </form>

        <p><a href="register.php">Δεν έχεις λογαριασμό; Register</a></p>
    </main>
</body>
</html>

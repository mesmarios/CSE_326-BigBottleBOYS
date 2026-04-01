<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .container {
            max-width: 760px;
            margin: 48px auto;
            background: #ffffff;
            border: 1px solid #dbe3ee;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        .links a {
            display: inline-block;
            margin-right: 12px;
            color: #0f766e;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Protected Dashboard</h1>
        <p>Καλώς ήρθες, <strong><?= e((string) $_SESSION['username']) ?></strong>.</p>
        <p>Ο ρόλος σου είναι: <strong><?= e((string) $_SESSION['role']) ?></strong>.</p>
        <p>Από εδώ μπορείς να μεταβείς στο required `list.php` αλλά και στα βασικά modules του project.</p>

        <div class="links">
            <a href="list.php">Μετάβαση στη λίστα</a>
            <a href="admin/index.php">Admin Module</a>
            <a href="recruitmentModule/index.php">Recruitment Module</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </main>
</body>
</html>

<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BigBottleBOYS | Specialist Management System</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #eef2ff, #f8fafc);
            color: #0f172a;
        }

        .wrap {
            width: min(820px, 92vw);
            background: #ffffff;
            border: 1px solid #dbe3ee;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            padding: 32px;
        }

        h1 {
            margin-top: 0;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        a {
            text-decoration: none;
            font-weight: 700;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            background: #f8fafc;
        }

        a.primary {
            background: #1d4ed8;
            color: #ffffff;
            border-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <main class="wrap">
        <h1>Specialist Management System</h1>
        <p>Landing page για το γενικό concept της εφαρμογής διαχείρισης ειδικών επιστημόνων, με κοινή βάση δεδομένων και πρόσβαση στα βασικά modules.</p>

        <div class="actions">
            <a class="primary" href="auth/register.php">Register</a>
            <a href="auth/login.php">Login</a>
            <a href="modules/admin/index.php">Admin Module</a>
            <a href="modules/recruitmentModule/index.php">Recruitment Module</a>
            <a href="modules/dashboard.php">M2 Dashboard</a>
            <a href="modules/list.php">M2 List with Search</a>
        </div>
    </main>
</body>
</html>

<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$username = htmlspecialchars((string)($_SESSION['username'] ?? ''), ENT_QUOTES, 'UTF-8');
$role = htmlspecialchars((string)($_SESSION['role'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h3 mb-3">Dashboard</h1>
            <p class="mb-2">Username: <strong><?= $username ?></strong></p>
            <p class="mb-4">Role: <strong><?= $role ?></strong></p>
            <a href="list.php" class="btn btn-primary">Open List</a>
            <a href="../auth/logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</div>
</body>
</html>

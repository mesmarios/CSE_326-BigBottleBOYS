<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$keyword = trim($_GET['keyword'] ?? '');
$stmt = $pdo->prepare(
    'SELECT
        id,
        title,
        description,
        status,
        created_at
     FROM job_announcements ja
     WHERE :keyword = \'\'
        OR title LIKE :kw
        OR description LIKE :kw
     ORDER BY ja.created_at DESC, ja.id DESC'
);
$stmt->execute([
    ':keyword' => $keyword,
    ':kw' => '%' . $keyword . '%',
]);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Job Announcements</h1>
            <p class="text-muted mb-0">Protected list with keyword search.</p>
        </div>
        <div>
            <a href="dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
            <a href="../auth/logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>

    <form method="get" class="row g-2 mb-4">
        <div class="col-md-9">
            <input
                type="text"
                name="keyword"
                class="form-control"
                placeholder="Αναζήτηση με λέξη-κλειδί"
                value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">Δεν βρέθηκαν αποτελέσματα.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$row['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$row['description'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$row['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>

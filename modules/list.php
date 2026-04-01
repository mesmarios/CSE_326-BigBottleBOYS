<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$keyword = trim($_GET['keyword'] ?? '');
$conditions = [];
$params = [];

if ($keyword !== '') {
    $conditions[] = '(
        ja.title LIKE :kw
        OR ja.status LIKE :kw
        OR d.name LIKE :kw
        OR s.name LIKE :kw
        OR u.username LIKE :kw
        OR u.first_name LIKE :kw
        OR u.last_name LIKE :kw
    )';
    $params[':kw'] = '%' . $keyword . '%';
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    $conditions[] = 'ca.candidate_id = :user_id';
    $params[':user_id'] = (int) $_SESSION['user_id'];
}

$sql = '
    SELECT
        ca.id,
        ca.status,
        ca.progress,
        ca.submitted_at,
        ja.title,
        d.name AS department_name,
        s.name AS school_name,
        u.username,
        u.first_name,
        u.last_name
    FROM candidate_applications ca
    INNER JOIN job_announcements ja ON ja.id = ca.announcement_id
    INNER JOIN departments d ON d.id = ja.department_id
    INNER JOIN schools s ON s.id = ja.school_id
    INNER JOIN users u ON u.id = ca.candidate_id
';

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY ca.submitted_at DESC, ca.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications List</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .container {
            max-width: 1180px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #dbe3ee;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        form {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        input[type="search"] {
            flex: 1 1 320px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        button,
        .link {
            padding: 12px 18px;
            border: 0;
            border-radius: 8px;
            background: #1d4ed8;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #eff6ff;
        }

        .actions {
            margin-top: 18px;
        }
    </style>
</head>
<body>
    <main class="container">
        <section class="card">
            <h1>Applications List</h1>
            <p>Συνδεδεμένος χρήστης: <strong><?= e((string) $_SESSION['username']) ?></strong></p>

            <form method="get" action="">
                <input
                    type="search"
                    name="keyword"
                    placeholder="Αναζήτηση με keyword..."
                    value="<?= e($keyword) ?>"
                >
                <button type="submit">Search</button>
                <a class="link" href="list.php">Καθαρισμός</a>
            </form>

            <?php if (empty($applications)): ?>
                <p>Δεν βρέθηκαν αποτελέσματα.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Τίτλος Θέσης</th>
                            <th>Σχολή</th>
                            <th>Τμήμα</th>
                            <th>Υποψήφιος</th>
                            <th>Κατάσταση</th>
                            <th>Progress</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $application): ?>
                            <?php $candidateName = trim($application['first_name'] . ' ' . $application['last_name']); ?>
                            <tr>
                                <td><?= e((string) $application['id']) ?></td>
                                <td><?= e($application['title']) ?></td>
                                <td><?= e($application['school_name']) ?></td>
                                <td><?= e($application['department_name']) ?></td>
                                <td><?= e($application['username'] !== '' ? $application['username'] : $candidateName) ?></td>
                                <td><?= e($application['status']) ?></td>
                                <td><?= e((string) $application['progress']) ?>%</td>
                                <td><?= e((string) ($application['submitted_at'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="actions">
                <a class="link" href="dashboard.php">Dashboard</a>
                <a class="link" href="../auth/logout.php">Logout</a>
            </div>
        </section>
    </main>
</body>
</html>

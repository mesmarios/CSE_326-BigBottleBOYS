<?php
require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../includes/config.php';

$pdo = getDBConnection();

$totalApplications = (int) $pdo->query('SELECT COUNT(*) FROM candidate_applications')->fetchColumn();
$approvedApplications = (int) $pdo->query("SELECT COUNT(*) FROM candidate_applications WHERE status = 'accepted'")->fetchColumn();
$pendingApplications = (int) $pdo->query("SELECT COUNT(*) FROM candidate_applications WHERE status IN ('draft', 'submitted', 'under_review')")->fetchColumn();
$rejectedApplications = (int) $pdo->query("SELECT COUNT(*) FROM candidate_applications WHERE status = 'rejected'")->fetchColumn();

$statusRows = $pdo->query('SELECT status, COUNT(*) AS count FROM candidate_applications GROUP BY status')->fetchAll(PDO::FETCH_ASSOC);
$statusMap = [];
foreach ($statusRows as $row) {
    $statusMap[$row['status']] = (int) $row['count'];
}

$timelineRows = $pdo->query(
    "SELECT DATE_FORMAT(COALESCE(submitted_at, created_at), '%Y-%m') AS period,
            status,
            COUNT(*) AS total
     FROM candidate_applications
     GROUP BY DATE_FORMAT(COALESCE(submitted_at, created_at), '%Y-%m'), status
     ORDER BY period ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$periods = [];
$timelineMap = [];
foreach ($timelineRows as $row) {
    $period = $row['period'];
    if (!in_array($period, $periods, true)) {
        $periods[] = $period;
    }
    if (!isset($timelineMap[$period])) {
        $timelineMap[$period] = ['accepted' => 0, 'under_review' => 0, 'rejected' => 0];
    }
    if (isset($timelineMap[$period][$row['status']])) {
        $timelineMap[$period][$row['status']] = (int) $row['total'];
    }
}

$timelineLabels = [];
$timelineAccepted = [];
$timelineUnderReview = [];
$timelineRejected = [];
foreach ($periods as $period) {
    $timelineLabels[] = date('M Y', strtotime($period . '-01'));
    $timelineAccepted[] = $timelineMap[$period]['accepted'] ?? 0;
    $timelineUnderReview[] = $timelineMap[$period]['under_review'] ?? 0;
    $timelineRejected[] = $timelineMap[$period]['rejected'] ?? 0;
}

$departmentRows = $pdo->query(
    "SELECT d.name AS department_name, COUNT(ca.id) AS total
     FROM departments d
     LEFT JOIN job_announcements ja ON ja.department_id = d.id
     LEFT JOIN candidate_applications ca ON ca.announcement_id = ja.id
     GROUP BY d.id
     ORDER BY total DESC, d.name ASC
     LIMIT 8"
)->fetchAll(PDO::FETCH_ASSOC);

$courseRows = $pdo->query(
    "SELECT c.name AS course_name, COUNT(ca.id) AS total
     FROM courses c
     LEFT JOIN job_announcements ja ON ja.course_id = c.id
     LEFT JOIN candidate_applications ca ON ca.announcement_id = ja.id
     GROUP BY c.id
     ORDER BY total DESC, c.name ASC
     LIMIT 8"
)->fetchAll(PDO::FETCH_ASSOC);

$summaryRows = $pdo->query(
    "SELECT d.name AS department_name,
            s.name AS school_name,
            COUNT(ca.id) AS total,
            SUM(CASE WHEN ca.status = 'accepted' THEN 1 ELSE 0 END) AS approved,
            SUM(CASE WHEN ca.status IN ('draft', 'submitted', 'under_review') THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN ca.status = 'rejected' THEN 1 ELSE 0 END) AS rejected
     FROM departments d
     INNER JOIN schools s ON s.id = d.school_id
     LEFT JOIN job_announcements ja ON ja.department_id = d.id
     LEFT JOIN candidate_applications ca ON ca.announcement_id = ja.id
     GROUP BY d.id
     ORDER BY total DESC, d.name ASC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <title>Admin | Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/adminlte.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" crossorigin="anonymous"></script>
    <style>
        body { background: #f5f7fb; font-family: Arial, sans-serif; }
        .page-shell { max-width: 1280px; margin: 32px auto; padding: 0 16px; }
        .topbar, .card { background: #fff; border: 1px solid #dbe3ee; border-radius: 14px; box-shadow: 0 10px 24px rgba(15,23,42,0.06); }
        .topbar { padding: 18px 22px; margin-bottom: 18px; display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .topbar a { text-decoration: none; font-weight: 700; color: #1d4ed8; margin-right: 14px; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 18px; }
        .stat { padding: 18px; }
        .charts { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; margin-bottom: 18px; }
        .chart-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-bottom: 18px; }
        .card { padding: 22px; }
        .section-title { margin: 0 0 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: top; }
        th { background: #eff6ff; }
        .muted { color: #64748b; }
        .progress-line { width: 100%; height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
        .progress-line span { display: block; height: 100%; background: #22c55e; }
        @media (max-width: 980px) {
            .stats, .charts, .chart-grid { grid-template-columns: 1fr; }
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
            <div><a href="../../logout.php">Logout</a></div>
        </div>

        <div class="stats">
            <div class="card stat"><strong><?= $totalApplications ?></strong><div class="muted">Σύνολο Αιτήσεων</div></div>
            <div class="card stat"><strong><?= $approvedApplications ?></strong><div class="muted">Εγκεκριμένες</div></div>
            <div class="card stat"><strong><?= $pendingApplications ?></strong><div class="muted">Σε Εκκρεμότητα</div></div>
            <div class="card stat"><strong><?= $rejectedApplications ?></strong><div class="muted">Απορριφθείσες</div></div>
        </div>

        <div class="charts">
            <section class="card">
                <h3 class="section-title">Εξέλιξη Αιτήσεων</h3>
                <div id="timeline-chart"></div>
            </section>
            <section class="card">
                <h3 class="section-title">Κατανομή Status</h3>
                <div id="status-chart"></div>
            </section>
        </div>

        <div class="chart-grid">
            <section class="card">
                <h3 class="section-title">Αιτήσεις ανά Τμήμα</h3>
                <div id="department-chart"></div>
            </section>
            <section class="card">
                <h3 class="section-title">Αιτήσεις ανά Μάθημα</h3>
                <div id="course-chart"></div>
            </section>
        </div>

        <section class="card">
            <h3 class="section-title">Συγκεντρωτικός Πίνακας</h3>
            <table>
                <thead>
                    <tr>
                        <th>Τμήμα</th>
                        <th>Σχολή</th>
                        <th>Σύνολο</th>
                        <th>Εγκεκριμένες</th>
                        <th>Εκκρεμείς</th>
                        <th>Απορριφθείσες</th>
                        <th>Ποσοστό Έγκρισης</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summaryRows as $row): ?>
                        <?php
                        $total = (int) $row['total'];
                        $approved = (int) $row['approved'];
                        $approvalRate = $total > 0 ? (int) round(($approved / $total) * 100) : 0;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['department_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['school_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $total ?></td>
                            <td><?= $approved ?></td>
                            <td><?= (int) $row['pending'] ?></td>
                            <td><?= (int) $row['rejected'] ?></td>
                            <td>
                                <div class="progress-line"><span style="width: <?= $approvalRate ?>%"></span></div>
                                <small><?= $approvalRate ?>%</small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <script>
        new ApexCharts(document.querySelector('#timeline-chart'), {
            chart: { type: 'area', height: 300, toolbar: { show: false } },
            series: [
                { name: 'Εγκεκριμένες', data: <?= json_encode($timelineAccepted, JSON_UNESCAPED_UNICODE) ?> },
                { name: 'Υπό Αξιολόγηση', data: <?= json_encode($timelineUnderReview, JSON_UNESCAPED_UNICODE) ?> },
                { name: 'Απορριφθείσες', data: <?= json_encode($timelineRejected, JSON_UNESCAPED_UNICODE) ?> }
            ],
            xaxis: { categories: <?= json_encode($timelineLabels, JSON_UNESCAPED_UNICODE) ?> },
            colors: ['#22c55e', '#f59e0b', '#ef4444'],
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } }
        }).render();

        new ApexCharts(document.querySelector('#status-chart'), {
            chart: { type: 'donut', height: 300 },
            series: <?= json_encode(array_values($statusMap), JSON_UNESCAPED_UNICODE) ?>,
            labels: <?= json_encode(array_keys($statusMap), JSON_UNESCAPED_UNICODE) ?>,
            colors: ['#64748b', '#3b82f6', '#f59e0b', '#22c55e', '#ef4444', '#8b5cf6']
        }).render();

        new ApexCharts(document.querySelector('#department-chart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name: 'Αιτήσεις', data: <?= json_encode(array_map(fn($row) => (int) $row['total'], $departmentRows), JSON_UNESCAPED_UNICODE) ?> }],
            xaxis: { categories: <?= json_encode(array_map(fn($row) => $row['department_name'], $departmentRows), JSON_UNESCAPED_UNICODE) ?> },
            dataLabels: { enabled: false },
            colors: ['#6d28d9']
        }).render();

        new ApexCharts(document.querySelector('#course-chart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name: 'Αιτήσεις', data: <?= json_encode(array_map(fn($row) => (int) $row['total'], $courseRows), JSON_UNESCAPED_UNICODE) ?> }],
            xaxis: { categories: <?= json_encode(array_map(fn($row) => $row['course_name'], $courseRows), JSON_UNESCAPED_UNICODE) ?> },
            dataLabels: { enabled: false },
            colors: ['#1d4ed8']
        }).render();
    </script>
</body>
</html>

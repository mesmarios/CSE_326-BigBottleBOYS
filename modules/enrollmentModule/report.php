<?php
declare(strict_types=1);

$enrollmentRequireManager = true;
require_once __DIR__ . '/../../includes/enrollment-guard.php';

$enrollmentPageTitle = 'Report';
$enrollmentPageHeading = 'Enrollment Report';
$enrollmentPageDescription = 'Στατιστικά πρόσβασης LMS, courses χωρίς ενεργό ειδικό επιστήμονα και εικόνα συγχρονισμών.';
$enrollmentActivePage = 'report';
require_once __DIR__ . '/../../includes/enrollment-top.php';

$summary = [
    'total_specialists' => 0,
    'with_active_access' => 0,
    'without_active_access' => 0,
    'pending_access' => 0,
];
$statusBreakdown = [];
$departmentBreakdown = [];
$coursesWithoutInstructor = [];

try {
    $summary = $pdo->query(
        "
        SELECT
            (SELECT COUNT(*) FROM users WHERE role = 'specialist') AS total_specialists,
            (SELECT COUNT(DISTINCT user_id) FROM specialist_enrollments WHERE access_status = 'active') AS with_active_access,
            (SELECT COUNT(DISTINCT user_id) FROM specialist_enrollments WHERE access_status = 'pending') AS pending_access
        "
    )->fetch(PDO::FETCH_ASSOC) ?: $summary;
    $summary['total_specialists'] = (int)($summary['total_specialists'] ?? 0);
    $summary['with_active_access'] = (int)($summary['with_active_access'] ?? 0);
    $summary['pending_access'] = (int)($summary['pending_access'] ?? 0);
    $summary['without_active_access'] = max(0, $summary['total_specialists'] - $summary['with_active_access']);

    $statusBreakdown = $pdo->query(
        "
        SELECT access_status, COUNT(*) AS total
        FROM specialist_enrollments
        GROUP BY access_status
        ORDER BY total DESC, access_status ASC
        "
    )->fetchAll(PDO::FETCH_ASSOC);

    $departmentBreakdown = $pdo->query(
        "
        SELECT
            d.name AS department_name,
            COUNT(*) AS total
        FROM specialist_enrollments se
        INNER JOIN courses c ON c.id = se.course_id
        INNER JOIN departments d ON d.id = c.department_id
        WHERE se.access_status = 'active'
        GROUP BY d.id, d.name
        ORDER BY total DESC, d.name ASC
        "
    )->fetchAll(PDO::FETCH_ASSOC);

    $coursesWithoutInstructor = $pdo->query(
        "
        SELECT
            c.code,
            c.name AS course_name,
            d.name AS department_name,
            s.name AS school_name
        FROM courses c
        INNER JOIN departments d ON d.id = c.department_id
        INNER JOIN schools s ON s.id = d.school_id
        LEFT JOIN specialist_enrollments se
            ON se.course_id = c.id
           AND se.access_status = 'active'
        WHERE se.id IS NULL
        ORDER BY s.name ASC, d.name ASC, c.code ASC
        "
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

$statusLabels = array_map(static fn(array $row): string => ucfirst((string)$row['access_status']), $statusBreakdown);
$statusSeries = array_map(static fn(array $row): int => (int)$row['total'], $statusBreakdown);
$departmentLabels = array_map(static fn(array $row): string => (string)$row['department_name'], $departmentBreakdown);
$departmentSeries = array_map(static fn(array $row): int => (int)$row['total'], $departmentBreakdown);

if ($statusLabels === []) {
    $statusLabels = ['No data'];
    $statusSeries = [1];
}

if ($departmentLabels === []) {
    $departmentLabels = ['No active access'];
    $departmentSeries = [0];
}
?>
<div class="row g-3 mb-4">
  <div class="col-md-6 col-xl-3">
    <div class="card enrollment-kpi shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon" style="background:#dbeafe;color:#1d4ed8;"><i class="bi bi-people-fill"></i></div>
        <div><div class="stat-value"><?= (int)$summary['total_specialists'] ?></div><div class="stat-label">Σύνολο Specialists</div></div>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card enrollment-kpi shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon" style="background:#dcfce7;color:#15803d;"><i class="bi bi-check2-circle"></i></div>
        <div><div class="stat-value"><?= (int)$summary['with_active_access'] ?></div><div class="stat-label">Με ενεργή πρόσβαση</div></div>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card enrollment-kpi shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon" style="background:#fee2e2;color:#b91c1c;"><i class="bi bi-person-x"></i></div>
        <div><div class="stat-value"><?= (int)$summary['without_active_access'] ?></div><div class="stat-label">Χωρίς ενεργή πρόσβαση</div></div>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card enrollment-kpi shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon" style="background:#fef3c7;color:#b45309;"><i class="bi bi-hourglass-split"></i></div>
        <div><div class="stat-value"><?= (int)$summary['pending_access'] ?></div><div class="stat-label">Pending Specialists</div></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-5">
    <div class="card enrollment-section-card shadow-sm h-100">
      <div class="card-header bg-white">
        <h5 class="mb-0">Κατανομή access status</h5>
      </div>
      <div class="card-body">
        <div id="statusChart" style="min-height: 320px;"></div>
      </div>
    </div>
  </div>
  <div class="col-xl-7">
    <div class="card enrollment-section-card shadow-sm h-100">
      <div class="card-header bg-white">
        <h5 class="mb-0">Ενεργές προσβάσεις ανά τμήμα</h5>
      </div>
      <div class="card-body">
        <div id="departmentChart" style="min-height: 320px;"></div>
      </div>
    </div>
  </div>
</div>

<div class="card enrollment-section-card shadow-sm">
  <div class="card-header bg-white">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0">Courses χωρίς ενεργό διδάσκοντα στο LMS</h5>
      <input type="text" id="courseReportSearch" class="form-control form-control-sm" placeholder="Αναζήτηση course..." style="width:240px;">
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="coursesWithoutInstructorTable">
        <thead class="table-light">
          <tr>
            <th>Κωδικός</th>
            <th>Μάθημα</th>
            <th>Τμήμα</th>
            <th>Σχολή</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($coursesWithoutInstructor === []): ?>
          <tr><td colspan="4" class="text-center text-secondary py-4">Όλα τα courses έχουν τουλάχιστον έναν ενεργό ειδικό επιστήμονα.</td></tr>
          <?php else: ?>
            <?php foreach ($coursesWithoutInstructor as $course): ?>
            <tr>
              <td><strong><?= enrollmentH((string)$course['code']) ?></strong></td>
              <td><?= enrollmentH((string)$course['course_name']) ?></td>
              <td><?= enrollmentH((string)$course['department_name']) ?></td>
              <td><?= enrollmentH((string)$course['school_name']) ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const statusLabels = <?= json_encode($statusLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const statusSeries = <?= json_encode($statusSeries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const departmentLabels = <?= json_encode($departmentLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const departmentSeries = <?= json_encode($departmentSeries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    if (window.ApexCharts) {
      const statusChart = new ApexCharts(document.querySelector('#statusChart'), {
        chart: { type: 'donut', height: 320, toolbar: { show: false } },
        labels: statusLabels,
        series: statusSeries.length ? statusSeries : [1],
        colors: ['#16a34a', '#d97706', '#64748b', '#dc2626'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: true }
      });
      statusChart.render();

      const departmentChart = new ApexCharts(document.querySelector('#departmentChart'), {
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        series: [{ name: 'Active accesses', data: departmentSeries }],
        xaxis: { categories: departmentLabels },
        colors: ['#2563eb'],
        plotOptions: { bar: { borderRadius: 6, horizontal: false } },
        dataLabels: { enabled: false }
      });
      departmentChart.render();
    }

    const searchInput = document.getElementById('courseReportSearch');
    const rows = Array.from(document.querySelectorAll('#coursesWithoutInstructorTable tbody tr'));

    searchInput.addEventListener('input', function () {
      const query = (searchInput.value || '').toLowerCase().trim();
      rows.forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
      });
    });
  });
</script>

<?php require_once __DIR__ . '/../../includes/enrollment-bottom.php'; ?>

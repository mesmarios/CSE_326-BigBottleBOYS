<?php
declare(strict_types=1);

$enrollment_allowed = ['admin', 'hr'];
require_once __DIR__ . '/includes/enrollment-guard.php';
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/admin-branding.php';

$brandingContext = adminGetBrandingContext($pdo);
$adminBrandText  = $brandingContext['brand_text'];
$adminLogo       = preg_replace('#^\.\./\.\./assets/#', '../assets/', (string)$brandingContext['logo']) ?? (string)$brandingContext['logo'];
$adminFavicon    = $brandingContext['favicon'];

$enrollFullName  = trim((string)(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')));
if ($enrollFullName === '') { $enrollFullName = 'Χρήστης'; }
$enrollAvatarSrc = enrollResolveAvatarSrc($pdo, (int)($_SESSION['user_id'] ?? 0));

$pageTitle         = 'Report';
$currentEnrollPage = 'report.php';

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function fmtN(int $n): string { return number_format($n, 0, ',', '.'); }

// ── Stats ──────────────────────────────────────────────────────────────────
$totalEE       = 0;
$activeAccess  = 0;
$inactiveAccess = 0;
$coursesNoInstructor = [];
$accessByCourse = [];
$reportError   = null;

try {
    $totalEE = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'ee_hired'")->fetchColumn();

    $activeAccess = (int)$pdo->query(
        "SELECT COUNT(DISTINCT user_id) FROM lms_access WHERE status = 'active'"
    )->fetchColumn();

    $inactiveAccess = $totalEE - $activeAccess;

    // Courses with no instructor (no accepted ee_hired application for that course, as a proxy)
    $coursesNoInstructor = $pdo->query(
        "SELECT c.id, c.name, d.name AS dept_name
         FROM courses c
         LEFT JOIN departments d ON d.id = c.department_id
         LEFT JOIN lms_access la ON la.course_id = c.id AND la.status = 'active'
         WHERE la.id IS NULL
         ORDER BY c.name"
    )->fetchAll();

    // Active access grouped by course (for chart)
    $accessByCourse = $pdo->query(
        "SELECT c.name AS course_name, COUNT(la.user_id) AS count
         FROM lms_access la
         JOIN courses c ON c.id = la.course_id
         WHERE la.status = 'active'
         GROUP BY la.course_id, c.name
         ORDER BY count DESC
         LIMIT 10"
    )->fetchAll();

} catch (Throwable $e) {
    $reportError = 'Δεν ήταν δυνατή η φόρτωση δεδομένων: ' . $e->getMessage();
}

$chartLabels = json_encode(array_column($accessByCourse, 'course_name'));
$chartData   = json_encode(array_map(fn($r) => (int)$r['count'], $accessByCourse));

// ── Excel (CSV) Export ─────────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $allEE = $pdo->query(
        "SELECT u.first_name, u.last_name, u.email,
                c.name AS course_name, c.code AS course_code,
                la.status, la.granted_at
         FROM users u
         LEFT JOIN lms_access la ON la.user_id = u.id
         LEFT JOIN courses c ON c.id = la.course_id
         WHERE u.role = 'ee_hired'
         ORDER BY u.last_name, u.first_name"
    )->fetchAll();

    $filename = 'enrollment_report_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $out = fopen('php://output', 'w');
    // UTF-8 BOM για σωστή εμφάνιση ελληνικών στο Excel
    fputs($out, "\xEF\xBB\xBF");

    // === Σύνοψη ===
    fputcsv($out, ['=== ΣΥΝΟΨΗ ===']);
    fputcsv($out, ['Σύνολο ΕΕ', $totalEE]);
    fputcsv($out, ['ΕΕ με Ενεργή Πρόσβαση Moodle', $activeAccess]);
    fputcsv($out, ['ΕΕ χωρίς Πρόσβαση', max(0, $inactiveAccess)]);
    fputcsv($out, ['Ημερομηνία Εξαγωγής', date('d/m/Y H:i')]);
    fputcsv($out, []);

    // === Λεπτομέρειες ΕΕ ===
    fputcsv($out, ['=== ΛΕΠΤΟΜΕΡΕΙΕΣ ΧΡΗΣΤΩΝ ΕΕ ===']);
    fputcsv($out, ['Επώνυμο', 'Όνομα', 'Email', 'Μάθημα', 'Κωδικός', 'Κατάσταση Πρόσβασης', 'Ημ/νία Χορήγησης']);
    foreach ($allEE as $row) {
        fputcsv($out, [
            $row['last_name']   ?? '',
            $row['first_name']  ?? '',
            $row['email']       ?? '',
            $row['course_name'] ?? '—',
            $row['course_code'] ?? '—',
            match ($row['status'] ?? '') {
                'active'   => 'Ενεργή',
                'inactive' => 'Ανενεργή',
                default    => 'Χωρίς εγγραφή',
            },
            $row['granted_at'] ? date('d/m/Y H:i', strtotime($row['granted_at'])) : '—',
        ]);
    }
    fputcsv($out, []);

    // === Ενεργή Πρόσβαση ανά Μάθημα ===
    fputcsv($out, ['=== ΕΝΕΡΓΗ ΠΡΟΣΒΑΣΗ ΑΝΑ ΜΑΘΗΜΑ ===']);
    fputcsv($out, ['Μάθημα', 'Αριθμός ΕΕ']);
    foreach ($accessByCourse as $row) {
        fputcsv($out, [$row['course_name'], $row['count']]);
    }
    fputcsv($out, []);

    // === Μαθήματα χωρίς ΕΕ ===
    fputcsv($out, ['=== ΜΑΘΗΜΑΤΑ ΧΩΡΙΣ ΕΚΠΑΙΔΕΥΤΗ ΕΕ ===']);
    fputcsv($out, ['Μάθημα', 'Τμήμα']);
    foreach ($coursesNoInstructor as $row) {
        fputcsv($out, [$row['name'], $row['dept_name'] ?? '—']);
    }

    fclose($out);
    exit;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0"><i class="bi bi-bar-chart-fill me-2"></i>Report</h3></div>
        <div class="col-sm-6 d-flex align-items-center justify-content-sm-end gap-3">
          <a href="report.php?export=excel" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel-fill me-1"></i>Download Stats
          </a>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Report</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">

      <?php if ($reportError !== null): ?>
      <div class="alert alert-warning shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= h($reportError) ?>
      </div>
      <?php endif; ?>

      <!-- Stat Cards -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
          <div class="stat-card bg-body shadow-sm">
            <div class="d-flex align-items-center gap-3">
              <div class="stat-icon-wrap" style="background:#dbeafe;color:#1d4ed8;">
                <i class="bi bi-people-fill"></i>
              </div>
              <div>
                <div class="stat-value"><?= h(fmtN($totalEE)) ?></div>
                <div class="stat-label">Σύνολο ΕΕ</div>
                <div class="small text-secondary">Μισθωμένοι ΕΕ στο σύστημα</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="stat-card bg-body shadow-sm">
            <div class="d-flex align-items-center gap-3">
              <div class="stat-icon-wrap" style="background:#dcfce7;color:#15803d;">
                <i class="bi bi-check-circle-fill"></i>
              </div>
              <div>
                <div class="stat-value"><?= h(fmtN($activeAccess)) ?></div>
                <div class="stat-label">Ενεργή Πρόσβαση</div>
                <div class="small text-secondary">ΕΕ με ενεργή πρόσβαση Moodle</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="stat-card bg-body shadow-sm">
            <div class="d-flex align-items-center gap-3">
              <div class="stat-icon-wrap" style="background:#fef3c7;color:#b45309;">
                <i class="bi bi-slash-circle-fill"></i>
              </div>
              <div>
                <div class="stat-value"><?= h(fmtN(max(0, $inactiveAccess))) ?></div>
                <div class="stat-label">Χωρίς Πρόσβαση</div>
                <div class="small text-secondary">ΕΕ χωρίς ενεργή πρόσβαση</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">

        <!-- Chart: active access by course -->
        <?php if ($accessByCourse !== []): ?>
        <div class="col-12 col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-body border-0">
              <h5 class="card-title mb-0 fw-semibold">
                <i class="bi bi-bar-chart me-2 text-primary"></i>Ενεργή Πρόσβαση ανά Μάθημα
              </h5>
            </div>
            <div class="card-body">
              <div id="lmsChart"></div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Table: courses without instructor -->
        <div class="col-12 <?= $accessByCourse !== [] ? 'col-lg-6' : '' ?>">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-body border-0 d-flex align-items-center justify-content-between">
              <h5 class="card-title mb-0 fw-semibold">
                <i class="bi bi-exclamation-triangle me-2 text-warning"></i>Μαθήματα χωρίς Εκπαιδευτή ΕΕ
              </h5>
              <span class="badge text-bg-warning"><?= count($coursesNoInstructor) ?></span>
            </div>
            <div class="card-body p-0">
              <?php if ($coursesNoInstructor === []): ?>
              <div class="p-4 text-secondary">Όλα τα μαθήματα έχουν εκχωρημένο ΕΕ.</div>
              <?php else: ?>
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th class="ps-4">Μάθημα</th>
                      <th>Τμήμα</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($coursesNoInstructor as $c): ?>
                    <tr>
                      <td class="ps-4 fw-semibold"><?= h((string)$c['name']) ?></td>
                      <td class="text-secondary"><?= h((string)($c['dept_name'] ?? '—')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>

</div><!-- /.app-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" crossorigin="anonymous"></script>
<script src="../assets/js/adminlte.js" defer></script>
<script src="../assets/js/changes.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var sw = document.querySelector('.sidebar-wrapper');
  if (sw && OverlayScrollbarsGlobal?.OverlayScrollbars) {
    OverlayScrollbarsGlobal.OverlayScrollbars(sw, { scrollbars: { theme:'os-theme-light', autoHide:'leave', clickScroll:true } });
  }

  <?php if ($accessByCourse !== []): ?>
  var chartEl = document.getElementById('lmsChart');
  if (chartEl && typeof ApexCharts !== 'undefined') {
    new ApexCharts(chartEl, {
      chart: { type: 'bar', height: 280, toolbar: { show: false } },
      series: [{ name: 'ΕΕ', data: <?= $chartData ?> }],
      xaxis: { categories: <?= $chartLabels ?>, labels: { style: { fontSize: '11px' } } },
      colors: ['#1d4ed8'],
      plotOptions: { bar: { borderRadius: 4, horizontal: false } },
      dataLabels: { enabled: false },
      grid: { borderColor: 'rgba(0,0,0,.07)' },
      tooltip: { y: { formatter: function (v) { return v + ' ΕΕ'; } } }
    }).render();
  }
  <?php endif; ?>
});
</script>
</body>
</html>

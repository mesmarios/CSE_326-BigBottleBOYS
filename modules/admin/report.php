<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/admin-branding.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function reportPercent(int $value, int $total): string
{
    if ($total <= 0) {
        return '0%';
    }

    return number_format(($value / $total) * 100, 1) . '%';
}

function monthLabel(string $yearMonth): string
{
    static $greekMonths = [
        1 => 'Ιαν',
        2 => 'Φεβ',
        3 => 'Μαρ',
        4 => 'Απρ',
        5 => 'Μαϊ',
        6 => 'Ιουν',
        7 => 'Ιουλ',
        8 => 'Αυγ',
        9 => 'Σεπ',
        10 => 'Οκτ',
        11 => 'Νοε',
        12 => 'Δεκ',
    ];

    [$year, $month] = explode('-', $yearMonth);
    $monthNumber = (int)$month;

    return ($greekMonths[$monthNumber] ?? $yearMonth) . ' ' . $year;
}

function buildMonthKeys(?string $startDate, ?string $endDate, array $fallbackKeys = []): array
{
    $keys = [];

    if ($startDate && $endDate) {
        $start = new DateTimeImmutable(date('Y-m-01', strtotime($startDate)));
        $end = new DateTimeImmutable(date('Y-m-01', strtotime($endDate)));

        while ($start <= $end) {
            $keys[] = $start->format('Y-m');
            $start = $start->modify('+1 month');
        }

        if ($keys !== []) {
            return $keys;
        }
    }

    $fallbackKeys = array_values(array_unique(array_filter($fallbackKeys)));
    sort($fallbackKeys);

    if ($fallbackKeys !== []) {
        $start = new DateTimeImmutable($fallbackKeys[0] . '-01');
        $end = new DateTimeImmutable(end($fallbackKeys) . '-01');

        while ($start <= $end) {
            $keys[] = $start->format('Y-m');
            $start = $start->modify('+1 month');
        }

        if ($keys !== []) {
            return $keys;
        }
    }

    return [date('Y-m')];
}

function fetchAllWithParams(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll();
}

function fetchOneWithParams(PDO $pdo, string $sql, array $params = []): array
{
    $rows = fetchAllWithParams($pdo, $sql, $params);
    return $rows[0] ?? [];
}

function buildReportUrl(?int $periodId, bool $export = false): string
{
    $params = [];

    if ($periodId === null) {
        $params['period_id'] = 'all';
    } else {
        $params['period_id'] = (string)$periodId;
    }

    if ($export) {
        $params['export'] = 'excel';
    }

    return 'report.php?' . http_build_query($params);
}

function resolveAdminAvatarSrc(PDO $pdo, int $userId): string
{
  $fallback = '../../assets/images/avatar.png';

  if ($userId <= 0) {
    return $fallback;
  }

  try {
    $stmt = $pdo->prepare('SELECT profilepic_path FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $storedPath = ltrim(str_replace('\\', '/', (string)($row['profilepic_path'] ?? '')), '/');
    if ($storedPath !== '' && str_starts_with($storedPath, 'uploads/profile_pics/')) {
      $avatarBase = realpath(__DIR__ . '/../../uploads/profile_pics');
      $avatarAbs = realpath(__DIR__ . '/../../' . $storedPath);
      if ($avatarBase !== false && $avatarAbs !== false && is_file($avatarAbs)
        && strpos($avatarAbs, $avatarBase . DIRECTORY_SEPARATOR) === 0) {
        $fileVersion = (int)@filemtime($avatarAbs) ?: time();
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $storedPath)));
        return '../../' . $encodedPath . '?v=' . $fileVersion;
      }
    }
  } catch (Throwable $e) {
    // Fallback to file path below.
  }

  $fileMatches = glob(__DIR__ . '/../../uploads/profile_pics/user_' . $userId . '.*');
  if (is_array($fileMatches) && $fileMatches !== []) {
    $filePath = $fileMatches[0];
    $fileVersion = (int)@filemtime($filePath) ?: time();
    return '../../uploads/profile_pics/' . rawurlencode(basename($filePath)) . '?v=' . $fileVersion;
  }

  return $fallback;
}

$periods = $pdo->query("
    SELECT id, name, status, start_date, end_date
    FROM recruitment_periods
    ORDER BY
        CASE WHEN status = 'active' THEN 0 ELSE 1 END,
        start_date DESC,
        id DESC
")->fetchAll();

$periodById = [];
$defaultPeriod = null;

foreach ($periods as $period) {
    $periodById[(int)$period['id']] = $period;

    if ($defaultPeriod === null) {
        $defaultPeriod = $period;
    }
}

$selectedPeriodParam = $_GET['period_id'] ?? '';
$isExportRequest = ($_GET['export'] ?? '') === 'excel';
$selectedPeriod = null;
$selectedPeriodId = null;

if ($selectedPeriodParam === 'all') {
    $selectedPeriod = null;
    $selectedPeriodId = null;
} elseif (ctype_digit((string)$selectedPeriodParam) && isset($periodById[(int)$selectedPeriodParam])) {
    $selectedPeriodId = (int)$selectedPeriodParam;
    $selectedPeriod = $periodById[$selectedPeriodId];
} elseif ($defaultPeriod !== null) {
    $selectedPeriodId = (int)$defaultPeriod['id'];
    $selectedPeriod = $defaultPeriod;
}

$selectedPeriodLabel = $selectedPeriod['name'] ?? 'Όλες οι περίοδοι';
$selectedPeriodStatus = $selectedPeriod['status'] ?? null;

$params = [];
$reportableWhere = [
    "ca.status <> 'draft'",
];
$draftWhere = [
    "ca.status = 'draft'",
];

if ($selectedPeriodId !== null) {
    $reportableWhere[] = 'ja.period_id = :period_id';
    $draftWhere[] = 'ja.period_id = :period_id';
    $params[':period_id'] = $selectedPeriodId;
}

$reportableWhereSql = implode(' AND ', $reportableWhere);
$draftWhereSql = implode(' AND ', $draftWhere);

$kpi = fetchOneWithParams(
    $pdo,
    "
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN ca.status = 'accepted' THEN 1 ELSE 0 END), 0) AS approved,
        COALESCE(SUM(CASE WHEN ca.status IN ('submitted', 'under_review') THEN 1 ELSE 0 END), 0) AS pending,
        COALESCE(SUM(CASE WHEN ca.status IN ('rejected', 'withdrawn') THEN 1 ELSE 0 END), 0) AS rejected
    FROM candidate_applications ca
    INNER JOIN job_announcements ja ON ja.id = ca.announcement_id
    WHERE {$reportableWhereSql}
    ",
    $params
);

$draftStats = fetchOneWithParams(
    $pdo,
    "
    SELECT COUNT(*) AS draft_count
    FROM candidate_applications ca
    INNER JOIN job_announcements ja ON ja.id = ca.announcement_id
    WHERE {$draftWhereSql}
    ",
    $params
);

$timelineRows = fetchAllWithParams(
    $pdo,
    "
    SELECT
        DATE_FORMAT(COALESCE(ca.submitted_at, ca.created_at), '%Y-%m') AS month_key,
        COALESCE(SUM(CASE WHEN ca.status = 'accepted' THEN 1 ELSE 0 END), 0) AS approved,
        COALESCE(SUM(CASE WHEN ca.status IN ('submitted', 'under_review') THEN 1 ELSE 0 END), 0) AS pending,
        COALESCE(SUM(CASE WHEN ca.status IN ('rejected', 'withdrawn') THEN 1 ELSE 0 END), 0) AS rejected
    FROM candidate_applications ca
    INNER JOIN job_announcements ja ON ja.id = ca.announcement_id
    WHERE {$reportableWhereSql}
    GROUP BY month_key
    ORDER BY month_key ASC
    ",
    $params
);

$departmentRows = fetchAllWithParams(
    $pdo,
    "
    SELECT
        d.id AS department_id,
        d.name AS department_name,
        s.name AS school_name,
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN ca.status = 'accepted' THEN 1 ELSE 0 END), 0) AS approved,
        COALESCE(SUM(CASE WHEN ca.status IN ('submitted', 'under_review') THEN 1 ELSE 0 END), 0) AS pending,
        COALESCE(SUM(CASE WHEN ca.status IN ('rejected', 'withdrawn') THEN 1 ELSE 0 END), 0) AS rejected
    FROM candidate_applications ca
    INNER JOIN job_announcements ja ON ja.id = ca.announcement_id
    INNER JOIN departments d ON d.id = ja.department_id
    INNER JOIN schools s ON s.id = ja.school_id
    WHERE {$reportableWhereSql}
    GROUP BY d.id, d.name, s.name
    ORDER BY total DESC, d.name ASC
    ",
    $params
);

$courseRows = fetchAllWithParams(
    $pdo,
    "
    SELECT
        c.id AS course_id,
        c.name AS course_name,
        COUNT(*) AS total
    FROM candidate_applications ca
    INNER JOIN job_announcements ja ON ja.id = ca.announcement_id
    INNER JOIN courses c ON c.id = ja.course_id
    WHERE {$reportableWhereSql}
    GROUP BY c.id, c.name
    ORDER BY total DESC, c.name ASC
    ",
    $params
);

$totalApplications = (int)($kpi['total'] ?? 0);
$approvedApplications = (int)($kpi['approved'] ?? 0);
$pendingApplications = (int)($kpi['pending'] ?? 0);
$rejectedApplications = (int)($kpi['rejected'] ?? 0);
$draftCount = (int)($draftStats['draft_count'] ?? 0);

$monthKeys = buildMonthKeys(
    $selectedPeriod['start_date'] ?? null,
    $selectedPeriod['end_date'] ?? null,
    array_column($timelineRows, 'month_key')
);

$timelineByMonth = [];
foreach ($timelineRows as $row) {
    $timelineByMonth[$row['month_key']] = [
        'approved' => (int)$row['approved'],
        'pending' => (int)$row['pending'],
        'rejected' => (int)$row['rejected'],
    ];
}

$timelineCategories = [];
$timelineApproved = [];
$timelinePending = [];
$timelineRejected = [];

foreach ($monthKeys as $monthKey) {
    $monthData = $timelineByMonth[$monthKey] ?? ['approved' => 0, 'pending' => 0, 'rejected' => 0];

    $timelineCategories[] = monthLabel($monthKey);
    $timelineApproved[] = $monthData['approved'];
    $timelinePending[] = $monthData['pending'];
    $timelineRejected[] = $monthData['rejected'];
}

$deptChartRows = array_slice($departmentRows, 0, 6);
$courseChartRows = array_slice($courseRows, 0, 8);

$deptCategories = array_map(static fn(array $row): string => $row['department_name'], $deptChartRows);
$deptTotals = array_map(static fn(array $row): int => (int)$row['total'], $deptChartRows);

$courseCategories = array_map(static fn(array $row): string => $row['course_name'], $courseChartRows);
$courseTotals = array_map(static fn(array $row): int => (int)$row['total'], $courseChartRows);

$reportData = [
    'hasData' => $totalApplications > 0,
    'timeline' => [
        'categories' => $timelineCategories,
        'approved' => $timelineApproved,
        'pending' => $timelinePending,
        'rejected' => $timelineRejected,
    ],
    'donut' => [
        'approved' => $approvedApplications,
        'pending' => $pendingApplications,
        'rejected' => $rejectedApplications,
    ],
    'department' => [
        'categories' => $deptCategories,
        'totals' => $deptTotals,
    ],
    'course' => [
        'categories' => $courseCategories,
        'totals' => $courseTotals,
    ],
];

$periodStatusMap = [
    'active' => 'Ενεργή περίοδος',
    'planning' => 'Προγραμματισμένη περίοδος',
    'closed' => 'Κλειστή περίοδος',
    'archived' => 'Αρχειοθετημένη περίοδος',
];

$exportUrl = buildReportUrl($selectedPeriodId, true);

if ($isExportRequest) {
    $filenameLabel = $selectedPeriodLabel !== '' ? $selectedPeriodLabel : 'all-periods';
    $filenameLabel = preg_replace('/[^A-Za-z0-9_-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filenameLabel) ?: $filenameLabel);
    $filenameLabel = trim((string)$filenameLabel, '-');
    $filenameLabel = $filenameLabel !== '' ? $filenameLabel : 'report';

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="reports-' . $filenameLabel . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "\xEF\xBB\xBF";
    ?>
<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Reports Export</title>
  </head>
  <body>
    <table border="1">
      <tr>
        <th colspan="2">Αναφορά Στατιστικών</th>
      </tr>
      <tr>
        <td>Περίοδος</td>
        <td><?= h($selectedPeriodLabel) ?></td>
      </tr>
      <tr>
        <td>Κατάσταση περιόδου</td>
        <td><?= h($selectedPeriodStatus !== null ? ($periodStatusMap[$selectedPeriodStatus] ?? $selectedPeriodStatus) : 'Όλες οι περίοδοι') ?></td>
      </tr>
      <tr>
        <td>Πρόχειρες εκτός αναφοράς</td>
        <td><?= $draftCount ?></td>
      </tr>
    </table>

    <br />

    <table border="1">
      <tr>
        <th>Μετρική</th>
        <th>Τιμή</th>
        <th>Ποσοστό</th>
      </tr>
      <tr>
        <td>Σύνολο Αιτήσεων</td>
        <td><?= $totalApplications ?></td>
        <td>100%</td>
      </tr>
      <tr>
        <td>Εγκεκριμένες</td>
        <td><?= $approvedApplications ?></td>
        <td><?= reportPercent($approvedApplications, $totalApplications) ?></td>
      </tr>
      <tr>
        <td>Υπό Αξιολόγηση</td>
        <td><?= $pendingApplications ?></td>
        <td><?= reportPercent($pendingApplications, $totalApplications) ?></td>
      </tr>
      <tr>
        <td>Απορριφθείσες</td>
        <td><?= $rejectedApplications ?></td>
        <td><?= reportPercent($rejectedApplications, $totalApplications) ?></td>
      </tr>
    </table>

    <br />

    <table border="1">
      <tr>
        <th>Τμήμα</th>
        <th>Σχολή</th>
        <th>Σύνολο</th>
        <th>Εγκεκριμένες</th>
        <th>Υπό Αξιολόγηση</th>
        <th>Απορριφθείσες</th>
        <th>Ποσοστό Έγκρισης</th>
      </tr>
      <?php if ($departmentRows === []): ?>
        <tr>
          <td colspan="7">Δεν υπάρχουν υποβληθείσες αιτήσεις για το επιλεγμένο φίλτρο.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($departmentRows as $row): ?>
          <tr>
            <td><?= h($row['department_name']) ?></td>
            <td><?= h($row['school_name']) ?></td>
            <td><?= (int)$row['total'] ?></td>
            <td><?= (int)$row['approved'] ?></td>
            <td><?= (int)$row['pending'] ?></td>
            <td><?= (int)$row['rejected'] ?></td>
            <td><?= reportPercent((int)$row['approved'], (int)$row['total']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </table>

    <br />

    <table border="1">
      <tr>
        <th>Μάθημα</th>
        <th>Αιτήσεις</th>
      </tr>
      <?php if ($courseRows === []): ?>
        <tr>
          <td colspan="2">Δεν υπάρχουν δεδομένα.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($courseRows as $row): ?>
          <tr>
            <td><?= h($row['course_name']) ?></td>
            <td><?= (int)$row['total'] ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </table>
  </body>
</html>
    <?php
    exit;
}

$navFullName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?: 'Administrator';
$navAvatarSrc = resolveAdminAvatarSrc($pdo, (int)($_SESSION['user_id'] ?? 0));
$brandingContext = adminGetBrandingContext($pdo);
$adminBrandText = $brandingContext['brand_text'];
$adminLogo = $brandingContext['logo'];
$adminFavicon = $brandingContext['favicon'];
?>
<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= h($adminBrandText) ?> | Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php if ($adminFavicon): ?>
    <link rel="icon" href="<?= h($adminFavicon) ?>" />
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../assets/css/adminlte.css" />
    <link rel="stylesheet" href="../../assets/css/admin-pages.css" />
    <link rel="stylesheet" href="../../assets/css/admin-ui.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" crossorigin="anonymous" />
  </head>
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">

      <header class="app-header">
        <nav class="navbar navbar-expand bg-body h-100" aria-label="Primary">
          <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="toggleSidebar(event)"><i class="bi bi-list"></i></a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="index.php" class="nav-link"><i class="bi bi-house me-1"></i>Dashboard</a>
            </li>
            <li class="nav-item d-none d-md-block">
              <span class="nav-link text-secondary"><i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>Reports</span>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item d-none d-md-flex align-items-center me-1">
              <button class="btn btn-outline-secondary btn-sm me-1" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Εκτύπωση
              </button>
              <a class="btn btn-outline-success btn-sm" href="<?= h($exportUrl) ?>" target="report-export-frame">
                <i class="bi bi-file-earmark-excel me-1"></i>Export
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display:none"></i>
              </a>
            </li>
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img src="<?= h($navAvatarSrc) ?>" class="user-image rounded-circle shadow" alt="<?= h($navFullName) ?>" />
                <span class="d-none d-md-inline"><?= h($navFullName) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="<?= h($navAvatarSrc) ?>" class="rounded-circle shadow" alt="<?= h($navFullName) ?>" />
                  <p><?= h($navFullName) ?><small>Διαχειριστής Συστήματος</small></p>
                </li>
                <li class="user-footer">
                  <a href="my_profile.php" class="btn btn-default btn-flat"><i class="bi bi-person me-1"></i>Προφίλ</a>
                  <a href="../../logout.php" class="btn btn-default btn-flat float-end"><i class="bi bi-box-arrow-right me-1"></i>Αποσύνδεση</a>
                </li>
              </ul>
            </li>
          </ul>
          </div>
        </nav>
      </header>

      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
          <a href="index.php" class="brand-link">
            <img src="<?= h($adminLogo) ?>" alt="Logo" class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light"><?= h($adminBrandText) ?></span>
          </a>
        </div>
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
              <li class="nav-header">ΚΥΡΙΟ ΜΕΝΟΥ</li>
              <li class="nav-item"><a href="index.php" class="nav-link"><i class="nav-icon bi bi-speedometer2"></i><p>Dashboard</p></a></li>
              <li class="nav-header">ΔΙΑΧΕΙΡΙΣΗ</li>
              <li class="nav-item"><a href="manage_users.php" class="nav-link"><i class="nav-icon bi bi-people"></i><p>Manage Users</p></a></li>
              <li class="nav-item">
                <a href="#" class="nav-link" role="button">
                  <i class="nav-icon bi bi-clipboard-check"></i>
                  <p>Manage Recruitment<i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item"><a href="manage_recruitment.php#applications" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Αιτήσεις</p></a></li>
                  <li class="nav-item"><a href="manage_recruitment.php#schools" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Σχολές</p></a></li>
                  <li class="nav-item"><a href="manage_recruitment.php#departments" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Τμήματα</p></a></li>
                  <li class="nav-item"><a href="manage_recruitment.php#courses" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Μαθήματα</p></a></li>
                  <li class="nav-item"><a href="manage_recruitment.php#period" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Περίοδος Αιτήσεων</p></a></li>
                </ul>
              </li>
              <li class="nav-item"><a href="configure_system.php" class="nav-link"><i class="nav-icon bi bi-gear"></i><p>Configure System</p></a></li>
              <li class="nav-item"><a href="report.php" class="nav-link active"><i class="nav-icon bi bi-bar-chart"></i><p>Reports</p></a></li>
              <li class="nav-header">ΛΟΓΑΡΙΑΣΜΟΣ</li>
              <li class="nav-item"><a href="my_profile.php" class="nav-link"><i class="nav-icon bi bi-person-circle"></i><p>My Profile</p></a></li>
              <li class="nav-header">ΑΛΛΑΓΗ ΕΝΟΤΗΤΑΣ</li>
              <li class="nav-item"><a href="../../module-select.php" class="nav-link"><i class="nav-icon bi bi-grid-3x3-gap-fill"></i><p>Switch Module</p></a></li>
            </ul>
          </nav>
        </div>
      </aside>

      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row align-items-center py-2">
              <div class="col">
                <h4 class="mb-0 fw-bold d-flex align-items-center gap-2">
                  <span class="admin-page-title-icon" style="background:#ede9fe;color:#6d28d9;">
                    <i class="bi bi-bar-chart-fill"></i>
                  </span>
                  Reports & Στατιστικά
                </h4>
              </div>
              <div class="col-auto d-flex align-items-center gap-2">
                <select class="form-select form-select-sm" style="width:auto;" id="periodFilter" onchange="applyPeriodFilter()">
                  <?php foreach ($periods as $period): ?>
                    <option value="<?= (int)$period['id'] ?>" <?= $selectedPeriodId === (int)$period['id'] ? 'selected' : '' ?>>
                      <?= h($period['name']) ?>
                    </option>
                  <?php endforeach; ?>
                  <option value="all" <?= $selectedPeriodId === null ? 'selected' : '' ?>>Όλες οι περίοδοι</option>
                </select>
                <ol class="breadcrumb mb-0 d-none d-md-flex">
                  <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                  <li class="breadcrumb-item active">Reports</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <div class="alert alert-light border d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
              <div>
                <strong><?= h($selectedPeriodLabel) ?></strong>
                <?php if ($selectedPeriodStatus !== null): ?>
                  <span class="text-secondary ms-2"><?= h($periodStatusMap[$selectedPeriodStatus] ?? $selectedPeriodStatus) ?></span>
                <?php else: ?>
                  <span class="text-secondary ms-2">Συγκεντρωτικά δεδομένα από όλες τις περιόδους</span>
                <?php endif; ?>
              </div>
              <div class="text-secondary small">
                Τα drafts δεν υπολογίζονται στα στατιστικά.
                <?php if ($draftCount > 0): ?>
                  Υπάρχουν <?= $draftCount ?> πρόχειρες αιτήσεις εκτός αναφοράς.
                <?php endif; ?>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="stat-icon-wrap mb-2" style="background:#dbeafe;color:#1d4ed8;"><i class="bi bi-file-earmark-text-fill"></i></div>
                  <div class="stat-value" id="kpiTotal"><?= $totalApplications ?></div>
                  <div class="stat-label">Σύνολο Αιτήσεων</div>
                  <div class="mt-1"><span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.75rem;"><?= $selectedPeriodId === null ? 'Όλες οι περίοδοι' : 'Επιλεγμένη περίοδος' ?></span></div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="stat-icon-wrap mb-2" style="background:#dcfce7;color:#15803d;"><i class="bi bi-check-circle-fill"></i></div>
                  <div class="stat-value" id="kpiApproved"><?= $approvedApplications ?></div>
                  <div class="stat-label">Εγκεκριμένες</div>
                  <div class="mt-1"><span class="badge bg-success bg-opacity-10 text-success" style="font-size:.75rem;"><?= reportPercent($approvedApplications, $totalApplications) ?></span></div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="stat-icon-wrap mb-2" style="background:#fef3c7;color:#b45309;"><i class="bi bi-hourglass-split"></i></div>
                  <div class="stat-value" id="kpiPending"><?= $pendingApplications ?></div>
                  <div class="stat-label">Υπό Αξιολόγηση</div>
                  <div class="mt-1"><span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.75rem;"><?= reportPercent($pendingApplications, $totalApplications) ?></span></div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="stat-icon-wrap mb-2" style="background:#fee2e2;color:#b91c1c;"><i class="bi bi-x-circle-fill"></i></div>
                  <div class="stat-value" id="kpiRejected"><?= $rejectedApplications ?></div>
                  <div class="stat-label">Απορριφθείσες</div>
                  <div class="mt-1"><span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:.75rem;"><?= reportPercent($rejectedApplications, $totalApplications) ?></span></div>
                </div>
              </div>
            </div>

            <div class="row g-4 mb-4">
              <div class="col-12 col-lg-8">
                <div class="chart-card bg-body shadow-sm">
                  <div class="chart-card-header">
                    <div>
                      <span class="fw-semibold">Εξέλιξη Αιτήσεων ανά Μήνα</span>
                      <small class="text-secondary d-block">Βάσει υποβληθεισών αιτήσεων της επιλεγμένης περιόδου</small>
                    </div>
                    <div class="d-flex gap-2">
                      <span class="badge bg-success bg-opacity-10 text-success">Εγκεκριμένες</span>
                      <span class="badge bg-warning bg-opacity-10 text-warning">Υπό Αξιολόγηση</span>
                      <span class="badge bg-danger bg-opacity-10 text-danger">Απορριφθείσες</span>
                    </div>
                  </div>
                  <div class="chart-card-body">
                    <div id="chart-timeline"></div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-4">
                <div class="chart-card bg-body shadow-sm">
                  <div class="chart-card-header">
                    <div>
                      <span class="fw-semibold">Κατανομή Αιτήσεων</span>
                      <small class="text-secondary d-block">Ανά κατάσταση</small>
                    </div>
                  </div>
                  <div class="chart-card-body">
                    <div id="chart-donut"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row g-4 mb-4">
              <div class="col-12 col-lg-6">
                <div class="chart-card bg-body shadow-sm">
                  <div class="chart-card-header">
                    <span class="fw-semibold">Αιτήσεις ανά Τμήμα</span>
                  </div>
                  <div class="chart-card-body">
                    <div id="chart-by-dept"></div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-6">
                <div class="chart-card bg-body shadow-sm">
                  <div class="chart-card-header">
                    <span class="fw-semibold">Αιτήσεις ανά Μάθημα</span>
                  </div>
                  <div class="chart-card-body">
                    <div id="chart-by-course"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="admin-table-card bg-body shadow-sm">
              <div class="admin-table-toolbar">
                <span class="fw-semibold">Συγκεντρωτικός Πίνακας Αιτήσεων</span>
                <a class="btn btn-outline-success btn-sm" href="<?= h($exportUrl) ?>" target="report-export-frame">
                  <i class="bi bi-file-earmark-excel me-1"></i>Εξαγωγή σε Excel
                </a>
              </div>
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Τμήμα</th>
                      <th>Σχολή</th>
                      <th class="text-center">Σύνολο</th>
                      <th class="text-center">Εγκεκριμένες</th>
                      <th class="text-center">Υπό Αξιολόγηση</th>
                      <th class="text-center">Απορριφθείσες</th>
                      <th>Ποσοστό Έγκρισης</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($departmentRows === []): ?>
                      <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">Δεν υπάρχουν υποβληθείσες αιτήσεις για το επιλεγμένο φίλτρο.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($departmentRows as $row): ?>
                        <?php
                        $rowTotal = (int)$row['total'];
                        $rowApproved = (int)$row['approved'];
                        $rowPending = (int)$row['pending'];
                        $rowRejected = (int)$row['rejected'];
                        $approvalRate = $rowTotal > 0 ? ($rowApproved / $rowTotal) * 100 : 0;
                        ?>
                        <tr>
                          <td class="fw-semibold"><?= h($row['department_name']) ?></td>
                          <td class="text-secondary small"><?= h($row['school_name']) ?></td>
                          <td class="text-center"><?= $rowTotal ?></td>
                          <td class="text-center text-success fw-semibold"><?= $rowApproved ?></td>
                          <td class="text-center text-warning fw-semibold"><?= $rowPending ?></td>
                          <td class="text-center text-danger fw-semibold"><?= $rowRejected ?></td>
                          <td>
                            <div class="d-flex align-items-center gap-2">
                              <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar bg-success" style="width:<?= max(0, min(100, $approvalRate)) ?>%"></div>
                              </div>
                              <small class="fw-semibold"><?= reportPercent($rowApproved, $rowTotal) ?></small>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                  <tfoot class="table-light fw-bold">
                    <tr>
                      <td>Σύνολο</td>
                      <td></td>
                      <td class="text-center"><?= $totalApplications ?></td>
                      <td class="text-center text-success"><?= $approvedApplications ?></td>
                      <td class="text-center text-warning"><?= $pendingApplications ?></td>
                      <td class="text-center text-danger"><?= $rejectedApplications ?></td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="progress flex-grow-1" style="height:6px;">
                            <div class="progress-bar bg-success" style="width:<?= max(0, min(100, $totalApplications > 0 ? ($approvedApplications / $totalApplications) * 100 : 0)) ?>%"></div>
                          </div>
                          <small><?= reportPercent($approvedApplications, $totalApplications) ?></small>
                        </div>
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

          </div>
        </div>
      </main>

      <?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

    </div>

    <iframe name="report-export-frame" style="display:none;" title="Report export"></iframe>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/js/adminlte.js" defer></script>
    <script src="../../assets/js/changes.js" defer></script>
    <script>
      const reportData = <?= json_encode($reportData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

      document.addEventListener('DOMContentLoaded', function () {
        const sw = document.querySelector('.sidebar-wrapper');
        if (sw && OverlayScrollbarsGlobal?.OverlayScrollbars) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sw, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
          });
        }

        renderCharts();
      });

      function applyPeriodFilter() {
        const periodValue = document.getElementById('periodFilter').value;
        const target = new URL(window.location.href);
        target.searchParams.set('period_id', periodValue);
        window.location.href = target.toString();
      }

      function renderCharts() {
        renderTimelineChart();
        renderDonutChart();
        renderDepartmentChart();
        renderCourseChart();
      }

      function renderTimelineChart() {
        const categories = reportData.timeline.categories.length
          ? reportData.timeline.categories
          : ['Χωρίς δεδομένα'];

        const approved = reportData.timeline.approved.length
          ? reportData.timeline.approved
          : [0];

        const pending = reportData.timeline.pending.length
          ? reportData.timeline.pending
          : [0];

        const rejected = reportData.timeline.rejected.length
          ? reportData.timeline.rejected
          : [0];

        new ApexCharts(document.querySelector('#chart-timeline'), {
          series: [
            { name: 'Εγκεκριμένες', data: approved },
            { name: 'Υπό Αξιολόγηση', data: pending },
            { name: 'Απορριφθείσες', data: rejected }
          ],
          chart: { type: 'area', height: 260, toolbar: { show: false } },
          colors: ['#22c55e', '#f59e0b', '#ef4444'],
          stroke: { curve: 'smooth', width: 2 },
          fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
          dataLabels: { enabled: false },
          legend: { position: 'bottom', offsetY: 5 },
          xaxis: {
            categories: categories,
            axisBorder: { show: false },
            axisTicks: { show: false }
          },
          yaxis: {
            min: 0,
            forceNiceScale: true
          },
          grid: { strokeDashArray: 4, borderColor: 'rgba(0,0,0,0.06)' },
          tooltip: { shared: true, intersect: false }
        }).render();
      }

      function renderDonutChart() {
        const total = reportData.donut.approved + reportData.donut.pending + reportData.donut.rejected;
        const hasDonutData = total > 0;

        new ApexCharts(document.querySelector('#chart-donut'), {
          series: hasDonutData
            ? [reportData.donut.approved, reportData.donut.pending, reportData.donut.rejected]
            : [1],
          labels: hasDonutData
            ? ['Εγκεκριμένες', 'Υπό Αξιολόγηση', 'Απορριφθείσες']
            : ['Χωρίς δεδομένα'],
          chart: { type: 'donut', height: 260 },
          colors: hasDonutData ? ['#22c55e', '#f59e0b', '#ef4444'] : ['#cbd5e1'],
          legend: { position: 'bottom' },
          dataLabels: {
            formatter: function (val) {
              return hasDonutData ? Math.round(val) + '%' : '';
            }
          },
          plotOptions: { pie: { donut: { size: '70%' } } },
          tooltip: {
            y: {
              formatter: function (value) {
                return hasDonutData ? value + ' αιτήσεις' : 'Δεν υπάρχουν δεδομένα';
              }
            }
          }
        }).render();
      }

      function renderDepartmentChart() {
        const categories = reportData.department.categories.length
          ? reportData.department.categories
          : ['Χωρίς δεδομένα'];

        const totals = reportData.department.totals.length
          ? reportData.department.totals
          : [0];

        new ApexCharts(document.querySelector('#chart-by-dept'), {
          series: [{ name: 'Αιτήσεις', data: totals }],
          chart: { type: 'bar', height: 260, toolbar: { show: false } },
          colors: ['#6d28d9'],
          plotOptions: { bar: { borderRadius: 6, horizontal: true } },
          dataLabels: { enabled: false },
          xaxis: {
            categories: categories,
            axisBorder: { show: false }
          },
          yaxis: {
            labels: { maxWidth: 220 }
          },
          grid: { strokeDashArray: 4, borderColor: 'rgba(0,0,0,0.06)' },
          tooltip: { y: { formatter: function (value) { return value + ' αιτήσεις'; } } }
        }).render();
      }

      function renderCourseChart() {
        const categories = reportData.course.categories.length
          ? reportData.course.categories
          : ['Χωρίς δεδομένα'];

        const totals = reportData.course.totals.length
          ? reportData.course.totals
          : [0];

        new ApexCharts(document.querySelector('#chart-by-course'), {
          series: [{ name: 'Αιτήσεις', data: totals }],
          chart: { type: 'bar', height: 260, toolbar: { show: false } },
          colors: ['#0d6efd'],
          plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
          dataLabels: { enabled: false },
          xaxis: {
            categories: categories,
            axisBorder: { show: false },
            axisTicks: { show: false }
          },
          yaxis: {
            min: 0,
            forceNiceScale: true
          },
          grid: { strokeDashArray: 4, borderColor: 'rgba(0,0,0,0.06)' },
          tooltip: { y: { formatter: function (value) { return value + ' αιτήσεις'; } } }
        }).render();
      }
    </script>
  </body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../database/db.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatNumber(int $value): string
{
    return number_format($value, 0, ',', '.');
}

function formatDate(?string $value, bool $includeTime = false): string
{
    if (!$value) {
        return '—';
    }

    try {
        $date = new DateTimeImmutable($value);
        return $date->format($includeTime ? 'd/m/Y H:i' : 'd/m/Y');
    } catch (Throwable $e) {
        return '—';
    }
}

function truncateText(?string $value, int $limit = 110): string
{
    $value = trim((string)$value);

    if ($value === '') {
        return '';
    }

    if (mb_strlen($value, 'UTF-8') <= $limit) {
        return $value;
    }

    return rtrim(mb_substr($value, 0, $limit - 1, 'UTF-8')) . '…';
}

function applicationStatusMeta(string $status): array
{
    return match ($status) {
        'draft' => ['label' => 'Πρόχειρη', 'class' => 'text-bg-secondary'],
        'submitted' => ['label' => 'Υποβλήθηκε', 'class' => 'text-bg-primary'],
        'under_review' => ['label' => 'Σε αξιολόγηση', 'class' => 'text-bg-warning'],
        'accepted' => ['label' => 'Εγκρίθηκε', 'class' => 'text-bg-success'],
        'rejected' => ['label' => 'Απορρίφθηκε', 'class' => 'text-bg-danger'],
        'withdrawn' => ['label' => 'Αποσύρθηκε', 'class' => 'text-bg-dark'],
        default => ['label' => 'Άγνωστο', 'class' => 'text-bg-light'],
    };
}

function periodStatusMeta(string $status): array
{
    return match ($status) {
        'active' => ['label' => 'Ενεργή', 'class' => 'text-bg-success'],
        'planning' => ['label' => 'Προγραμματισμός', 'class' => 'text-bg-warning'],
        'closed' => ['label' => 'Κλειστή', 'class' => 'text-bg-secondary'],
        'archived' => ['label' => 'Αρχείο', 'class' => 'text-bg-dark'],
        default => ['label' => 'Άγνωστο', 'class' => 'text-bg-light'],
    };
}

function roleLabel(string $role): string
{
    return $role === 'admin' ? 'Admin' : 'Χρήστης';
}

function resolveAdminAvatarSrc(PDO $pdo, int $userId): string
{
  $fallback = '../../assets/images/avatar.png';

  if ($userId <= 0) {
    return $fallback;
  }

  try {
    $stmt = $pdo->prepare('SELECT profilepic, profilepic_mime FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch();

    if (is_array($row) && !empty($row['profilepic'])) {
      $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      $mime = in_array((string)($row['profilepic_mime'] ?? ''), $allowedMimeTypes, true)
        ? (string)$row['profilepic_mime']
        : 'image/jpeg';

      return 'data:' . $mime . ';base64,' . base64_encode((string)$row['profilepic']);
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

$adminFullName = trim((string)(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')));
$adminFullName = $adminFullName !== '' ? $adminFullName : 'Administrator';
$adminId = (int)($_SESSION['user_id'] ?? 0);
$adminAvatarSrc = resolveAdminAvatarSrc($pdo, $adminId);

$stats = [
    'total_users' => 0,
    'total_admins' => 0,
    'submitted_applications' => 0,
    'pending_applications' => 0,
    'draft_applications' => 0,
    'approved_applications' => 0,
    'rejected_applications' => 0,
    'total_departments' => 0,
    'total_courses' => 0,
    'published_announcements' => 0,
    'active_periods' => 0,
];
$currentPeriod = null;
$recentApplications = [];
$recentUsers = [];
$notifications = [];
$unreadNotifications = 0;
$dashboardError = null;

try {
    $statsRow = $pdo->query(
        "
        SELECT
            (SELECT COUNT(*) FROM users) AS total_users,
            (SELECT COUNT(*) FROM users WHERE role = 'admin') AS total_admins,
            (SELECT COUNT(*) FROM candidate_applications WHERE status <> 'draft') AS submitted_applications,
            (SELECT COUNT(*) FROM candidate_applications WHERE status IN ('submitted', 'under_review')) AS pending_applications,
            (SELECT COUNT(*) FROM candidate_applications WHERE status = 'draft') AS draft_applications,
            (SELECT COUNT(*) FROM candidate_applications WHERE status = 'accepted') AS approved_applications,
            (SELECT COUNT(*) FROM candidate_applications WHERE status IN ('rejected', 'withdrawn')) AS rejected_applications,
            (SELECT COUNT(*) FROM departments) AS total_departments,
            (SELECT COUNT(*) FROM courses) AS total_courses,
            (SELECT COUNT(*) FROM job_announcements WHERE status = 'published') AS published_announcements,
            (SELECT COUNT(*) FROM recruitment_periods WHERE status = 'active') AS active_periods
        "
    )->fetch();

    if (is_array($statsRow)) {
        foreach ($stats as $key => $value) {
            $stats[$key] = (int)($statsRow[$key] ?? 0);
        }
    }

    $currentPeriod = $pdo->query(
        "
        SELECT id, name, status, start_date, end_date, description
        FROM recruitment_periods
        ORDER BY
            CASE
                WHEN status = 'active' THEN 0
                WHEN status = 'planning' THEN 1
                WHEN status = 'closed' THEN 2
                ELSE 3
            END,
            start_date DESC,
            id DESC
        LIMIT 1
        "
    )->fetch() ?: null;

    $recentApplications = $pdo->query(
        "
        SELECT
            ca.id,
            ca.status,
            COALESCE(ca.submitted_at, ca.created_at) AS activity_at,
            u.first_name,
            u.last_name,
            ja.title,
            d.name AS department_name
        FROM candidate_applications ca
        INNER JOIN users u ON u.id = ca.candidate_id
        INNER JOIN job_announcements ja ON ja.id = ca.announcement_id
        LEFT JOIN departments d ON d.id = ja.department_id
        ORDER BY COALESCE(ca.submitted_at, ca.created_at) DESC, ca.id DESC
        LIMIT 5
        "
    )->fetchAll();

    $recentUsers = $pdo->query(
        "
        SELECT id, first_name, last_name, email, role, created_at
        FROM users
        ORDER BY created_at DESC, id DESC
        LIMIT 5
        "
    )->fetchAll();

    if ($adminId > 0) {
        $notificationStmt = $pdo->prepare(
            "
            SELECT id, title, message, is_read, created_at
            FROM notifications
            WHERE user_id = :user_id
            ORDER BY created_at DESC, id DESC
            LIMIT 5
            "
        );
        $notificationStmt->execute([':user_id' => $adminId]);
        $notifications = $notificationStmt->fetchAll();

        $unreadStmt = $pdo->prepare(
            "
            SELECT COUNT(*) AS unread_count
            FROM notifications
            WHERE user_id = :user_id AND is_read = 0
            "
        );
        $unreadStmt->execute([':user_id' => $adminId]);
        $unreadNotifications = (int)($unreadStmt->fetch()['unread_count'] ?? 0);
    }
} catch (Throwable $e) {
    $dashboardError = 'Δεν ήταν δυνατή η φόρτωση των δεδομένων του dashboard.';
}

$candidateCount = max(0, $stats['total_users'] - $stats['total_admins']);
$periodMeta = periodStatusMeta((string)($currentPeriod['status'] ?? 'archived'));
$periodTimeline = 'Δεν υπάρχει καταχωρημένη περίοδος αιτήσεων.';

if ($currentPeriod) {
    try {
        $today = new DateTimeImmutable('today');
        $startDate = new DateTimeImmutable((string)$currentPeriod['start_date']);
        $endDate = new DateTimeImmutable((string)$currentPeriod['end_date']);

        if ($today < $startDate) {
            $periodTimeline = 'Έναρξη στις ' . $startDate->format('d/m/Y') . '.';
        } elseif ($today > $endDate) {
            $periodTimeline = 'Η περίοδος έληξε στις ' . $endDate->format('d/m/Y') . '.';
        } else {
            $periodTimeline = 'Η περίοδος λήγει στις ' . $endDate->format('d/m/Y') . '.';
        }
    } catch (Throwable $e) {
        $periodTimeline = 'Η περίοδος είναι διαθέσιμη, αλλά οι ημερομηνίες της δεν μπόρεσαν να διαβαστούν.';
    }
}
?>
<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../assets/css/adminlte.css" />
    <link rel="stylesheet" href="../../assets/css/card-nav.css" />
    <link rel="stylesheet" href="../../assets/css/admin-pages.css" />
    <link rel="stylesheet" href="../../assets/css/admin-ui.css" />
  </head>
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">

      <header class="app-header">
        <nav class="navbar navbar-expand bg-body h-100" aria-label="Primary">
          <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="#" role="button" onclick="toggleSidebar(event)" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="index.php" class="nav-link fw-semibold">
                <i class="bi bi-house me-1"></i>Dashboard
              </a>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
              <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-label="Notifications" id="notifBell">
                <i class="bi bi-bell-fill"></i>
                <?php if ($unreadNotifications > 0): ?>
                <span class="navbar-badge badge text-bg-warning" id="notifBadge"><?= h((string)min($unreadNotifications, 99)) ?></span>
                <?php endif; ?>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                  <span class="fw-semibold small"><?= h((string)$unreadNotifications) ?> μη αναγνωσμένες ειδοποιήσεις</span>
                  <?php if ($unreadNotifications > 0): ?>
                  <button type="button" class="btn btn-link btn-sm p-0 text-secondary text-decoration-none" onclick="markAllNotificationsRead()">Σήμανση ως αναγνωσμένα</button>
                  <?php endif; ?>
                </div>
                <?php if ($notifications === []): ?>
                <span class="dropdown-item text-secondary small py-3">
                  Δεν υπάρχουν ειδοποιήσεις για τον τρέχοντα διαχειριστή.
                </span>
                <?php else: ?>
                  <?php foreach ($notifications as $notification): ?>
                  <a href="manage_recruitment.php" class="dropdown-item <?= (int)$notification['is_read'] === 0 ? 'fw-semibold' : '' ?>">
                    <i class="bi bi-person-fill-add me-2 <?= (int)$notification['is_read'] === 0 ? 'text-primary' : 'text-secondary' ?>"></i>
                    <?= h(truncateText((string)$notification['title'], 52)) ?>
                    <span class="float-end text-secondary fs-7"><?= h(formatDate((string)$notification['created_at'], true)) ?></span>
                    <?php if (trim((string)($notification['message'] ?? '')) !== ''): ?>
                    <span class="d-block text-secondary small mt-1 fw-normal"><?= h(truncateText((string)$notification['message'], 78)) ?></span>
                    <?php endif; ?>
                  </a>
                  <div class="dropdown-divider"></div>
                  <?php endforeach; ?>
                <?php endif; ?>
                <a href="manage_recruitment.php" class="dropdown-item dropdown-footer">Προβολή αιτήσεων</a>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen" aria-label="Fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display:none"></i>
              </a>
            </li>
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img src="<?= h($adminAvatarSrc) ?>" class="user-image rounded-circle shadow" alt="<?= h($adminFullName) ?>" />
                <span class="d-none d-md-inline"><?= h($adminFullName) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="<?= h($adminAvatarSrc) ?>" class="rounded-circle shadow" alt="<?= h($adminFullName) ?>" />
                  <p>
                    <?= h($adminFullName) ?>
                    <small>Διαχειριστής Συστήματος</small>
                  </p>
                </li>
                <li class="user-footer">
                  <a href="my_profile.php" class="btn btn-default btn-flat">
                    <i class="bi bi-person me-1"></i>Προφίλ
                  </a>
                  <a href="../../logout.php" class="btn btn-default btn-flat float-end">
                    <i class="bi bi-box-arrow-right me-1"></i>Αποσύνδεση
                  </a>
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
            <img src="../../assets/images/AdminLTELogo.png" alt="Logo" class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light">Admin Panel</span>
          </a>
        </div>
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false">
              <li class="nav-header">ΚΥΡΙΟ ΜΕΝΟΥ</li>
              <li class="nav-item">
                <a href="index.php" class="nav-link active">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <li class="nav-header">ΔΙΑΧΕΙΡΙΣΗ</li>
              <li class="nav-item">
                <a href="manage_users.php" class="nav-link">
                  <i class="nav-icon bi bi-people"></i>
                  <p>Manage Users</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="manage_recruitment.php" class="nav-link">
                  <i class="nav-icon bi bi-clipboard-check"></i>
                  <p>
                    Manage Recruitment
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="manage_recruitment.php#applications" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i><p>Αιτήσεις</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="manage_recruitment.php#schools" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i><p>Σχολές</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="manage_recruitment.php#departments" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i><p>Τμήματα</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="manage_recruitment.php#courses" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i><p>Μαθήματα</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="manage_recruitment.php#period" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i><p>Περίοδος Αιτήσεων</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="configure_system.php" class="nav-link">
                  <i class="nav-icon bi bi-gear"></i>
                  <p>Configure System</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="report.php" class="nav-link">
                  <i class="nav-icon bi bi-bar-chart"></i>
                  <p>Reports</p>
                </a>
              </li>
              <li class="nav-header">ΛΟΓΑΡΙΑΣΜΟΣ</li>
              <li class="nav-item">
                <a href="my_profile.php" class="nav-link">
                  <i class="nav-icon bi bi-person-circle"></i>
                  <p>My Profile</p>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </aside>

      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row justify-content-center mt-2">
              <div class="col-12 col-md-10 col-lg-8">
                <div class="text-center py-1">
                  <h2 class="dashboard-hero-title mb-0">
                    <span class="dashboard-hero-pill">
                      <span class="dashboard-word-wrap">
                        <span id="dashWordA" class="dashboard-word">Admin</span>
                      </span>
                      <span class="dashboard-word-wrap">
                        <span id="dashWordB" class="dashboard-word">Dashboard</span>
                      </span>
                    </span>
                  </h2>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <div class="row justify-content-center mb-4">
              <div class="col-12 col-lg-10">
                <div class="config-card bg-body shadow-sm mb-2">
                  <div class="config-card-body py-3 px-4 text-center">
                    <div class="fw-semibold text-dark mb-2" style="font-size:1.04rem;">
                      Καλώς ήρθες ξανά, <?= h($adminFullName) ?>.
                    </div>
                    <p class="text-secondary mb-0">
                      Η πλατφόρμα τρέχει με
                      <span class="fw-semibold text-primary"><?= h(formatNumber($stats['published_announcements'])) ?> ενεργές ανακοινώσεις</span>,
                      <span class="fw-semibold text-success"><?= h(formatNumber($stats['submitted_applications'])) ?> καταχωρημένες αιτήσεις</span>
                      και
                      <span class="fw-semibold text-warning-emphasis"><?= h(formatNumber($stats['active_periods'])) ?> ενεργή περίοδο</span>
                      στρατολόγησης.
                    </p>
                  </div>
                </div>
                <?php if ($dashboardError !== null): ?>
                <div class="alert alert-warning shadow-sm mb-0" role="alert">
                  <i class="bi bi-exclamation-triangle me-2"></i><?= h($dashboardError) ?>
                </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="row g-4 justify-content-center">
              <div class="col-12 col-sm-6 col-xl-3">
                <a href="manage_users.php" class="admin-nav-card admin-nav-card-users">
                  <div class="card h-100 text-center shadow-sm">
                    <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                      <div class="admin-nav-icon-wrap">
                        <i class="bi bi-person-gear"></i>
                      </div>
                      <h5 class="card-title fw-bold mb-1">Manage Users</h5>
                      <p class="card-text text-secondary small mb-1">
                        Δημιουργία λογαριασμών, ενημέρωση στοιχείων και διαχείριση δικαιωμάτων
                      </p>
                      <span class="badge rounded-pill text-bg-light">
                        <?= h(formatNumber($stats['total_users'])) ?> χρήστες συνολικά
                      </span>
                    </div>
                  </div>
                </a>
              </div>

              <div class="col-12 col-sm-6 col-xl-3">
                <a href="manage_recruitment.php" class="admin-nav-card admin-nav-card-recruitment">
                  <div class="card h-100 text-center shadow-sm">
                    <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                      <div class="admin-nav-icon-wrap">
                        <i class="bi bi-diagram-3-fill"></i>
                      </div>
                      <h5 class="card-title fw-bold mb-1">Manage Recruitment</h5>
                      <p class="card-text text-secondary small mb-1">
                        Οργάνωση ροής πρόσληψης: αιτήσεις, αξιολόγηση και ακαδημαϊκή δομή
                      </p>
                      <span class="badge rounded-pill text-bg-light">
                        <?= h(formatNumber($stats['pending_applications'])) ?> αιτήσεις σε εκκρεμότητα
                      </span>
                    </div>
                  </div>
                </a>
              </div>

              <div class="col-12 col-sm-6 col-xl-3">
                <a href="configure_system.php" class="admin-nav-card admin-nav-card-config">
                  <div class="card h-100 text-center shadow-sm">
                    <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                      <div class="admin-nav-icon-wrap">
                        <i class="bi bi-sliders2-vertical"></i>
                      </div>
                      <h5 class="card-title fw-bold mb-1">Configure System</h5>
                      <p class="card-text text-secondary small mb-1">
                        Ρυθμίσεις πλατφόρμας, branding και παράμετροι ενσωμάτωσης
                      </p>
                      <span class="badge rounded-pill text-bg-light">
                        <?= h(formatNumber($stats['active_periods'])) ?> ενεργή περίοδος
                      </span>
                    </div>
                  </div>
                </a>
              </div>

              <div class="col-12 col-sm-6 col-xl-3">
                <a href="report.php" class="admin-nav-card admin-nav-card-report">
                  <div class="card h-100 text-center shadow-sm">
                    <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                      <div class="admin-nav-icon-wrap">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i>
                      </div>
                      <h5 class="card-title fw-bold mb-1">Reports</h5>
                      <p class="card-text text-secondary small mb-1">
                        Αναφορές απόδοσης, δείκτες προόδου και εξαγωγή συγκεντρωτικών στοιχείων
                      </p>
                      <span class="badge rounded-pill text-bg-light">
                        <?= h(formatNumber($stats['published_announcements'])) ?> δημοσιευμένες ανακοινώσεις
                      </span>
                    </div>
                  </div>
                </a>
              </div>
            </div>

            <div class="row g-3 mt-2 justify-content-center">
              <div class="col-12 col-lg-10">
                <div class="row g-3">
                  <div class="col-6 col-md-3">
                    <div class="stat-card bg-body shadow-sm h-100">
                      <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrap" style="background:#dbeafe;color:#1d4ed8;">
                          <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div>
                          <div class="stat-value"><?= h(formatNumber($stats['total_users'])) ?></div>
                          <div class="stat-label">Σύνολο Χρηστών</div>
                          <div class="small text-secondary">
                            <?= h(formatNumber($stats['total_admins'])) ?> admin / <?= h(formatNumber($candidateCount)) ?> χρήστες
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="stat-card bg-body shadow-sm h-100">
                      <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrap" style="background:#dcfce7;color:#15803d;">
                          <i class="bi bi-inbox-fill"></i>
                        </div>
                        <div>
                          <div class="stat-value"><?= h(formatNumber($stats['submitted_applications'])) ?></div>
                          <div class="stat-label">Υποβλημένες Αιτήσεις</div>
                          <div class="small text-secondary">
                            <?= h(formatNumber($stats['pending_applications'])) ?> περιμένουν αξιολόγηση
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="stat-card bg-body shadow-sm h-100">
                      <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrap" style="background:#fef3c7;color:#b45309;">
                          <i class="bi bi-building"></i>
                        </div>
                        <div>
                          <div class="stat-value"><?= h(formatNumber($stats['total_departments'])) ?></div>
                          <div class="stat-label">Τμήματα</div>
                          <div class="small text-secondary">
                            <?= h(formatNumber($stats['published_announcements'])) ?> ενεργές ανακοινώσεις
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="stat-card bg-body shadow-sm h-100">
                      <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrap" style="background:#ede9fe;color:#6d28d9;">
                          <i class="bi bi-journal-bookmark-fill"></i>
                        </div>
                        <div>
                          <div class="stat-value"><?= h(formatNumber($stats['total_courses'])) ?></div>
                          <div class="stat-label">Μαθήματα</div>
                          <div class="small text-secondary">
                            <?= h(formatNumber($stats['approved_applications'])) ?> εγκρίσεις μέχρι τώρα
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row g-3 mt-2 justify-content-center">
              <div class="col-12 col-lg-4">
                <div class="card shadow-sm h-100">
                  <div class="card-header bg-body border-0 d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0 fw-semibold">
                      <i class="bi bi-calendar2-week me-2 text-primary"></i>Τρέχουσα Περίοδος
                    </h3>
                    <span class="badge <?= h($periodMeta['class']) ?>"><?= h($periodMeta['label']) ?></span>
                  </div>
                  <div class="card-body">
                    <?php if ($currentPeriod): ?>
                    <h5 class="fw-bold mb-2"><?= h((string)$currentPeriod['name']) ?></h5>
                    <p class="text-secondary mb-2">
                      <?= h($periodTimeline) ?>
                    </p>
                    <div class="small text-secondary mb-3">
                      Από <?= h(formatDate((string)$currentPeriod['start_date'])) ?>
                      έως <?= h(formatDate((string)$currentPeriod['end_date'])) ?>
                    </div>
                    <?php if (trim((string)($currentPeriod['description'] ?? '')) !== ''): ?>
                    <p class="mb-0"><?= h(truncateText((string)$currentPeriod['description'], 180)) ?></p>
                    <?php else: ?>
                    <p class="mb-0 text-secondary">Δεν υπάρχει περιγραφή για την επιλεγμένη περίοδο.</p>
                    <?php endif; ?>
                    <?php else: ?>
                    <p class="mb-0 text-secondary">Δεν υπάρχουν ακόμη εγγραφές περιόδων αιτήσεων στη βάση δεδομένων.</p>
                    <?php endif; ?>
                  </div>
                  <div class="card-footer bg-body border-0 pt-0">
                    <div class="d-flex flex-column gap-2 small">
                      <div class="d-flex justify-content-between">
                        <span class="text-secondary">Πρόχειρες αιτήσεις</span>
                        <strong><?= h(formatNumber($stats['draft_applications'])) ?></strong>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-secondary">Εγκρίσεις</span>
                        <strong><?= h(formatNumber($stats['approved_applications'])) ?></strong>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-secondary">Απορρίψεις / αποσύρσεις</span>
                        <strong><?= h(formatNumber($stats['rejected_applications'])) ?></strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                  <div class="card-header bg-body border-0">
                    <h3 class="card-title mb-0 fw-semibold">
                      <i class="bi bi-clock-history me-2 text-success"></i>Πρόσφατες Αιτήσεις
                    </h3>
                  </div>
                  <div class="card-body p-0">
                    <?php if ($recentApplications === []): ?>
                    <div class="p-4 text-secondary">Δεν υπάρχουν ακόμη αιτήσεις στη βάση δεδομένων.</div>
                    <?php else: ?>
                      <?php foreach ($recentApplications as $index => $application): ?>
                        <?php $statusMeta = applicationStatusMeta((string)$application['status']); ?>
                        <div class="px-4 py-3<?= $index < count($recentApplications) - 1 ? ' border-bottom' : '' ?>">
                          <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                              <div class="fw-semibold"><?= h((string)$application['title']) ?></div>
                              <div class="small text-secondary">
                                <?= h(trim((string)$application['first_name'] . ' ' . (string)$application['last_name'])) ?>
                                <?php if (trim((string)($application['department_name'] ?? '')) !== ''): ?>
                                · <?= h((string)$application['department_name']) ?>
                                <?php endif; ?>
                              </div>
                              <div class="small text-secondary mt-1">
                                <?= h(formatDate((string)$application['activity_at'], true)) ?>
                              </div>
                            </div>
                            <span class="badge <?= h($statusMeta['class']) ?>"><?= h($statusMeta['label']) ?></span>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="row g-3 mt-2 justify-content-center">
              <div class="col-12 col-lg-10">
                <div class="card shadow-sm">
                  <div class="card-header bg-body border-0">
                    <h3 class="card-title mb-0 fw-semibold">
                      <i class="bi bi-person-plus-fill me-2 text-warning"></i>Τελευταίες Εγγραφές Χρηστών
                    </h3>
                  </div>
                  <div class="card-body p-0">
                    <?php if ($recentUsers === []): ?>
                    <div class="p-4 text-secondary">Δεν υπάρχουν ακόμη χρήστες στη βάση δεδομένων.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                      <table class="table align-middle mb-0">
                        <thead>
                          <tr>
                            <th class="ps-4">Χρήστης</th>
                            <th>Email</th>
                            <th>Ρόλος</th>
                            <th class="text-end pe-4">Ημερομηνία εγγραφής</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($recentUsers as $user): ?>
                          <tr>
                            <td class="ps-4 fw-semibold"><?= h(trim((string)$user['first_name'] . ' ' . (string)$user['last_name'])) ?></td>
                            <td><?= h((string)$user['email']) ?></td>
                            <td>
                              <span class="badge <?= $user['role'] === 'admin' ? 'text-bg-primary' : 'text-bg-light' ?>">
                                <?= h(roleLabel((string)$user['role'])) ?>
                              </span>
                            </td>
                            <td class="text-end pe-4 text-secondary"><?= h(formatDate((string)$user['created_at'], true)) ?></td>
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

      <?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/js/adminlte.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js" defer></script>
    <script src="../../assets/js/changes.js" defer></script>
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
          });
        }
      });

      function markAllNotificationsRead() {
        fetch('../../api/notifications.php?action=mark_all_read', { method: 'POST' })
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (data.success) {
              var badge = document.getElementById('notifBadge');
              if (badge) badge.remove();
              document.querySelectorAll('.dropdown-menu .fw-semibold.dropdown-item').forEach(function(el) {
                el.classList.remove('fw-semibold');
              });
              document.querySelectorAll('.bi-person-fill-add.text-primary').forEach(function(el) {
                el.classList.replace('text-primary', 'text-secondary');
              });
              var markBtn = document.querySelector('[onclick="markAllNotificationsRead()"]');
              if (markBtn) markBtn.remove();
              var header = document.querySelector('.dropdown-menu .fw-semibold.small');
              if (header) header.textContent = '0 μη αναγνωσμένες ειδοποιήσεις';
            }
          })
          .catch(function() {});
      }
    </script>
  </body>
</html>

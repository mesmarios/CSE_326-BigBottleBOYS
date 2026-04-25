<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/admin-branding.php';
require_once __DIR__ . '/role-access.php';

$enrollmentPageTitle = $enrollmentPageTitle ?? 'Enrollment Module';
$enrollmentPageHeading = $enrollmentPageHeading ?? $enrollmentPageTitle;
$enrollmentPageDescription = $enrollmentPageDescription ?? '';
$enrollmentActivePage = $enrollmentActivePage ?? 'dashboard';
$enrollmentExtraHead = $enrollmentExtraHead ?? '';
$enrollmentCurrentRole = normalizeAppRole((string)($_SESSION['role'] ?? 'candidate'));
$enrollmentCanManageSync = roleHasEnrollmentManagerAccess($enrollmentCurrentRole);
$brandingContext = adminGetBrandingContext($pdo);
$enrollmentBrandText = $brandingContext['brand_text'];
$enrollmentLogo = $brandingContext['logo'];
$enrollmentFavicon = $brandingContext['favicon'];
$enrollmentFullName = trim((string)(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')));
$enrollmentFullName = $enrollmentFullName !== '' ? $enrollmentFullName : 'User';

if (!function_exists('enrollmentResolveAvatarSrc')) {
    function enrollmentResolveAvatarSrc(PDO $pdo, int $userId): string
    {
        $fallback = '../../assets/images/avatar.png';

        if ($userId <= 0) {
            return $fallback;
        }

        try {
            $stmt = $pdo->prepare('SELECT profilepic, profilepic_mime FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($row) && !empty($row['profilepic'])) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $mime = in_array((string)($row['profilepic_mime'] ?? ''), $allowedMimeTypes, true)
                    ? (string)$row['profilepic_mime']
                    : 'image/jpeg';

                return 'data:' . $mime . ';base64,' . base64_encode((string)$row['profilepic']);
            }
        } catch (Throwable $e) {
        }

        $fileMatches = glob(__DIR__ . '/../uploads/profile_pics/user_' . $userId . '.*');
        if (is_array($fileMatches) && $fileMatches !== []) {
            $filePath = $fileMatches[0];
            $fileVersion = (int)@filemtime($filePath) ?: time();
            return '../../uploads/profile_pics/' . rawurlencode(basename($filePath)) . '?v=' . $fileVersion;
        }

        return $fallback;
    }
}

if (!function_exists('enrollmentH')) {
    function enrollmentH(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$enrollmentAvatarSrc = enrollmentResolveAvatarSrc($pdo, (int)($_SESSION['user_id'] ?? 0));
$enrollmentFlash = $_SESSION['enrollment_flash'] ?? null;
unset($_SESSION['enrollment_flash']);

$navItems = [
    'dashboard' => ['label' => 'Dashboard', 'href' => 'index.php', 'icon' => 'bi bi-speedometer2', 'enabled' => true],
    'lms_sync' => ['label' => 'LMS Sync', 'href' => 'lms_sync.php', 'icon' => 'bi bi-arrow-repeat', 'enabled' => true],
    'full_sync' => ['label' => 'Full Sync', 'href' => 'full_sync.php', 'icon' => 'bi bi-cloud-check', 'enabled' => $enrollmentCanManageSync],
    'report' => ['label' => 'Report', 'href' => 'report.php', 'icon' => 'bi bi-bar-chart-line', 'enabled' => $enrollmentCanManageSync],
];
?>
<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= enrollmentH($enrollmentBrandText) ?> | <?= enrollmentH($enrollmentPageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php if ($enrollmentFavicon): ?>
    <link rel="icon" href="<?= enrollmentH($enrollmentFavicon) ?>" />
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../assets/css/adminlte.css" />
    <link rel="stylesheet" href="../../assets/css/admin-pages.css" />
    <link rel="stylesheet" href="../../assets/css/admin-ui.css" />
    <style>
      .enrollment-hero-card {
        border: 0;
        border-radius: 1rem;
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #38bdf8 100%);
        color: #fff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
      }

      .enrollment-kpi {
        border-radius: 1rem;
        border: 1px solid #e5e7eb;
      }

      .enrollment-kpi .icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
      }

      .enrollment-section-card {
        border-radius: 1rem;
        border: 1px solid #e5e7eb;
      }

      .enrollment-nav-disabled {
        opacity: 0.52;
        cursor: not-allowed;
      }

      .enrollment-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
      }
    </style>
    <?= $enrollmentExtraHead ?>
  </head>
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
      <header class="app-header">
        <nav class="navbar navbar-expand bg-body h-100" aria-label="Primary">
          <div class="container-fluid">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link" href="#" onclick="toggleSidebar(event)" aria-label="Toggle sidebar">
                  <i class="bi bi-list"></i>
                </a>
              </li>
              <li class="nav-item d-none d-md-block">
                <a href="index.php" class="nav-link">
                  <i class="bi bi-house me-1"></i>Enrollment Module
                </a>
              </li>
              <li class="nav-item d-none d-md-block">
                <span class="nav-link text-secondary">
                  <i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i><?= enrollmentH($enrollmentPageTitle) ?>
                </span>
              </li>
            </ul>
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="fullscreen" aria-label="Fullscreen">
                  <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                  <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display:none"></i>
                </a>
              </li>
              <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                  <img src="<?= enrollmentH($enrollmentAvatarSrc) ?>" class="user-image rounded-circle shadow" alt="<?= enrollmentH($enrollmentFullName) ?>" />
                  <span class="d-none d-md-inline"><?= enrollmentH($enrollmentFullName) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                  <li class="user-header text-bg-primary">
                    <img src="<?= enrollmentH($enrollmentAvatarSrc) ?>" class="rounded-circle shadow" alt="<?= enrollmentH($enrollmentFullName) ?>" />
                    <p><?= enrollmentH($enrollmentFullName) ?><small><?= enrollmentH(appRoleLabel($enrollmentCurrentRole)) ?></small></p>
                  </li>
                  <li class="user-footer">
                    <?php if (roleCanAccessModule($enrollmentCurrentRole, 'recruitment')): ?>
                    <a href="../recruitmentModule/index.php" class="btn btn-default btn-flat"><i class="bi bi-arrow-left-right me-1"></i>Recruitment</a>
                    <?php else: ?>
                    <span class="btn btn-default btn-flat disabled" aria-disabled="true"><i class="bi bi-lock me-1"></i>Recruitment</span>
                    <?php endif; ?>
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
            <img src="<?= enrollmentH($enrollmentLogo) ?>" alt="Logo" class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light"><?= enrollmentH($enrollmentBrandText) ?></span>
          </a>
        </div>
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
              <li class="nav-header">ENROLLMENT</li>
              <?php foreach ($navItems as $key => $item): ?>
                <?php
                $isActive = $enrollmentActivePage === $key;
                $isEnabled = (bool)$item['enabled'];
                $linkClass = 'nav-link' . ($isActive ? ' active' : '') . (!$isEnabled ? ' enrollment-nav-disabled' : '');
                ?>
                <li class="nav-item">
                  <?php if ($isEnabled): ?>
                  <a href="<?= enrollmentH($item['href']) ?>" class="<?= enrollmentH($linkClass) ?>">
                    <i class="nav-icon <?= enrollmentH($item['icon']) ?>"></i><p><?= enrollmentH($item['label']) ?></p>
                  </a>
                  <?php else: ?>
                  <a href="#" class="<?= enrollmentH($linkClass) ?>" onclick="event.preventDefault()" aria-disabled="true">
                    <i class="nav-icon <?= enrollmentH($item['icon']) ?>"></i><p><?= enrollmentH($item['label']) ?> <span class="badge text-bg-secondary ms-1">Admin/HR</span></p>
                  </a>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
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
                  <span class="admin-page-title-icon" style="background:#dbeafe;color:#1d4ed8;">
                    <i class="bi bi-mortarboard-fill"></i>
                  </span>
                  <?= enrollmentH($enrollmentPageHeading) ?>
                </h4>
                <?php if ($enrollmentPageDescription !== ''): ?>
                <div class="text-secondary small mt-1"><?= enrollmentH($enrollmentPageDescription) ?></div>
                <?php endif; ?>
              </div>
              <div class="col-auto">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Enrollment</a></li>
                  <li class="breadcrumb-item active"><?= enrollmentH($enrollmentPageTitle) ?></li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
            <?php if (is_array($enrollmentFlash)): ?>
            <div class="alert alert-<?= enrollmentH((string)($enrollmentFlash['type'] ?? 'info')) ?> alert-dismissible fade show mb-3" role="alert">
              <?= enrollmentH((string)($enrollmentFlash['message'] ?? '')) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>


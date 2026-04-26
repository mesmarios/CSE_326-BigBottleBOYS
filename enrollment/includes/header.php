<?php
// Variables expected from the including page:
//   $pageTitle        string  — used in <title>
//   $adminBrandText   string  — sidebar/brand text
//   $adminLogo        string  — path to logo image
//   $adminFavicon     string|null
//   $enrollFullName   string  — logged-in user display name
//   $enrollAvatarSrc  string  — avatar image src

if (!function_exists('enrollResolveAvatarSrc')) {
    function enrollResolveAvatarSrc(PDO $pdo, int $userId): string
    {
        $fallback = '../assets/images/avatar.png';
        if ($userId <= 0) {
            return $fallback;
        }
        try {
            $stmt = $pdo->prepare('SELECT profilepic, profilepic_mime FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $row = $stmt->fetch();
            if (is_array($row) && !empty($row['profilepic'])) {
                $mime = in_array((string)($row['profilepic_mime'] ?? ''), ['image/jpeg','image/png','image/gif','image/webp'], true)
                    ? (string)$row['profilepic_mime'] : 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode((string)$row['profilepic']);
            }
        } catch (Throwable $e) {}
        $matches = glob(dirname(__DIR__, 2) . '/uploads/profile_pics/user_' . $userId . '.*');
        if (is_array($matches) && $matches !== []) {
            return '../uploads/profile_pics/' . rawurlencode(basename($matches[0])) . '?v=' . (int)@filemtime($matches[0]);
        }
        return $fallback;
    }
}
?>
<!doctype html>
<html lang="el">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?= htmlspecialchars($adminBrandText ?? 'CareerTrack', ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars($pageTitle ?? 'Enrollment', ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <?php if (!empty($adminFavicon)): ?>
  <link rel="icon" href="<?= htmlspecialchars($adminFavicon, ENT_QUOTES, 'UTF-8') ?>" />
  <?php endif; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="../assets/css/adminlte.css" />
  <link rel="stylesheet" href="../assets/css/card-nav.css" />
  <link rel="stylesheet" href="../assets/css/admin-pages.css" />
  <link rel="stylesheet" href="../assets/css/admin-ui.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" crossorigin="anonymous" />
  <style>
    .app-sidebar { transition: width .4s cubic-bezier(.4,0,.2,1), margin-left .4s cubic-bezier(.4,0,.2,1), transform .4s cubic-bezier(.4,0,.2,1) !important; }
    .app-wrapper > .app-main, .app-header { transition: margin-left .4s cubic-bezier(.4,0,.2,1) !important; }
    .sidebar-toggle-btn { padding:.35rem .6rem; border-radius:.4rem; transition: background-color .2s ease, box-shadow .2s ease; }
    .sidebar-toggle-btn:hover { background-color: rgba(0,0,0,.08); }
    .sidebar-toggle-icon { display:inline-block; font-size:1rem; transition: transform .4s cubic-bezier(.4,0,.2,1); will-change:transform; }
    body.sidebar-collapse .sidebar-toggle-icon { transform: rotate(180deg); }
  </style>
  <?php if (!empty($extra_head)) { echo $extra_head; } ?>
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
          <a href="dashboard.php" class="nav-link fw-semibold">
            <i class="bi bi-mortarboard me-1"></i>Enrollment
          </a>
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
            <img src="<?= htmlspecialchars($enrollAvatarSrc ?? '../assets/images/avatar.png', ENT_QUOTES, 'UTF-8') ?>"
                 class="user-image rounded-circle shadow"
                 alt="<?= htmlspecialchars($enrollFullName ?? '', ENT_QUOTES, 'UTF-8') ?>" />
            <span class="d-none d-md-inline"><?= htmlspecialchars($enrollFullName ?? '', ENT_QUOTES, 'UTF-8') ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
            <li class="user-header text-bg-primary">
              <img src="<?= htmlspecialchars($enrollAvatarSrc ?? '../assets/images/avatar.png', ENT_QUOTES, 'UTF-8') ?>"
                   class="rounded-circle shadow"
                   alt="<?= htmlspecialchars($enrollFullName ?? '', ENT_QUOTES, 'UTF-8') ?>" />
              <p>
                <?= htmlspecialchars($enrollFullName ?? '', ENT_QUOTES, 'UTF-8') ?>
                <small><?= htmlspecialchars(match ($_enrollment_role ?? '') {
                    'admin'    => 'Διαχειριστής',
                    'hr'       => 'HR Manager',
                    'ee_hired' => 'ΕΕ Μισθωμένος',
                    default    => 'Χρήστης',
                }, ENT_QUOTES, 'UTF-8') ?></small>
              </p>
            </li>
            <li class="user-footer">
              <a href="../logout.php" class="btn btn-default btn-flat float-end">
                <i class="bi bi-box-arrow-right me-1"></i>Αποσύνδεση
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>

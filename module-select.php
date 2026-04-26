<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$role = (string)$_SESSION['role'];

if (!in_array($role, ['admin', 'hr'], true)) {
    $fallback = match ($role) {
        'evaluator', 'candidate' => 'modules/recruitmentModule/index.php',
        'ee_hired'               => 'enrollment/dashboard.php',
        default                  => 'login.php',
    };
    header('Location: ' . $fallback);
    exit;
}

require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/includes/admin-branding.php';

$brandingContext = adminGetBrandingContext($pdo);
$adminBrandText  = $brandingContext['brand_text'];
$adminFavicon    = $brandingContext['favicon'];

// adminGetBrandingContext returns paths relative to modules/admin/ depth (../../assets/...)
// module-select.php is at root so we fix to assets/...
$adminLogo = (string)$brandingContext['logo'];
$adminLogo = preg_replace('#^\.\./\.\./assets/#', 'assets/', $adminLogo) ?? $adminLogo;

$fullName = trim((string)(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')));
if ($fullName === '') {
    $fullName = $role === 'admin' ? 'Administrator' : 'HR Manager';
}

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="el">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?= h($adminBrandText) ?> | Επιλογή Ενότητας</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <?php if ($adminFavicon): ?>
  <link rel="icon" href="<?= h($adminFavicon) ?>" />
  <?php endif; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="assets/css/adminlte.css" />
  <link rel="stylesheet" href="assets/css/admin-pages.css" />
  <link rel="stylesheet" href="assets/css/card-nav.css" />
  <link rel="stylesheet" href="assets/css/admin-ui.css" />
  <style>
    body { min-height: 100vh; display: flex; flex-direction: column; }
    .module-select-wrapper {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem 1rem;
    }
    .module-select-brand {
      display: flex;
      align-items: center;
      gap: .75rem;
      margin-bottom: 2rem;
    }
    .module-select-brand img { height: 42px; width: auto; }
    .module-select-brand span { font-size: 1.4rem; font-weight: 600; color: #0f4c81; }
    .module-select-greeting {
      font-size: 1rem;
      color: #6c757d;
      margin-bottom: 2.5rem;
      text-align: center;
    }
    .module-cards { max-width: 700px; width: 100%; }
    .module-select-footer {
      text-align: center;
      padding: 1.5rem;
      font-size: .82rem;
      color: #9aa3af;
    }
    .module-select-logout {
      display: inline-flex;
      align-items: center;
      gap: .45rem;
      padding: .7rem 1.1rem;
      border: 1px solid rgba(15, 76, 129, 0.14);
      border-radius: 999px;
      background: #fff;
      color: #49617a;
      font-weight: 600;
      text-decoration: none;
      box-shadow: 0 10px 24px rgba(15, 76, 129, 0.08);
      transition: transform .18s ease, box-shadow .18s ease, color .18s ease, border-color .18s ease;
    }
    .module-select-logout:hover {
      color: #0f4c81;
      border-color: rgba(15, 76, 129, 0.28);
      box-shadow: 0 14px 28px rgba(15, 76, 129, 0.12);
      transform: translateY(-1px);
      text-decoration: none;
    }
    .module-select-logout i {
      font-size: .95rem;
    }
  </style>
</head>
<body class="bg-body-tertiary">

<div class="module-select-wrapper">

  <div class="module-select-brand">
    <img src="<?= h($adminLogo) ?>" alt="Logo" />
    <span><?= h($adminBrandText) ?></span>
  </div>

  <p class="module-select-greeting">
    Καλώς ήρθες, <strong><?= h($fullName) ?></strong>. Επίλεξε την ενότητα που θέλεις να εισέλθεις.
  </p>

  <div class="module-cards">
    <div class="row g-4 justify-content-center">

      <?php if ($role === 'admin'): ?>
      <div class="col-12 col-sm-6">
        <a href="modules/admin/index.php" class="admin-nav-card admin-nav-card-users">
          <div class="card h-100 text-center shadow-sm">
            <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
              <div class="admin-nav-icon-wrap">
                <i class="bi bi-shield-lock-fill"></i>
              </div>
              <h5 class="card-title fw-bold mb-1">Admin Module</h5>
              <p class="card-text text-secondary small mb-0">
                Διαχείριση χρηστών, προσλήψεων και ρυθμίσεων συστήματος
              </p>
            </div>
          </div>
        </a>
      </div>
      <?php endif; ?>

      <?php if ($role === 'hr'): ?>
      <div class="col-12 col-sm-6">
        <a href="modules/recruitmentModule/index.php" class="admin-nav-card admin-nav-card-recruitment">
          <div class="card h-100 text-center shadow-sm">
            <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
              <div class="admin-nav-icon-wrap">
                <i class="bi bi-clipboard-check-fill"></i>
              </div>
              <h5 class="card-title fw-bold mb-1">Recruitment Module</h5>
              <p class="card-text text-secondary small mb-0">
                Αιτήσεις υποψηφίων, αξιολόγηση και διαχείριση περιόδων
              </p>
            </div>
          </div>
        </a>
      </div>
      <?php endif; ?>

      <div class="col-12 col-sm-6">
        <a href="enrollment/dashboard.php" class="admin-nav-card admin-nav-card-config">
          <div class="card h-100 text-center shadow-sm">
            <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
              <div class="admin-nav-icon-wrap">
                <i class="bi bi-mortarboard-fill"></i>
              </div>
              <h5 class="card-title fw-bold mb-1">Enrollment Module</h5>
              <p class="card-text text-secondary small mb-0">
                Διαχείριση εγγραφών, LMS sync και πρόσβαση Moodle για ΕΕ
              </p>
            </div>
          </div>
        </a>
      </div>

    </div>
  </div>

</div>

<div class="module-select-footer">
  <a href="logout.php" class="module-select-logout">
    <i class="bi bi-box-arrow-right me-1"></i>Αποσύνδεση
  </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js" defer></script>
<script src="assets/js/changes.js" defer></script>
</body>
</html>

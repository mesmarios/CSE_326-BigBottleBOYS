<?php
require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/admin-branding.php';
require_once __DIR__ . '/../../includes/maintenance-mode.php';

// DB Backup download
if (isset($_GET['action']) && $_GET['action'] === 'db_backup') {
    require_once __DIR__ . '/../../database/db.php';
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $sql = "-- DB Backup: " . date('Y-m-d H:i:s') . "\nSET FOREIGN_KEY_CHECKS=0;\n\n";
    foreach ($tables as $table) {
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
        $sql .= $create[1] . ";\n\n";
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v), array_values($row));
            $sql .= "INSERT INTO `$table` VALUES (" . implode(',', $vals) . ");\n";
        }
        $sql .= "\n";
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="backup_' . date('Ymd_His') . '.sql"');
    header('Content-Length: ' . strlen($sql));
    echo $sql;
    exit;
}

// General settings save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_general_settings') {
    $appName = trim((string)($_POST['app_name'] ?? ''));
    $appSlogan = trim((string)($_POST['app_slogan'] ?? ''));
    $appDescription = trim((string)($_POST['app_description'] ?? ''));
    $adminEmail = trim((string)($_POST['admin_email'] ?? ''));
    $supportPhone = trim((string)($_POST['support_phone'] ?? ''));

    if ($appName === '') {
        $_SESSION['configure_system_flash'] = [
            'type' => 'danger',
            'message' => 'Το όνομα εφαρμογής είναι υποχρεωτικό.',
        ];
        header('Location: configure_system.php');
        exit;
    }

    if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['configure_system_flash'] = [
            'type' => 'danger',
            'message' => 'Το email διαχειριστή δεν είναι έγκυρο.',
        ];
        header('Location: configure_system.php');
        exit;
    }

    try {
        adminSaveGeneralSettings($pdo, [
            'app_name' => $appName,
            'app_slogan' => $appSlogan,
            'app_description' => $appDescription,
            'admin_email' => $adminEmail,
            'support_phone' => $supportPhone,
        ]);

        $_SESSION['configure_system_flash'] = [
            'type' => 'success',
            'message' => 'Οι γενικές ρυθμίσεις αποθηκεύτηκαν επιτυχώς.',
        ];
    } catch (Throwable $e) {
        $_SESSION['configure_system_flash'] = [
            'type' => 'danger',
            'message' => 'Δεν ήταν δυνατή η αποθήκευση των γενικών ρυθμίσεων.',
        ];
    }

    header('Location: configure_system.php');
    exit;
}

// Maintenance mode toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_maintenance') {
    $enable = ($_POST['enable'] ?? '') === '1';
    $toggleSuccess = setMaintenanceMode($enable);
    $_SESSION['configure_system_flash'] = [
        'type' => $toggleSuccess ? 'success' : 'danger',
        'message' => $toggleSuccess
            ? ($enable
                ? 'Η λειτουργία συντήρησης ενεργοποιήθηκε.'
                : 'Η λειτουργία συντήρησης απενεργοποιήθηκε.')
            : 'Δεν ήταν δυνατή η ενημέρωση της λειτουργίας συντήρησης.',
    ];
    header('Location: configure_system.php');
    exit;
}

$maintenanceActive = isMaintenanceModeActive();
$maintenanceFlash = $_SESSION['configure_system_flash'] ?? null;
unset($_SESSION['configure_system_flash']);
$brandingError   = null;
$brandingSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_branding') {
    $assetsDir = __DIR__ . '/../../assets/images/';

    if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowedLogo = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/svg+xml' => 'svg', 'image/gif' => 'gif'];
        $mime = mime_content_type($_FILES['logo']['tmp_name']);
        if (isset($allowedLogo[$mime])) {
            foreach (glob($assetsDir . 'site-logo.*') as $old) { @unlink($old); }
            move_uploaded_file($_FILES['logo']['tmp_name'], $assetsDir . 'site-logo.' . $allowedLogo[$mime]);
        } else {
            $brandingError = 'Μη έγκυρος τύπος λογοτύπου. Επιτρέπονται PNG, JPG, SVG, GIF.';
        }
    }

    if (!$brandingError && !empty($_FILES['favicon']['tmp_name']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION));
        $allowedFav = ['ico', 'png', 'jpg', 'jpeg'];
        if (in_array($ext, $allowedFav, true)) {
            foreach (glob($assetsDir . 'site-favicon.*') as $old) { @unlink($old); }
            move_uploaded_file($_FILES['favicon']['tmp_name'], $assetsDir . 'site-favicon.' . $ext);
        } else {
            $brandingError = 'Μη έγκυρος τύπος favicon. Επιτρέπονται ICO, PNG, JPG.';
        }
    }

    if (!$brandingError) {
        $brandingSuccess = true;
    }
}

$brandingContext = adminGetBrandingContext($pdo);
$generalSettings = $brandingContext['settings'];
$brandText = $brandingContext['brand_text'];
$currentLogo = $brandingContext['logo'];
$currentFavicon = $brandingContext['favicon'];

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

$navFullName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?: 'Administrator';
$navAvatarSrc = resolveAdminAvatarSrc($pdo, (int)($_SESSION['user_id'] ?? 0));
?>
<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= htmlspecialchars($brandText, ENT_QUOTES, 'UTF-8') ?> | Configure System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php if ($currentFavicon): ?>
    <link rel="icon" href="<?= htmlspecialchars($currentFavicon) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../assets/css/adminlte.css" />
    <link rel="stylesheet" href="../../assets/css/admin-pages.css" />
    <link rel="stylesheet" href="../../assets/css/admin-ui.css" />
  </head>
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">

      <!-- ===== NAVBAR ===== -->
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
              <span class="nav-link text-secondary"><i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>Configure System</span>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display:none"></i>
              </a>
            </li>
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img src="<?= htmlspecialchars($navAvatarSrc, ENT_QUOTES, 'UTF-8') ?>" class="user-image rounded-circle shadow" alt="<?= htmlspecialchars($navFullName, ENT_QUOTES, 'UTF-8') ?>" />
                <span class="d-none d-md-inline"><?= htmlspecialchars($navFullName, ENT_QUOTES, 'UTF-8') ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="<?= htmlspecialchars($navAvatarSrc, ENT_QUOTES, 'UTF-8') ?>" class="rounded-circle shadow" alt="<?= htmlspecialchars($navFullName, ENT_QUOTES, 'UTF-8') ?>" />
                  <p><?= htmlspecialchars($navFullName, ENT_QUOTES, 'UTF-8') ?><small>Διαχειριστής Συστήματος</small></p>
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

      <!-- ===== SIDEBAR ===== -->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
          <a href="index.php" class="brand-link">
            <img src="<?= htmlspecialchars($currentLogo) ?>" alt="Logo" class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light"><?= htmlspecialchars($brandText, ENT_QUOTES, 'UTF-8') ?></span>
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
              <li class="nav-item"><a href="configure_system.php" class="nav-link active"><i class="nav-icon bi bi-gear"></i><p>Configure System</p></a></li>
              <li class="nav-item"><a href="report.php" class="nav-link"><i class="nav-icon bi bi-bar-chart"></i><p>Reports</p></a></li>
              <li class="nav-header">ΛΟΓΑΡΙΑΣΜΟΣ</li>
              <li class="nav-item"><a href="my_profile.php" class="nav-link"><i class="nav-icon bi bi-person-circle"></i><p>My Profile</p></a></li>
              <li class="nav-header">ΑΛΛΑΓΗ ΕΝΟΤΗΤΑΣ</li>
              <li class="nav-item"><a href="../../module-select.php" class="nav-link"><i class="nav-icon bi bi-grid-3x3-gap-fill"></i><p>Switch Module</p></a></li>
            </ul>
          </nav>
        </div>
      </aside>

      <!-- ===== MAIN ===== -->
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row align-items-center py-2">
              <div class="col">
                <h4 class="mb-0 fw-bold d-flex align-items-center gap-2">
                  <span class="admin-page-title-icon" style="background:#fef3c7;color:#b45309;">
                    <i class="bi bi-gear-fill"></i>
                  </span>
                  Configure System
                </h4>
              </div>
              <div class="col-auto">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                  <li class="breadcrumb-item active">Configure System</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <div class="alert alert-success alert-dismissible fade d-none mb-3" id="saveAlert" role="alert">
              <i class="bi bi-check-circle me-2"></i>Οι ρυθμίσεις αποθηκεύτηκαν επιτυχώς.
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php if ($maintenanceFlash): ?>
            <div class="alert alert-<?= htmlspecialchars((string)$maintenanceFlash['type'], ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show mb-3" role="alert">
              <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars((string)$maintenanceFlash['message'], ENT_QUOTES, 'UTF-8') ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row g-4">
              <div class="col-12 col-xl-8">

                <!-- General Settings -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-sliders text-primary"></i>
                    Γενικές Ρυθμίσεις
                  </div>
                  <div class="config-card-body">
                    <form method="POST" action="configure_system.php">
                      <input type="hidden" name="action" value="save_general_settings">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Όνομα Εφαρμογής <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" name="app_name" value="<?= htmlspecialchars((string)$generalSettings['app_name'], ENT_QUOTES, 'UTF-8') ?>" required />
                          <div class="form-text">Το όνομα που εμφανίζεται στην κεφαλίδα και τον τίτλο.</div>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Υπότιτλος / Slogan</label>
                          <input type="text" class="form-control" name="app_slogan" value="<?= htmlspecialchars((string)$generalSettings['app_slogan'], ENT_QUOTES, 'UTF-8') ?>" />
                        </div>
                        <div class="col-12">
                          <label class="form-label fw-semibold">Περιγραφή Εφαρμογής</label>
                          <textarea class="form-control" rows="2" name="app_description"><?= htmlspecialchars((string)$generalSettings['app_description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Email Διαχειριστή <span class="text-danger">*</span></label>
                          <input type="email" class="form-control" name="admin_email" value="<?= htmlspecialchars((string)$generalSettings['admin_email'], ENT_QUOTES, 'UTF-8') ?>" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Τηλέφωνο Υποστήριξης</label>
                          <input type="tel" class="form-control" name="support_phone" value="<?= htmlspecialchars((string)$generalSettings['support_phone'], ENT_QUOTES, 'UTF-8') ?>" />
                        </div>
                        <div class="col-12">
                          <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-1"></i>Αποθήκευση
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Branding / Theme -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-palette text-purple" style="color:#6d28d9;"></i>
                    Branding & Θέμα
                  </div>
                  <div class="config-card-body">
                    <?php if ($brandingError): ?>
                    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($brandingError) ?></div>
                    <?php elseif ($brandingSuccess): ?>
                    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Το λογότυπο/favicon αποθηκεύτηκε επιτυχώς.</div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                      <input type="hidden" name="action" value="upload_branding">
                      <div class="row g-3">
                        <!-- Logo upload -->
                        <div class="col-12">
                          <label class="form-label fw-semibold">Λογότυπο</label>
                          <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="border rounded p-2" style="background:#f8fafc;">
                              <img src="<?= htmlspecialchars($currentLogo) ?>" alt="Current Logo" style="height:48px;object-fit:contain;" id="logoPreview" />
                            </div>
                            <div>
                              <label class="btn btn-outline-secondary btn-sm mb-1">
                                <i class="bi bi-upload me-1"></i>Αλλαγή Λογοτύπου
                                <input type="file" name="logo" accept="image/*" class="d-none" onchange="previewLogo(this)" />
                              </label>
                              <div class="form-text">Συνιστώμενο μέγεθος: 200×50px. PNG ή SVG.</div>
                            </div>
                          </div>
                        </div>
                        <!-- Favicon -->
                        <div class="col-12">
                          <label class="form-label fw-semibold">Favicon</label>
                          <div class="d-flex align-items-center gap-3">
                            <div class="border rounded p-2" style="background:#f8fafc;width:40px;height:40px;display:flex;align-items:center;justify-content:center;" id="faviconPreviewWrap">
                              <?php if ($currentFavicon): ?>
                              <img src="<?= htmlspecialchars($currentFavicon) ?>" alt="Favicon" style="width:24px;height:24px;object-fit:contain;" id="faviconPreview" />
                              <?php else: ?>
                              <i class="bi bi-globe text-secondary" id="faviconPreview"></i>
                              <?php endif; ?>
                            </div>
                            <div>
                              <label class="btn btn-outline-secondary btn-sm mb-1">
                                <i class="bi bi-upload me-1"></i>Αλλαγή Favicon
                                <input type="file" name="favicon" accept="image/*,.ico" class="d-none" onchange="previewFavicon(this)" />
                              </label>
                              <div class="form-text">ICO, PNG ή JPG. Συνιστώμενο: 32×32px.</div>
                            </div>
                          </div>
                        </div>
                        <div class="col-12">
                          <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-1"></i>Αποθήκευση
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Moodle Integration -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-plug text-success"></i>
                    Ενσωμάτωση Moodle
                  </div>
                  <div class="config-card-body">
                    <form>
                      <div class="row g-3">
                        <div class="col-12">
                          <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" id="moodleEnabled" checked />
                            <label class="form-check-label fw-semibold" for="moodleEnabled">
                              Ενεργοποίηση σύνδεσης με Moodle
                            </label>
                          </div>
                          <small class="text-secondary">Επιτρέπει τον συγχρονισμό χρηστών και μαθημάτων από το Moodle.</small>
                        </div>
                        <div class="col-12">
                          <label class="form-label fw-semibold">URL Moodle <span class="text-danger">*</span></label>
                          <input type="url" class="form-control" value="https://moodle.university.gr" placeholder="https://moodle.youruniversity.gr" />
                        </div>
                        <div class="col-12">
                          <label class="form-label fw-semibold">API Token <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="moodleToken" value="abc123xyz789token" />
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleMoodleToken()">
                              <i class="bi bi-eye" id="moodleTokenIcon"></i>
                            </button>
                            <button class="btn btn-outline-primary" type="button" title="Δοκιμή σύνδεσης" onclick="testMoodleConn()">
                              <i class="bi bi-wifi me-1"></i>Δοκιμή
                            </button>
                          </div>
                          <div class="form-text">Μπορείτε να βρείτε το token στις ρυθμίσεις χρήστη του Moodle.</div>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">ID Κατηγορίας Μαθημάτων</label>
                          <input type="number" class="form-control" value="12" placeholder="π.χ. 12" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Συχνότητα Συγχρονισμού</label>
                          <select class="form-select">
                            <option>Κάθε ώρα</option>
                            <option selected>Κάθε 6 ώρες</option>
                            <option>Καθημερινά</option>
                            <option>Χειροκίνητα</option>
                          </select>
                        </div>
                        <!-- Connection status indicator -->
                        <div class="col-12" id="moodleConnStatus" style="display:none;">
                          <div class="alert alert-success mb-0 py-2">
                            <i class="bi bi-check-circle me-2"></i>Επιτυχής σύνδεση με το Moodle!
                          </div>
                        </div>
                        <div class="col-12">
                          <button type="button" class="btn btn-primary" onclick="showSaveAlert()">
                            <i class="bi bi-floppy me-1"></i>Αποθήκευση
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

              </div>

              <!-- Right column: System Info + Quick Actions -->
              <div class="col-12 col-xl-4">

                <!-- System Info -->
                <div class="config-card bg-body shadow-sm mb-4">
                  <div class="config-card-header">
                    <i class="bi bi-info-circle text-info"></i>
                    Πληροφορίες Συστήματος
                  </div>
                  <div class="config-card-body p-0">
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-secondary small">PHP Version</span>
                        <span class="fw-semibold small">8.2.x</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-secondary small">Database</span>
                        <span class="fw-semibold small">MySQL 8.0</span>
                      </li>
                    </ul>
                  </div>
                </div>

                <!-- Maintenance -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-wrench text-warning"></i>
                    Συντήρηση Συστήματος
                  </div>
                  <div class="config-card-body">
                    <div class="d-grid gap-2">
                      <button type="button" class="btn btn-outline-secondary btn-sm text-start" disabled>
                        <i class="bi bi-arrow-clockwise me-2 text-primary"></i>Εκκαθάριση Cache
                      </button>
                      <a href="?action=db_backup" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-database me-2 text-success"></i>Δημιουργία Αντιγράφου DB
                      </a>
                      <button type="button" class="btn btn-outline-secondary btn-sm text-start" disabled>
                        <i class="bi bi-file-earmark-text me-2 text-info"></i>Λήψη Αρχείων Καταγραφής
                      </button>
                    </div>
                    <hr class="my-3" />
                    <form method="POST" action="configure_system.php" id="maintenanceToggleForm">
                      <input type="hidden" name="action" value="toggle_maintenance" />
                      <input type="hidden" name="enable" id="maintenanceModeValue" value="<?= $maintenanceActive ? '1' : '0' ?>" />
                      <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" id="maintenanceMode" <?= $maintenanceActive ? 'checked' : '' ?> onchange="toggleMaintenance(this)" />
                        <label class="form-check-label text-danger fw-semibold" for="maintenanceMode">
                          Λειτουργία Συντήρησης
                        </label>
                      </div>
                    </form>
                    <small class="text-secondary d-block">Ενεργοποιεί σελίδα συντήρησης για όλους τους χρήστες εκτός του admin.</small>
                    <?php if ($maintenanceActive): ?>
                    <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small"><i class="bi bi-cone-striped me-1"></i>Η λειτουργία συντήρησης είναι <strong>ενεργή</strong>.</div>
                    <?php else: ?>
                    <div class="alert alert-light border py-1 px-2 mt-2 mb-0 small text-secondary"><i class="bi bi-check-circle me-1 text-success"></i>Η λειτουργία συντήρησης είναι ανενεργή.</div>
                    <?php endif; ?>
                  </div>
                </div>

              </div>
            </div>
            <!-- end row -->

          </div>
        </div>
      </main>

      <?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/js/adminlte.js" defer></script>
    <script src="../../assets/js/changes.js" defer></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const sw = document.querySelector('.sidebar-wrapper');
        if (sw && OverlayScrollbarsGlobal?.OverlayScrollbars) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sw, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
          });
        }
      });

      function showSaveAlert() {
        var el = document.getElementById('saveAlert');
        el.classList.remove('d-none');
        el.classList.add('show');
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        setTimeout(function () { el.classList.remove('show'); setTimeout(function () { el.classList.add('d-none'); }, 200); }, 3500);
      }

      function toggleMoodleToken() {
        var inp = document.getElementById('moodleToken');
        var icon = document.getElementById('moodleTokenIcon');
        if (inp.type === 'password') {
          inp.type = 'text';
          icon.className = 'bi bi-eye-slash';
        } else {
          inp.type = 'password';
          icon.className = 'bi bi-eye';
        }
      }

      function testMoodleConn() {
        var status = document.getElementById('moodleConnStatus');
        status.style.display = 'block';
        setTimeout(function () { status.style.display = 'none'; }, 4000);
      }

      function toggleMaintenance(toggle) {
        var form = document.getElementById('maintenanceToggleForm');
        var valueInput = document.getElementById('maintenanceModeValue');

        if (!form || !valueInput || !toggle) {
          return;
        }

        valueInput.value = toggle.checked ? '1' : '0';
        toggle.disabled = true;
        form.submit();
      }

      function previewLogo(input) {
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
            document.getElementById('logoPreview').src = e.target.result;
          };
          reader.readAsDataURL(input.files[0]);
        }
      }

      function previewFavicon(input) {
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
            var wrap = document.getElementById('faviconPreviewWrap');
            wrap.innerHTML = '<img src="' + e.target.result + '" alt="Favicon" style="width:24px;height:24px;object-fit:contain;" id="faviconPreview" />';
          };
          reader.readAsDataURL(input.files[0]);
        }
      }
    </script>
  </body>
</html>

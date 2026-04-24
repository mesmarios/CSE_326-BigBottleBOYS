<?php
require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/admin-branding.php';
$pdo = getDBConnection();

// ── CRUD action handlers ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id <= 0) {
            header('Location: manage_users.php?msg=' . urlencode('Μη έγκυρο αναγνωριστικό χρήστη.') . '&mtype=danger');
            exit;
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$id]);

            if ($stmt->rowCount() < 1) {
                header('Location: manage_users.php?msg=' . urlencode('Ο χρήστης δεν βρέθηκε.') . '&mtype=danger');
                exit;
            }

            header('Location: manage_users.php?msg=' . urlencode('Ο χρήστης διαγράφηκε επιτυχώς.') . '&mtype=success');
            exit;
        } catch (Throwable $e) {
            header('Location: manage_users.php?msg=' . urlencode('Αποτυχία διαγραφής χρήστη. Δοκιμάστε ξανά.') . '&mtype=danger');
            exit;
        }

    } elseif ($action === 'add') {
        $fn    = trim($_POST['first_name'] ?? '');
        $ln    = trim($_POST['last_name']  ?? '');
        $email = trim($_POST['email']      ?? '');
        $phone = trim($_POST['phone']      ?? '') ?: null;
        $role  = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user';
        $pass  = $_POST['password'] ?? '';
        if ($fn && $ln && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($pass) >= 8) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (first_name,last_name,email,phone,role,password_hash) VALUES (?,?,?,?,?,?)')
                ->execute([$fn, $ln, $email, $phone, $role, $hash]);
            header('Location: manage_users.php?msg=' . urlencode('Ο χρήστης προστέθηκε επιτυχώς.') . '&mtype=success');
            exit;
        }
        header('Location: manage_users.php?msg=' . urlencode('Σφάλμα: Ελέγξτε τα στοιχεία (email, κωδικός ≥8 χαρακτήρες).') . '&mtype=danger');
        exit;

    } elseif ($action === 'edit') {
        $id      = (int)($_POST['user_id'] ?? 0);
        $fn      = trim($_POST['first_name'] ?? '');
        $ln      = trim($_POST['last_name']  ?? '');
        $email   = trim($_POST['email']      ?? '');
        $phone   = trim($_POST['phone']      ?? '') ?: null;
        $role    = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user';
        $newPass = trim($_POST['new_password'] ?? '');
        if ($id > 0 && $fn && $ln && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($newPass !== '' && strlen($newPass) >= 8) {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE users SET first_name=?,last_name=?,email=?,phone=?,role=?,password_hash=?,updated_at=NOW() WHERE id=?')
                    ->execute([$fn, $ln, $email, $phone, $role, $hash, $id]);
            } else {
                $pdo->prepare('UPDATE users SET first_name=?,last_name=?,email=?,phone=?,role=?,updated_at=NOW() WHERE id=?')
                    ->execute([$fn, $ln, $email, $phone, $role, $id]);
            }
            header('Location: manage_users.php?msg=' . urlencode('Τα στοιχεία αποθηκεύτηκαν επιτυχώς.') . '&mtype=success');
            exit;
        }
        header('Location: manage_users.php?msg=' . urlencode('Σφάλμα: Ελέγξτε τα στοιχεία.') . '&mtype=danger');
        exit;
    }
}

$flashMsg  = isset($_GET['msg'])   ? htmlspecialchars($_GET['msg']) : null;
$flashType = isset($_GET['mtype']) && in_array($_GET['mtype'], ['success', 'danger'], true)
    ? $_GET['mtype']
    : 'success';

function resolveAdminAvatarSrc(PDO $pdo, int $userId): string {
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

function resolveUserAvatarSrc(array $user): ?string {
  if (!empty($user['profilepic'])) {
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mime = in_array((string)($user['profilepic_mime'] ?? ''), $allowedMimeTypes, true)
      ? (string)$user['profilepic_mime']
      : 'image/jpeg';
    return 'data:' . $mime . ';base64,' . base64_encode((string)$user['profilepic']);
  }

  $userId = (int)($user['id'] ?? 0);
  if ($userId > 0) {
    $fileMatches = glob(__DIR__ . '/../../uploads/profile_pics/user_' . $userId . '.*');
    if (is_array($fileMatches) && $fileMatches !== []) {
      $filePath = $fileMatches[0];
      $fileVersion = (int)@filemtime($filePath) ?: time();
      return '../../uploads/profile_pics/' . rawurlencode(basename($filePath)) . '?v=' . $fileVersion;
    }
  }

  return null;
}

$navFullName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?: 'Administrator';
$navAvatarSrc = resolveAdminAvatarSrc($pdo, (int)($_SESSION['user_id'] ?? 0));
$brandingContext = adminGetBrandingContext($pdo);
$adminBrandText = $brandingContext['brand_text'];
$adminLogo = $brandingContext['logo'];
$adminFavicon = $brandingContext['favicon'];

$stmt = $pdo->query(
  "SELECT id, first_name, last_name, email, phone, role, created_at, profilepic, profilepic_mime
     FROM users ORDER BY created_at DESC"
);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalUsers   = count($users);
$adminCount   = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
$userCount    = $totalUsers - $adminCount;
$thisMonth    = date('Y-m');
$newThisMonth = count(array_filter($users, fn($u) => str_starts_with($u['created_at'], $thisMonth)));

function avatarInitials(string $f, string $l): string {
    return mb_strtoupper(mb_substr($f,0,1,'UTF-8') . mb_substr($l,0,1,'UTF-8'), 'UTF-8');
}
?>
<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= escape($adminBrandText) ?> | Manage Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php if ($adminFavicon): ?>
    <link rel="icon" href="<?= escape($adminFavicon) ?>" />
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../assets/css/adminlte.css" />
    <link rel="stylesheet" href="../../assets/css/admin-pages.css" />
    <link rel="stylesheet" href="../../assets/css/admin-ui.css" />
    <style>
      .btn-modal-cancel {
        transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
      }

      .btn-modal-cancel:hover,
      .btn-modal-cancel:focus-visible {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #fff;
      }
    </style>
  </head>
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">

      <!-- ===== NAVBAR ===== -->
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
                <i class="bi bi-house me-1"></i>Dashboard
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <span class="nav-link text-secondary">
                <i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>Manage Users
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
                <img src="<?= escape($navAvatarSrc) ?>" class="user-image rounded-circle shadow" alt="<?= escape($navFullName) ?>" />
                <span class="d-none d-md-inline"><?= escape($navFullName) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="<?= escape($navAvatarSrc) ?>" class="rounded-circle shadow" alt="<?= escape($navFullName) ?>" />
                  <p><?= escape($navFullName) ?><small>Διαχειριστής Συστήματος</small></p>
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
            <img src="<?= escape($adminLogo) ?>" alt="Logo" class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light"><?= escape($adminBrandText) ?></span>
          </a>
        </div>
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
              <li class="nav-header">ΚΥΡΙΟ ΜΕΝΟΥ</li>
              <li class="nav-item">
                <a href="index.php" class="nav-link"><i class="nav-icon bi bi-speedometer2"></i><p>Dashboard</p></a>
              </li>
              <li class="nav-header">ΔΙΑΧΕΙΡΙΣΗ</li>
              <li class="nav-item">
                <a href="manage_users.php" class="nav-link active"><i class="nav-icon bi bi-people"></i><p>Manage Users</p></a>
              </li>
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
              <li class="nav-item"><a href="report.php" class="nav-link"><i class="nav-icon bi bi-bar-chart"></i><p>Reports</p></a></li>
              <li class="nav-header">ΛΟΓΑΡΙΑΣΜΟΣ</li>
              <li class="nav-item"><a href="my_profile.php" class="nav-link"><i class="nav-icon bi bi-person-circle"></i><p>My Profile</p></a></li>
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
                  <span class="admin-page-title-icon" style="background:#dbeafe;color:#1d4ed8;">
                    <i class="bi bi-people-fill"></i>
                  </span>
                  Manage Users
                </h4>
              </div>
              <div class="col-auto">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                  <li class="breadcrumb-item active">Manage Users</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <?php if ($flashMsg): ?>
            <div class="alert alert-<?= $flashType ?> alert-dismissible fade show mb-3" role="alert">
              <i class="bi bi-<?= $flashType === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
              <?= $flashMsg ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Stats Row -->
            <div class="row g-3 mb-4">
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap" style="background:#dbeafe;color:#1d4ed8;"><i class="bi bi-people-fill"></i></div>
                    <div><div class="stat-value"><?= $totalUsers ?></div><div class="stat-label">Σύνολο Χρηστών</div></div>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap" style="background:#fee2e2;color:#b91c1c;"><i class="bi bi-shield-fill"></i></div>
                    <div><div class="stat-value"><?= $adminCount ?></div><div class="stat-label">Διαχειριστές</div></div>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap" style="background:#dcfce7;color:#15803d;"><i class="bi bi-person-fill"></i></div>
                    <div><div class="stat-value"><?= $userCount ?></div><div class="stat-label">Χρήστες</div></div>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap" style="background:#fef3c7;color:#b45309;"><i class="bi bi-person-plus-fill"></i></div>
                    <div><div class="stat-value"><?= $newThisMonth ?></div><div class="stat-label">Νέοι τον Μήνα</div></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Users Table -->
            <div class="admin-table-card bg-body shadow-sm">
              <div class="admin-table-toolbar">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <div class="admin-table-search">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control form-control-sm" placeholder="Αναζήτηση χρήστη..." id="userSearch" />
                  </div>
                  <select class="form-select form-select-sm" style="width:auto;" id="roleFilter">
                    <option value="">Όλοι οι ρόλοι</option>
                    <option value="admin">Admin</option>
                    <option value="user">Χρήστης</option>
                  </select>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openAddUserModal()">
                  <i class="bi bi-plus-lg me-1"></i>Προσθήκη Χρήστη
                </button>
              </div>

              <div class="table-responsive">
                <table class="table table-hover mb-0" id="usersTable">
                  <thead class="table-light">
                    <tr>
                      <th style="width:46px;"></th>
                      <th>Ονοματεπώνυμο</th>
                      <th>Email</th>
                      <th>Ρόλος</th>
                      <th>Ημ/νία Εγγραφής</th>
                      <th class="text-end">Ενέργειες</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="text-center text-secondary py-4">Δεν βρέθηκαν χρήστες.</td></tr>
                    <?php else: foreach ($users as $u):
                        $initials  = avatarInitials($u['first_name'], $u['last_name']);
                        $isAdmin   = $u['role'] === 'admin';
                        $avBg      = $isAdmin ? '#dbeafe' : '#dcfce7';
                        $avColor   = $isAdmin ? '#1d4ed8' : '#15803d';
                        $avatarSrc = resolveUserAvatarSrc($u);
                        $badgeCls  = $isAdmin ? 'badge-role-admin' : 'badge-role-applicant';
                        $roleLabel = $isAdmin ? 'Admin' : 'Χρήστης';
                        $fullName  = escape($u['first_name']) . ' ' . escape($u['last_name']);
                        $dateFmt   = date('d/m/Y', strtotime($u['created_at']));
                    ?>
                    <tr data-role="<?= escape($u['role']) ?>">
                      <td>
                        <?php if ($avatarSrc !== null): ?>
                        <img src="<?= escape($avatarSrc) ?>" alt="<?= $fullName ?>" class="table-avatar" />
                        <?php else: ?>
                        <div class="table-avatar-placeholder" style="background:<?= $avBg ?>;color:<?= $avColor ?>;"><?= escape($initials) ?></div>
                        <?php endif; ?>
                      </td>
                      <td class="fw-semibold"><?= $fullName ?></td>
                      <td class="text-secondary"><?= escape($u['email']) ?></td>
                      <td><span class="badge <?= $badgeCls ?> rounded-pill px-3 py-1"><?= $roleLabel ?></span></td>
                      <td class="text-secondary small"><?= $dateFmt ?></td>
                      <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="openEditUserModal(<?= (int)$u['id'] ?>)" title="Επεξεργασία"><i class="bi bi-pencil"></i></button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Είστε βέβαιοι ότι θέλετε να διαγράψετε τον χρήστη <?= escape(trim($u['first_name'] . ' ' . $u['last_name'])) ?>;');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Διαγραφή">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                    <?php endforeach; endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top">
                <small class="text-secondary">Σύνολο <strong><?= $totalUsers ?></strong> χρηστών</small>
              </div>
            </div>
            <!-- end table card -->

          </div>
        </div>
      </main>

      <?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

    </div>
    <!-- end app-wrapper -->

    <!-- ===== ADD / EDIT USER MODAL ===== -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="userModalLabel">Προσθήκη Χρήστη</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="userForm" method="POST" onsubmit="return validateUserForm()">
              <input type="hidden" id="userFormAction" name="action" value="add">
              <input type="hidden" id="userFormId" name="user_id" value="">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Όνομα <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="userFirstName" name="first_name" placeholder="π.χ. Ανδρέας" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Επώνυμο <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="userLastName" name="last_name" placeholder="π.χ. Γεωργίου" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="userEmail" name="email" placeholder="email@example.gr" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Τηλέφωνο</label>
                  <input type="tel" class="form-control" id="userPhone" name="phone" placeholder="+35799123456" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Ρόλος <span class="text-danger">*</span></label>
                  <select class="form-select" id="userRole" name="role" required>
                    <option value="">Επιλέξτε ρόλο...</option>
                    <option value="admin">Admin</option>
                    <option value="user">Χρήστης</option>
                  </select>
                </div>
                <!-- Πεδίο κωδικού για νέο χρήστη -->
                <div class="col-md-6" id="passwordField">
                  <label class="form-label fw-semibold">Κωδικός <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="userPassword" name="password" placeholder="Τουλάχιστον 8 χαρακτήρες" />
                </div>
                <div class="col-md-6" id="confirmPasswordField">
                  <label class="form-label fw-semibold">Επιβεβαίωση Κωδικού <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="userPasswordConfirm" placeholder="Επαναλάβετε τον κωδικό" />
                </div>
                <!-- Πεδία αλλαγής κωδικού για επεξεργασία (εμφανίζεται μόνο στο edit) -->
                <div class="col-12" id="changePasswordSection" style="display:none;">
                  <hr class="my-1">
                  <p class="text-muted small mb-2"><i class="bi bi-info-circle me-1"></i>Αφήστε κενό αν δεν θέλετε να αλλάξετε τον κωδικό.</p>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Νέος Κωδικός</label>
                      <input type="password" class="form-control" id="userNewPassword" name="new_password" placeholder="Τουλάχιστον 8 χαρακτήρες" />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Επιβεβαίωση Νέου Κωδικού</label>
                      <input type="password" class="form-control" id="userNewPasswordConfirm" placeholder="Επαναλάβετε τον κωδικό" />
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-modal-cancel" data-bs-dismiss="modal">Ακύρωση</button>
            <button type="submit" form="userForm" class="btn btn-primary" id="saveUserBtn">
              <i class="bi bi-check-lg me-1"></i>Αποθήκευση
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/js/adminlte.js" defer></script>
    <script src="../../assets/js/changes.js" defer></script>
    <script>
      var USERS_DATA = <?= json_encode(
        array_column(
          array_map(function($u) { unset($u['profilepic'], $u['profilepic_mime']); return $u; }, $users),
          null, 'id'
        ),
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
      ) ?>;

      var _userModal = null;

      document.addEventListener('DOMContentLoaded', function () {
        // Αρχικοποίηση Bootstrap modal instance
        var modalEl = document.getElementById('userModal');
        _userModal = new bootstrap.Modal(modalEl);

        // OverlayScrollbars
        var sw = document.querySelector('.sidebar-wrapper');
        if (sw && OverlayScrollbarsGlobal && OverlayScrollbarsGlobal.OverlayScrollbars) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sw, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
          });
        }

        // Live search
        document.getElementById('userSearch').addEventListener('input', function () {
          var q = this.value.toLowerCase();
          document.querySelectorAll('#usersTable tbody tr').forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
          });
        });

        // Role filter
        document.getElementById('roleFilter').addEventListener('change', function () {
          var val = this.value;
          document.querySelectorAll('#usersTable tbody tr').forEach(function (row) {
            row.style.display = (!val || row.dataset.role === val) ? '' : 'none';
          });
        });
      });

      function openAddUserModal() {
        document.getElementById('userModalLabel').textContent = 'Προσθήκη Χρήστη';
        document.getElementById('userForm').reset();
        document.getElementById('userFormAction').value = 'add';
        document.getElementById('userFormId').value = '';
        document.getElementById('passwordField').style.display = '';
        document.getElementById('confirmPasswordField').style.display = '';
        document.getElementById('changePasswordSection').style.display = 'none';
        document.getElementById('userPassword').setAttribute('required', 'required');
        if (_userModal) {
          _userModal.show();
        } else {
          bootstrap.Modal.getOrCreateInstance(document.getElementById('userModal')).show();
        }
      }

      function openEditUserModal(id) {
        var u = USERS_DATA[id];
        if (!u) {
          alert('Σφάλμα: Δεν βρέθηκαν στοιχεία χρήστη. Ανανεώστε τη σελίδα.');
          return;
        }
        document.getElementById('userModalLabel').textContent = 'Επεξεργασία Χρήστη';
        document.getElementById('userForm').reset();
        document.getElementById('userFormAction').value = 'edit';
        document.getElementById('userFormId').value = id;
        document.getElementById('userFirstName').value = u.first_name || '';
        document.getElementById('userLastName').value  = u.last_name  || '';
        document.getElementById('userEmail').value     = u.email      || '';
        document.getElementById('userPhone').value     = u.phone      || '';
        document.getElementById('userRole').value      = u.role       || 'user';
        // Κρύψε πεδία κωδικού για νέο χρήστη, δείξε την ενότητα αλλαγής κωδικού
        document.getElementById('passwordField').style.display = 'none';
        document.getElementById('confirmPasswordField').style.display = 'none';
        document.getElementById('changePasswordSection').style.display = '';
        document.getElementById('userPassword').removeAttribute('required');
        document.getElementById('userNewPassword').value = '';
        document.getElementById('userNewPasswordConfirm').value = '';
        if (_userModal) {
          _userModal.show();
        } else {
          bootstrap.Modal.getOrCreateInstance(document.getElementById('userModal')).show();
        }
      }

      function validateUserForm() {
        var action = document.getElementById('userFormAction').value;
        if (action === 'add') {
          var pass = document.getElementById('userPassword').value;
          var confirm = document.getElementById('userPasswordConfirm').value;
          if (pass.length < 8) {
            alert('Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.');
            return false;
          }
          if (pass !== confirm) {
            alert('Οι κωδικοί δεν ταιριάζουν.');
            return false;
          }
        } else if (action === 'edit') {
          var newPass = document.getElementById('userNewPassword').value;
          if (newPass !== '') {
            if (newPass.length < 8) {
              alert('Ο νέος κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.');
              return false;
            }
            var newConfirm = document.getElementById('userNewPasswordConfirm').value;
            if (newPass !== newConfirm) {
              alert('Οι νέοι κωδικοί δεν ταιριάζουν.');
              return false;
            }
          }
        }
        return true;
      }
    </script>
  </body>
</html>

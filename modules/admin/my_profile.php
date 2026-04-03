<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../database/db.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function respondJson(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function isValidStrongPassword(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password) === 1
        && preg_match('/[a-z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1
        && preg_match('/[!@#$%^&*()_+\-=]/', $password) === 1;
}

    function buildAvatarSrc(?string $binary, ?string $mimeType, int $userId = 0): string
    {
      if ($binary) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $safeMimeType = in_array((string)$mimeType, $allowedMimeTypes, true)
          ? (string)$mimeType
          : 'image/jpeg';

        return 'data:' . $safeMimeType . ';base64,' . base64_encode($binary);
      }

      if ($userId > 0) {
        $fileMatches = glob(__DIR__ . '/../../uploads/profile_pics/user_' . $userId . '.*');
        if (is_array($fileMatches) && $fileMatches !== []) {
          $filePath = $fileMatches[0];
          $fileVersion = (int)@filemtime($filePath) ?: time();
          return '../../uploads/profile_pics/' . rawurlencode(basename($filePath)) . '?v=' . $fileVersion;
        }
      }

      return '../../assets/images/avatar.png';
    }

function formatDateDisplay(?string $value): string
{
    if (!$value) {
        return '—';
    }

    try {
        return (new DateTimeImmutable($value))->format('d/m/Y');
    } catch (Throwable $e) {
        return '—';
    }
}

  function uploadErrorMessage(int $uploadError): string
  {
    return match ($uploadError) {
      UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Το αρχείο είναι πολύ μεγάλο και υπερβαίνει τα όρια μεταφόρτωσης του server ή της φόρμας.',
      UPLOAD_ERR_PARTIAL => 'Η μεταφόρτωση διακόπηκε πριν ολοκληρωθεί. Προσπαθήστε ξανά.',
      UPLOAD_ERR_NO_FILE => 'Δεν επιλέχθηκε αρχείο εικόνας.',
      UPLOAD_ERR_NO_TMP_DIR => 'Σφάλμα server: λείπει ο προσωρινός φάκελος μεταφόρτωσης.',
      UPLOAD_ERR_CANT_WRITE => 'Σφάλμα server: δεν ήταν δυνατή η εγγραφή του αρχείου στον δίσκο.',
      UPLOAD_ERR_EXTENSION => 'Η μεταφόρτωση μπλοκαρίστηκε από επέκταση του server.',
      default => 'Η μεταφόρτωση απέτυχε. Προσπαθήστε ξανά.',
    };
  }

$adminId = (int)($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($adminId <= 0) {
        respondJson(['success' => false, 'error' => 'Μη έγκυρη συνεδρία χρήστη.'], 401);
    }

    try {
        if ($action === 'update_profile') {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '') ?: null;
          $dobRaw = trim($_POST['dob'] ?? '');
          $dob = $dobRaw !== '' ? $dobRaw : null;

            if ($firstName === '' || $lastName === '' || $email === '') {
                respondJson(['success' => false, 'error' => 'Συμπληρώστε όλα τα υποχρεωτικά πεδία.'], 422);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                respondJson(['success' => false, 'error' => 'Το email δεν είναι έγκυρο.'], 422);
            }

          if ($dob !== null) {
            $parsedDob = DateTimeImmutable::createFromFormat('Y-m-d', $dob);
            $dobErrors = DateTimeImmutable::getLastErrors();
            $hasDobErrors = is_array($dobErrors)
              ? (($dobErrors['warning_count'] ?? 0) > 0 || ($dobErrors['error_count'] ?? 0) > 0)
              : false;

            if (!$parsedDob || $hasDobErrors || $parsedDob->format('Y-m-d') !== $dob) {
              respondJson(['success' => false, 'error' => 'Η ημερομηνία γέννησης δεν είναι έγκυρη.'], 422);
            }
          }

            $existingEmailStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
            $existingEmailStmt->execute([
                ':email' => $email,
                ':id' => $adminId,
            ]);

            if ($existingEmailStmt->fetch()) {
                respondJson(['success' => false, 'error' => 'Το email χρησιμοποιείται ήδη από άλλον χρήστη.'], 409);
            }

            $updateProfileStmt = $pdo->prepare(
                '
                UPDATE users
                SET first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                  dob = :dob,
                    updated_at = NOW()
                WHERE id = :id
                '
            );
            $updateProfileStmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':phone' => $phone,
                ':dob' => $dob,
                ':id' => $adminId,
            ]);

            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;

            respondJson([
                'success' => true,
                'message' => 'Τα στοιχεία του προφίλ αποθηκεύτηκαν επιτυχώς.',
                'profile' => [
                    'full_name' => trim($firstName . ' ' . $lastName),
                    'email' => $email,
                    'phone' => $phone ?? '',
                  'dob' => $dob ?? '',
                  'dob_display' => formatDateDisplay($dob),
                ],
            ]);
        }

        if ($action === 'change_password') {
            $currentPassword = (string)($_POST['current_password'] ?? '');
            $newPassword = (string)($_POST['new_password'] ?? '');
            $confirmPassword = (string)($_POST['confirm_password'] ?? '');

            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                respondJson(['success' => false, 'error' => 'Συμπληρώστε όλα τα πεδία κωδικού.'], 422);
            }

            if ($newPassword !== $confirmPassword) {
                respondJson(['success' => false, 'error' => 'Ο νέος κωδικός και η επιβεβαίωση δεν ταιριάζουν.'], 422);
            }

            if (!isValidStrongPassword($newPassword)) {
                respondJson(['success' => false, 'error' => 'Ο νέος κωδικός δεν καλύπτει όλες τις απαιτήσεις ασφαλείας.'], 422);
            }

            $passwordStmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
            $passwordStmt->execute([':id' => $adminId]);
            $passwordRow = $passwordStmt->fetch();

            if (!$passwordRow || !password_verify($currentPassword, (string)$passwordRow['password_hash'])) {
                respondJson(['success' => false, 'error' => 'Ο τρέχων κωδικός δεν είναι σωστός.'], 403);
            }

            if (password_verify($newPassword, (string)$passwordRow['password_hash'])) {
                respondJson(['success' => false, 'error' => 'Ο νέος κωδικός πρέπει να είναι διαφορετικός από τον τρέχοντα.'], 422);
            }

            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $updatePasswordStmt = $pdo->prepare(
                '
                UPDATE users
                SET password_hash = :password_hash,
                    updated_at = NOW()
                WHERE id = :id
                '
            );
            $updatePasswordStmt->execute([
                ':password_hash' => $newHash,
                ':id' => $adminId,
            ]);

            respondJson([
                'success' => true,
                'message' => 'Ο κωδικός πρόσβασης άλλαξε επιτυχώς.',
            ]);
        }

          if ($action === 'update_avatar') {
            if (!isset($_FILES['avatar']) || !is_array($_FILES['avatar'])) {
              respondJson(['success' => false, 'error' => 'Δεν επιλέχθηκε αρχείο εικόνας.'], 422);
            }

            $avatarFile = $_FILES['avatar'];
            $uploadError = (int)($avatarFile['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($uploadError !== UPLOAD_ERR_OK) {
              respondJson(['success' => false, 'error' => uploadErrorMessage($uploadError)], 422);
            }

            $tmpPath = (string)($avatarFile['tmp_name'] ?? '');
            if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
              respondJson(['success' => false, 'error' => 'Μη έγκυρο αρχείο μεταφόρτωσης.'], 422);
            }

            $maxBytes = 8 * 1024 * 1024;
            $fileSize = (int)($avatarFile['size'] ?? 0);
            if ($fileSize <= 0 || $fileSize > $maxBytes) {
              respondJson(['success' => false, 'error' => 'Η εικόνα πρέπει να είναι έως 8MB.'], 422);
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMimeType = $finfo ? finfo_file($finfo, $tmpPath) : false;
            if ($finfo) {
              finfo_close($finfo);
            }

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array((string)$detectedMimeType, $allowedMimeTypes, true)) {
              respondJson(['success' => false, 'error' => 'Επιτρέπονται μόνο εικόνες JPG, PNG, GIF ή WEBP.'], 422);
            }

            $extByMime = [
              'image/jpeg' => 'jpg',
              'image/png' => 'png',
              'image/gif' => 'gif',
              'image/webp' => 'webp',
            ];
            $extension = $extByMime[(string)$detectedMimeType] ?? 'jpg';

            $avatarDir = __DIR__ . '/../../uploads/profile_pics';
            if (!is_dir($avatarDir) && !mkdir($avatarDir, 0775, true) && !is_dir($avatarDir)) {
              respondJson(['success' => false, 'error' => 'Αδυναμία δημιουργίας φακέλου αποθήκευσης εικόνας.'], 500);
            }

            $existingFiles = glob($avatarDir . '/user_' . $adminId . '.*');
            if (is_array($existingFiles)) {
              foreach ($existingFiles as $existingFile) {
                @unlink($existingFile);
              }
            }

            $targetPath = $avatarDir . '/user_' . $adminId . '.' . $extension;
            $savedToFile = move_uploaded_file($tmpPath, $targetPath);
            if (!$savedToFile) {
              $savedToFile = @copy($tmpPath, $targetPath);
            }

            $binarySourcePath = $savedToFile ? $targetPath : $tmpPath;
            $binaryData = file_get_contents($binarySourcePath);
            if ($binaryData === false || $binaryData === '') {
              respondJson(['success' => false, 'error' => 'Δεν ήταν δυνατή η ανάγνωση της εικόνας.'], 500);
            }

            $updateAvatarStmt = $pdo->prepare(
              '
              UPDATE users
              SET profilepic = :profilepic,
                profilepic_mime = :profilepic_mime,
                updated_at = NOW()
              WHERE id = :id
              '
            );
            $updateAvatarStmt->bindValue(':profilepic', $binaryData, PDO::PARAM_LOB);
            $updateAvatarStmt->bindValue(':profilepic_mime', (string)$detectedMimeType, PDO::PARAM_STR);
            $updateAvatarStmt->bindValue(':id', $adminId, PDO::PARAM_INT);
            $updateAvatarStmt->execute();

            respondJson([
              'success' => true,
              'message' => 'Η φωτογραφία προφίλ ενημερώθηκε επιτυχώς.',
              'avatar_src' => buildAvatarSrc($binaryData, (string)$detectedMimeType, $adminId),
            ]);
          }

        respondJson(['success' => false, 'error' => 'Μη υποστηριζόμενη ενέργεια.'], 400);
    } catch (Throwable $e) {
        respondJson(['success' => false, 'error' => 'Παρουσιάστηκε σφάλμα κατά την αποθήκευση.'], 500);
    }
}

$adminStmt = $pdo->prepare(
    '
  SELECT id, first_name, last_name, email, phone, dob, role, created_at, profilepic, profilepic_mime
    FROM users
    WHERE id = :id
    LIMIT 1
    '
);
$adminStmt->execute([':id' => $adminId]);
$adminUser = $adminStmt->fetch() ?: [];

if ($adminUser === []) {
    $_SESSION['auth_error'] = 'Ο λογαριασμός διαχειριστή δεν βρέθηκε.';
    header('Location: ../../logout.php');
    exit;
}

$statsRow = $pdo->query(
    "
    SELECT
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM candidate_applications WHERE status <> 'draft') AS total_applications,
        (SELECT COUNT(*) FROM departments) AS total_departments
    "
)->fetch() ?: [];

$adminFullName = trim((string)$adminUser['first_name'] . ' ' . (string)$adminUser['last_name']);
$adminEmail = (string)($adminUser['email'] ?? '');
$adminPhone = (string)($adminUser['phone'] ?? '');
$adminDob = (string)($adminUser['dob'] ?? '');
$adminDobDisplay = formatDateDisplay($adminDob !== '' ? $adminDob : null);
$adminAvatarSrc = buildAvatarSrc(
  isset($adminUser['profilepic']) ? (string)$adminUser['profilepic'] : null,
  isset($adminUser['profilepic_mime']) ? (string)$adminUser['profilepic_mime'] : null,
  $adminId
);
$memberSince = formatDateDisplay((string)($adminUser['created_at'] ?? ''));
$totalUsers = (int)($statsRow['total_users'] ?? 0);
$totalApplications = (int)($statsRow['total_applications'] ?? 0);
$totalDepartments = (int)($statsRow['total_departments'] ?? 0);
?>
<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin | My Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../assets/css/adminlte.css" />
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
              <a class="nav-link" href="#" onclick="toggleSidebar(event)"><i class="bi bi-list"></i></a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="index.php" class="nav-link"><i class="bi bi-house me-1"></i>Dashboard</a>
            </li>
            <li class="nav-item d-none d-md-block">
              <span class="nav-link text-secondary"><i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>My Profile</span>
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
                <img src="<?= h($adminAvatarSrc) ?>" class="user-image rounded-circle shadow" alt="<?= h($adminFullName) ?>" id="navAvatar" />
                <span class="d-none d-md-inline" id="navUserName"><?= h($adminFullName) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="<?= h($adminAvatarSrc) ?>" class="rounded-circle shadow" alt="<?= h($adminFullName) ?>" />
                  <p><?= h($adminFullName) ?><small>Διαχειριστής Συστήματος</small></p>
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
            <img src="../../assets/images/AdminLTELogo.png" alt="Logo" class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light">Admin Panel</span>
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
                <a href="manage_recruitment.php" class="nav-link">
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
              <li class="nav-item"><a href="my_profile.php" class="nav-link active"><i class="nav-icon bi bi-person-circle"></i><p>My Profile</p></a></li>
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
                    <i class="bi bi-person-fill"></i>
                  </span>
                  My Profile
                </h4>
              </div>
              <div class="col-auto">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                  <li class="breadcrumb-item active">My Profile</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <div class="alert alert-success alert-dismissible fade d-none mb-3" id="profileAlert" role="alert">
              <span id="profileAlertMessage"><i class="bi bi-check-circle me-2"></i>Οι αλλαγές αποθηκεύτηκαν επιτυχώς.</span>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <div class="row g-4">

              <div class="col-12 col-lg-4 col-xl-3">
                <div class="config-card bg-body shadow-sm text-center">
                  <div class="config-card-body py-4">
                    <div class="profile-avatar-upload mx-auto">
                      <img src="<?= h($adminAvatarSrc) ?>" alt="Avatar" id="profileAvatarImg" />
                      <label class="avatar-edit-btn" title="Αλλαγή φωτογραφίας">
                        <i class="bi bi-camera-fill"></i>
                        <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="d-none" id="avatarFileInput" onchange="handleAvatarChange(this)" />
                      </label>
                    </div>

                    <h5 class="fw-bold mb-0" id="profileDisplayName"><?= h($adminFullName) ?></h5>
                    <p class="text-secondary small mb-3">Διαχειριστής Συστήματος</p>

                    <span class="badge badge-role-admin rounded-pill px-3 py-2 mb-3">
                      <i class="bi bi-shield-fill me-1"></i>Admin
                    </span>

                    <hr />

                    <div class="row text-center g-0">
                      <div class="col-4 border-end">
                        <div class="fw-bold"><?= h((string)$totalUsers) ?></div>
                        <div class="text-secondary" style="font-size:.75rem;">Χρήστες</div>
                      </div>
                      <div class="col-4 border-end">
                        <div class="fw-bold"><?= h((string)$totalApplications) ?></div>
                        <div class="text-secondary" style="font-size:.75rem;">Αιτήσεις</div>
                      </div>
                      <div class="col-4">
                        <div class="fw-bold"><?= h((string)$totalDepartments) ?></div>
                        <div class="text-secondary" style="font-size:.75rem;">Τμήματα</div>
                      </div>
                    </div>

                    <hr />

                    <div class="text-start">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-envelope text-secondary" style="width:18px;"></i>
                        <span class="small" id="profileEmailDisplay"><?= h($adminEmail) ?></span>
                      </div>
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-telephone text-secondary" style="width:18px;"></i>
                        <span class="small" id="profilePhoneDisplay"><?= h($adminPhone !== '' ? $adminPhone : 'Δεν έχει οριστεί') ?></span>
                      </div>
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-calendar-event text-secondary" style="width:18px;"></i>
                        <span class="small" id="profileDobDisplay"><?= h($adminDobDisplay) ?></span>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-check text-secondary" style="width:18px;"></i>
                        <span class="small">Μέλος από <?= h($memberSince) ?></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-8 col-xl-9">

                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-person-lines-fill text-primary"></i>
                    Στοιχεία Λογαριασμού
                  </div>
                  <div class="config-card-body">
                    <form id="profileForm">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Όνομα <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="firstName" value="<?= h((string)$adminUser['first_name']) ?>" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Επώνυμο <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="lastName" value="<?= h((string)$adminUser['last_name']) ?>" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                          <input type="email" class="form-control" id="profileEmail" value="<?= h($adminEmail) ?>" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Τηλέφωνο</label>
                          <input type="tel" class="form-control" id="profilePhone" value="<?= h($adminPhone) ?>" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Ημερομηνία Γέννησης</label>
                          <input type="date" class="form-control" id="profileDob" value="<?= h($adminDob) ?>" max="<?= h(date('Y-m-d')) ?>" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Ρόλος</label>
                          <input type="text" class="form-control" value="Διαχειριστής Συστήματος" disabled />
                          <div class="form-text">Ο ρόλος δεν μπορεί να αλλαχθεί από εδώ.</div>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Γλώσσα Διεπαφής</label>
                          <select class="form-select">
                            <option selected>Ελληνικά</option>
                            <option>English</option>
                          </select>
                        </div>
                        <div class="col-12">
                          <button type="button" class="btn btn-primary" onclick="saveProfile()">
                            <i class="bi bi-floppy me-1"></i>Αποθήκευση Στοιχείων
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-lock-fill text-warning"></i>
                    Αλλαγή Κωδικού Πρόσβασης
                  </div>
                  <div class="config-card-body">
                    <form id="passwordForm" novalidate>
                      <div class="row g-3">
                        <div class="col-md-6 col-xl-4">
                          <label class="form-label fw-semibold">Τρέχων Κωδικός <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="currentPassword" placeholder="Τρέχων κωδικός" />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('currentPassword',this)">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                          <label class="form-label fw-semibold">Νέος Κωδικός <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" placeholder="Τουλάχιστον 8 χαρακτήρες" oninput="checkPwdStrength(this.value)" />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('newPassword',this)">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                          <div class="mt-1" id="pwdStrengthWrap" style="display:none;">
                            <div class="progress" style="height:4px;">
                              <div class="progress-bar" id="pwdStrengthBar" style="width:0%"></div>
                            </div>
                            <small id="pwdStrengthText" class="text-secondary"></small>
                          </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                          <label class="form-label fw-semibold">Επιβεβαίωση Νέου Κωδικού <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" placeholder="Επαναλάβετε νέο κωδικό" />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('confirmPassword',this)">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                        </div>

                        <div class="col-12">
                          <div class="border rounded p-3 bg-body-tertiary">
                            <p class="small fw-semibold mb-2 text-secondary">Απαιτήσεις κωδικού:</p>
                            <ul class="list-unstyled mb-0 small text-secondary" id="pwdReqs">
                              <li id="req-length"><i class="bi bi-circle me-2"></i>Τουλάχιστον 8 χαρακτήρες</li>
                              <li id="req-upper"><i class="bi bi-circle me-2"></i>Ένα κεφαλαίο γράμμα</li>
                              <li id="req-lower"><i class="bi bi-circle me-2"></i>Ένα πεζό γράμμα</li>
                              <li id="req-number"><i class="bi bi-circle me-2"></i>Έναν αριθμό</li>
                              <li id="req-special"><i class="bi bi-circle me-2"></i>Έναν ειδικό χαρακτήρα (!@#$%)</li>
                            </ul>
                          </div>
                        </div>

                        <div class="col-12">
                          <button type="button" class="btn btn-warning" onclick="changePassword()">
                            <i class="bi bi-lock me-1"></i>Αλλαγή Κωδικού
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-shield-check text-success"></i>
                    Ασφάλεια Λογαριασμού
                  </div>
                  <div class="config-card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                      <div>
                        <div class="fw-semibold">Έλεγχος Ταυτότητας Δύο Παραγόντων (2FA)</div>
                        <small class="text-secondary">Προσθέστε ένα επιπλέον επίπεδο ασφάλειας στον λογαριασμό σας.</small>
                      </div>
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="twoFactor" style="width:2.5em;height:1.4em;" />
                        <label class="form-check-label ms-1 fw-semibold" for="twoFactor">Ανενεργό</label>
                      </div>
                    </div>
                    <hr />
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                      <div>
                        <div class="fw-semibold">Ειδοποιήσεις Σύνδεσης</div>
                        <small class="text-secondary">Λαμβάνετε email κάθε φορά που γίνεται σύνδεση στον λογαριασμό σας.</small>
                      </div>
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="loginNotif" checked style="width:2.5em;height:1.4em;" />
                        <label class="form-check-label ms-1 fw-semibold" for="loginNotif">Ενεργό</label>
                      </div>
                    </div>
                    <hr />
                    <div class="fw-semibold mb-2">Πρόσφατη Δραστηριότητα</div>
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item px-0 py-2">
                        <div class="d-flex align-items-center gap-3">
                          <div class="stat-icon-wrap" style="background:#dcfce7;color:#15803d;width:36px;height:36px;border-radius:10px;font-size:1rem;">
                            <i class="bi bi-box-arrow-in-right"></i>
                          </div>
                          <div>
                            <div class="small fw-semibold">Σύνδεση επιτυχής</div>
                            <div class="text-secondary" style="font-size:.78rem;">27/02/2026 09:14 · Chrome · Windows 11</div>
                          </div>
                          <span class="badge bg-success ms-auto">Επιτυχία</span>
                        </div>
                      </li>
                      <li class="list-group-item px-0 py-2">
                        <div class="d-flex align-items-center gap-3">
                          <div class="stat-icon-wrap" style="background:#dcfce7;color:#15803d;width:36px;height:36px;border-radius:10px;font-size:1rem;">
                            <i class="bi bi-box-arrow-in-right"></i>
                          </div>
                          <div>
                            <div class="small fw-semibold">Σύνδεση επιτυχής</div>
                            <div class="text-secondary" style="font-size:.78rem;">26/02/2026 14:32 · Chrome · Windows 11</div>
                          </div>
                          <span class="badge bg-success ms-auto">Επιτυχία</span>
                        </div>
                      </li>
                      <li class="list-group-item px-0 py-2">
                        <div class="d-flex align-items-center gap-3">
                          <div class="stat-icon-wrap" style="background:#fee2e2;color:#b91c1c;width:36px;height:36px;border-radius:10px;font-size:1rem;">
                            <i class="bi bi-x-circle"></i>
                          </div>
                          <div>
                            <div class="small fw-semibold">Αποτυχημένη σύνδεση</div>
                            <div class="text-secondary" style="font-size:.78rem;">25/02/2026 11:05 · Firefox · Unknown</div>
                          </div>
                          <span class="badge bg-danger ms-auto">Αποτυχία</span>
                        </div>
                      </li>
                    </ul>
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
    <script src="../../assets/js/changes.js" defer></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const sw = document.querySelector('.sidebar-wrapper');
        if (sw && OverlayScrollbarsGlobal?.OverlayScrollbars) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sw, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
          });
        }

        document.getElementById('twoFactor').addEventListener('change', function () {
          this.nextElementSibling.textContent = this.checked ? 'Ενεργό' : 'Ανενεργό';
        });
      });

      function updateAvatarElements(src) {
        document.getElementById('profileAvatarImg').src = src;
        document.getElementById('navAvatar').src = src;
      }

      function handleAvatarChange(input) {
        if (!input.files || !input.files[0]) {
          return;
        }

        var file = input.files[0];
        var allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (allowed.indexOf(file.type) === -1) {
          showAlert('Επιτρέπονται μόνο εικόνες JPG, PNG, GIF ή WEBP.', 'danger');
          input.value = '';
          return;
        }

        if (file.size > (8 * 1024 * 1024)) {
          showAlert('Η εικόνα πρέπει να είναι έως 8MB.', 'danger');
          input.value = '';
          return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
          updateAvatarElements(e.target.result);
        };
        reader.readAsDataURL(file);

        uploadAvatar(file, input);
      }

      async function uploadAvatar(file, inputElement) {
        var formData = new FormData();
        formData.append('action', 'update_avatar');
        formData.append('avatar', file);

        try {
          var response = await fetch('my_profile.php', {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
          });

          var result = await response.json();

          if (!response.ok || !result.success) {
            showAlert(result.error || 'Αποτυχία ενημέρωσης φωτογραφίας.', 'danger');
            inputElement.value = '';
            return;
          }

          if (result.avatar_src) {
            updateAvatarElements(result.avatar_src);
          }

          showAlert(result.message || 'Η φωτογραφία προφίλ ενημερώθηκε επιτυχώς.', 'success');
          inputElement.value = '';
        } catch (error) {
          showAlert('Παρουσιάστηκε σφάλμα κατά την ενημέρωση φωτογραφίας.', 'danger');
          inputElement.value = '';
        }
      }

      async function saveProfile() {
        var firstName = document.getElementById('firstName').value.trim();
        var lastName  = document.getElementById('lastName').value.trim();
        var email     = document.getElementById('profileEmail').value.trim();
        var phone     = document.getElementById('profilePhone').value.trim();
        var dob       = document.getElementById('profileDob').value.trim();

        if (!firstName || !lastName || !email) {
          showAlert('Παρακαλώ συμπληρώστε όλα τα υποχρεωτικά πεδία.', 'danger');
          return;
        }

        var payload = new URLSearchParams();
        payload.append('action', 'update_profile');
        payload.append('first_name', firstName);
        payload.append('last_name', lastName);
        payload.append('email', email);
        payload.append('phone', phone);
        payload.append('dob', dob);

        try {
          var response = await fetch('my_profile.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload.toString()
          });

          var result = await response.json();

          if (!response.ok || !result.success) {
            showAlert(result.error || 'Αποτυχία αποθήκευσης στοιχείων.', 'danger');
            return;
          }

          document.getElementById('profileDisplayName').textContent = result.profile.full_name;
          document.getElementById('navUserName').textContent = result.profile.full_name;
          document.getElementById('profileEmailDisplay').textContent = result.profile.email;
          document.getElementById('profilePhoneDisplay').textContent = result.profile.phone || 'Δεν έχει οριστεί';
          document.getElementById('profileDobDisplay').textContent = result.profile.dob_display || '—';
          showAlert(result.message || 'Οι αλλαγές αποθηκεύτηκαν επιτυχώς.', 'success');
        } catch (error) {
          showAlert('Παρουσιάστηκε σφάλμα κατά την αποθήκευση.', 'danger');
        }
      }

      async function changePassword() {
        var curr = document.getElementById('currentPassword').value;
        var nw   = document.getElementById('newPassword').value;
        var conf = document.getElementById('confirmPassword').value;

        if (!curr || !nw || !conf) {
          showAlert('Συμπληρώστε όλα τα πεδία κωδικού.', 'danger');
          return;
        }

        if (nw !== conf) {
          showAlert('Ο νέος κωδικός και η επιβεβαίωση δεν ταιριάζουν.', 'danger');
          return;
        }

        if (nw.length < 8) {
          showAlert('Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.', 'danger');
          return;
        }

        var payload = new URLSearchParams();
        payload.append('action', 'change_password');
        payload.append('current_password', curr);
        payload.append('new_password', nw);
        payload.append('confirm_password', conf);

        try {
          var response = await fetch('my_profile.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload.toString()
          });

          var result = await response.json();

          if (!response.ok || !result.success) {
            showAlert(result.error || 'Αποτυχία αλλαγής κωδικού.', 'danger');
            return;
          }

          document.getElementById('passwordForm').reset();
          document.getElementById('pwdStrengthWrap').style.display = 'none';
          resetPwdRequirements();
          showAlert(result.message || 'Ο κωδικός άλλαξε επιτυχώς.', 'success');
        } catch (error) {
          showAlert('Παρουσιάστηκε σφάλμα κατά την αλλαγή κωδικού.', 'danger');
        }
      }

      function togglePwd(fieldId, btn) {
        var inp = document.getElementById(fieldId);
        var icon = btn.querySelector('i');
        if (inp.type === 'password') {
          inp.type = 'text';
          icon.className = 'bi bi-eye-slash';
        } else {
          inp.type = 'password';
          icon.className = 'bi bi-eye';
        }
      }

      function checkPwdStrength(val) {
        var wrap = document.getElementById('pwdStrengthWrap');
        var bar  = document.getElementById('pwdStrengthBar');
        var txt  = document.getElementById('pwdStrengthText');
        if (!val) {
          wrap.style.display = 'none';
          resetPwdRequirements();
          return;
        }
        wrap.style.display = 'block';

        var score = 0;
        var setReq = function (id, ok) {
          var el = document.getElementById(id);
          el.querySelector('i').className = ok ? 'bi bi-check-circle-fill me-2 text-success' : 'bi bi-circle me-2';
          el.className = ok ? 'text-success' : '';
          if (ok) score++;
        };

        setReq('req-length',  val.length >= 8);
        setReq('req-upper',   /[A-Z]/.test(val));
        setReq('req-lower',   /[a-z]/.test(val));
        setReq('req-number',  /[0-9]/.test(val));
        setReq('req-special', /[!@#$%^&*()_+\-=]/.test(val));

        var widths  = ['20%', '40%', '60%', '80%', '100%'];
        var colors  = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'];
        var labels  = ['Πολύ αδύναμος', 'Αδύναμος', 'Μέτριος', 'Ισχυρός', 'Πολύ ισχυρός'];

        bar.style.width = widths[score - 1] || '0%';
        bar.style.backgroundColor = colors[score - 1] || '#ef4444';
        txt.textContent = labels[score - 1] || '';
        txt.style.color = colors[score - 1] || '';
      }

      function resetPwdRequirements() {
        ['req-length', 'req-upper', 'req-lower', 'req-number', 'req-special'].forEach(function (id) {
          var el = document.getElementById(id);
          el.querySelector('i').className = 'bi bi-circle me-2';
          el.className = '';
        });
      }

      function showAlert(message, type) {
        var el = document.getElementById('profileAlert');
        var msg = document.getElementById('profileAlertMessage');
        var icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';

        el.classList.remove('d-none', 'alert-success', 'alert-danger');
        el.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
        msg.innerHTML = '<i class="bi bi-' + icon + ' me-2"></i>' + message;
        el.classList.add('show');
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        setTimeout(function () {
          el.classList.remove('show');
          setTimeout(function () { el.classList.add('d-none'); }, 200);
        }, 3500);
      }
    </script>
  </body>
</html>

<?php
require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../database/db.php';

/* ── POST handlers ──────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_announcement') {
        $id          = (int)($_POST['announcement_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $school_id   = (int)($_POST['school_id'] ?? 0);
        $dept_id     = (int)($_POST['department_id'] ?? 0);
        $course_id   = (int)($_POST['course_id'] ?? 0);
        $period_id   = (int)($_POST['period_id'] ?? 0);
        $status      = $_POST['status'] ?? 'draft';
        $description = trim($_POST['description'] ?? '');
        $num_pos     = max(1, (int)($_POST['number_of_positions'] ?? 1));

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE job_announcements SET title=?, school_id=?, department_id=?, course_id=?, status=?, description=?, number_of_positions=? WHERE id=?");
            $stmt->execute([$title, $school_id, $dept_id, $course_id, $status, $description, $num_pos, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO job_announcements (period_id, school_id, department_id, course_id, title, description, number_of_positions, status) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$period_id, $school_id, $dept_id, $course_id, $title, $description, $num_pos, $status]);
        }
        header('Location: manage_recruitment.php#applications');
        exit;
    }

    if ($action === 'delete_announcement') {
        $id = (int)($_POST['announcement_id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM job_announcements WHERE id=?")->execute([$id]);
        }
        header('Location: manage_recruitment.php#applications');
        exit;
    }

    if ($action === 'assign_evaluator') {
        $ann_id  = (int)($_POST['announcement_id'] ?? 0);
        $eval_id = (int)($_POST['evaluator_id'] ?? 0);
        if ($ann_id > 0 && $eval_id > 0) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO application_evaluators (announcement_id, evaluator_id) VALUES (?,?)");
            $stmt->execute([$ann_id, $eval_id]);
        }
        header('Location: manage_recruitment.php#applications');
        exit;
    }

    if ($action === 'save_school') {
        $id   = (int)($_POST['school_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            if ($id > 0) {
                $pdo->prepare("UPDATE schools SET name=? WHERE id=?")->execute([$name, $id]);
            } else {
                $pdo->prepare("INSERT INTO schools (name) VALUES (?)")->execute([$name]);
            }
        }
        header('Location: manage_recruitment.php#schools'); exit;
    }

    if ($action === 'delete_school') {
        $id = (int)($_POST['school_id'] ?? 0);
        if ($id > 0) $pdo->prepare("DELETE FROM schools WHERE id=?")->execute([$id]);
        header('Location: manage_recruitment.php#schools'); exit;
    }

    if ($action === 'save_department') {
        $id        = (int)($_POST['dept_id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $school_id = (int)($_POST['school_id'] ?? 0);
        if ($name !== '' && $school_id > 0) {
            if ($id > 0) {
                $pdo->prepare("UPDATE departments SET name=?, school_id=? WHERE id=?")->execute([$name, $school_id, $id]);
            } else {
                $pdo->prepare("INSERT INTO departments (name, school_id) VALUES (?,?)")->execute([$name, $school_id]);
            }
        }
        header('Location: manage_recruitment.php#departments'); exit;
    }

    if ($action === 'delete_department') {
        $id = (int)($_POST['dept_id'] ?? 0);
        if ($id > 0) $pdo->prepare("DELETE FROM departments WHERE id=?")->execute([$id]);
        header('Location: manage_recruitment.php#departments'); exit;
    }

    if ($action === 'save_course') {
        $id      = (int)($_POST['course_id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');
        $dept_id = (int)($_POST['department_id'] ?? 0);
        if ($name !== '' && $dept_id > 0) {
            if ($id > 0) {
                $pdo->prepare("UPDATE courses SET name=?, department_id=? WHERE id=?")->execute([$name, $dept_id, $id]);
            } else {
                $pdo->prepare("INSERT INTO courses (name, department_id) VALUES (?,?)")->execute([$name, $dept_id]);
            }
        }
        header('Location: manage_recruitment.php#courses'); exit;
    }

    if ($action === 'delete_course') {
        $id = (int)($_POST['course_id'] ?? 0);
        if ($id > 0) $pdo->prepare("DELETE FROM courses WHERE id=?")->execute([$id]);
        header('Location: manage_recruitment.php#courses'); exit;
    }

    if ($action === 'save_period') {
        $id          = (int)($_POST['period_id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $start_date  = $_POST['start_date'] ?? '';
        $end_date    = $_POST['end_date'] ?? '';
        $status      = $_POST['status'] ?? 'planning';
        $description = trim($_POST['description'] ?? '');
        if ($name !== '' && $start_date !== '' && $end_date !== '') {
            if ($id > 0) {
                $pdo->prepare("UPDATE recruitment_periods SET name=?, start_date=?, end_date=?, status=?, description=? WHERE id=?")
                    ->execute([$name, $start_date, $end_date, $status, $description, $id]);
            } else {
                $pdo->prepare("INSERT INTO recruitment_periods (name, start_date, end_date, status, description) VALUES (?,?,?,?,?)")
                    ->execute([$name, $start_date, $end_date, $status, $description]);
            }
        }
        header('Location: manage_recruitment.php#period'); exit;
    }

    if ($action === 'delete_period') {
        $id = (int)($_POST['period_id'] ?? 0);
        if ($id > 0) $pdo->prepare("DELETE FROM recruitment_periods WHERE id=?")->execute([$id]);
        header('Location: manage_recruitment.php#period'); exit;
    }

    if ($action === 'delete_evaluator') {
        $ae_id = (int)($_POST['ae_id'] ?? 0);
        if ($ae_id > 0) $pdo->prepare("DELETE FROM application_evaluators WHERE id=?")->execute([$ae_id]);
        header('Location: manage_recruitment.php#evaluators'); exit;
    }
}

/* ── Fetch data for tables & modals ─────────────────────────── */
$announcements = $pdo->query("
    SELECT ja.*, s.name AS school_name, d.name AS dept_name, c.name AS course_name,
           GROUP_CONCAT(DISTINCT CONCAT(u.first_name,' ',u.last_name) SEPARATOR ', ') AS evaluators
    FROM job_announcements ja
    LEFT JOIN schools s ON s.id = ja.school_id
    LEFT JOIN departments d ON d.id = ja.department_id
    LEFT JOIN courses c ON c.id = ja.course_id
    LEFT JOIN application_evaluators ae ON ae.announcement_id = ja.id
    LEFT JOIN users u ON u.id = ae.evaluator_id
    GROUP BY ja.id
    ORDER BY ja.created_at DESC
")->fetchAll();

$schools    = $pdo->query("SELECT id, name FROM schools ORDER BY name")->fetchAll();
$departments= $pdo->query("SELECT id, name, school_id FROM departments ORDER BY name")->fetchAll();
$courses    = $pdo->query("SELECT id, name, department_id FROM courses ORDER BY name")->fetchAll();
$periods    = $pdo->query("SELECT id, name, start_date, end_date, status, description FROM recruitment_periods ORDER BY start_date DESC")->fetchAll();
$evalUsers  = $pdo->query("SELECT id, first_name, last_name FROM users ORDER BY last_name, first_name")->fetchAll();

$evalAssignments = $pdo->query("
    SELECT ae.id AS ae_id, ja.title AS ann_title, u.first_name, u.last_name, ae.created_at
    FROM application_evaluators ae
    JOIN job_announcements ja ON ja.id = ae.announcement_id
    JOIN users u ON u.id = ae.evaluator_id
    ORDER BY ja.title, u.last_name
")->fetchAll();

$activePeriod = $pdo->query("SELECT * FROM recruitment_periods WHERE status='active' ORDER BY start_date DESC LIMIT 1")->fetch();
if (!$activePeriod) {
    $activePeriod = $pdo->query("SELECT * FROM recruitment_periods ORDER BY start_date DESC LIMIT 1")->fetch();
}

$periodStatusMap = [
    'planning' => ['label' => 'Προγραμματισμός', 'class' => 'period-status-closed'],
    'active'   => ['label' => 'Ανοιχτή',         'class' => 'period-status-open'],
    'closed'   => ['label' => 'Κλειστή',          'class' => 'period-status-closed'],
    'archived' => ['label' => 'Αρχειοθετήθηκε',  'class' => 'period-status-closed'],
];

$statusMap = [
    'published' => ['label' => 'Ανοιχτή',     'class' => 'bg-success'],
    'closed'    => ['label' => 'Κλειστή',      'class' => 'bg-secondary'],
    'draft'     => ['label' => 'Πρόχειρο',     'class' => 'bg-warning text-dark'],
    'cancelled' => ['label' => 'Ακυρωμένη',   'class' => 'bg-danger'],
];

function resolveAdminAvatarSrc(PDO $pdo, int $userId): string
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
    <title>Admin | Manage Recruitment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
              <span class="nav-link text-secondary"><i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>Manage Recruitment</span>
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
              <li class="nav-item menu-open">
                <a href="manage_recruitment.php" class="nav-link active">
                  <i class="nav-icon bi bi-clipboard-check"></i>
                  <p>Manage Recruitment<i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item"><a href="#applications" class="nav-link" onclick="switchTab('applications')"><i class="nav-icon bi bi-circle"></i><p>Αιτήσεις</p></a></li>
                  <li class="nav-item"><a href="#schools" class="nav-link" onclick="switchTab('schools')"><i class="nav-icon bi bi-circle"></i><p>Σχολές</p></a></li>
                  <li class="nav-item"><a href="#departments" class="nav-link" onclick="switchTab('departments')"><i class="nav-icon bi bi-circle"></i><p>Τμήματα</p></a></li>
                  <li class="nav-item"><a href="#courses" class="nav-link" onclick="switchTab('courses')"><i class="nav-icon bi bi-circle"></i><p>Μαθήματα</p></a></li>
                  <li class="nav-item"><a href="#period" class="nav-link" onclick="switchTab('period')"><i class="nav-icon bi bi-circle"></i><p>Περίοδος Αιτήσεων</p></a></li>
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
                  <span class="admin-page-title-icon" style="background:#dcfce7;color:#15803d;">
                    <i class="bi bi-clipboard-check-fill"></i>
                  </span>
                  Manage Recruitment
                </h4>
              </div>
              <div class="col-auto">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                  <li class="breadcrumb-item active">Manage Recruitment</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <!-- Tabs Navigation -->
            <ul class="nav admin-tabs" id="recruitTabs" role="tablist">
              <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#applications" id="tab-applications">
                  <i class="bi bi-file-earmark-text"></i>Αιτήσεις
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#schools" id="tab-schools">
                  <i class="bi bi-building"></i>Σχολές
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#departments" id="tab-departments">
                  <i class="bi bi-diagram-3"></i>Τμήματα
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#courses" id="tab-courses">
                  <i class="bi bi-book"></i>Μαθήματα
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#period" id="tab-period">
                  <i class="bi bi-calendar-range"></i>Περίοδος Αιτήσεων
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#evaluators" id="tab-evaluators">
                  <i class="bi bi-person-check"></i>Αξιολογητές
                </button>
              </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">

              <!-- ===== TAB: APPLICATIONS ===== -->
              <div class="tab-pane fade show active" id="applications" role="tabpanel">
                <div class="admin-table-card bg-body shadow-sm">
                  <div class="admin-table-toolbar">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <div class="admin-table-search">
                        <i class="bi bi-search"></i>
                        <input type="text" id="appSearch" class="form-control form-control-sm" placeholder="Αναζήτηση αίτησης..." />
                      </div>
                      <select id="appStatusFilter" class="form-select form-select-sm" style="width:auto;">
                        <option value="">Όλες οι καταστάσεις</option>
                        <option value="published">Ανοιχτή</option>
                        <option value="draft">Πρόχειρο</option>
                        <option value="closed">Κλειστή</option>
                        <option value="cancelled">Ακυρωμένη</option>
                      </select>
                    </div>
                    <button class="btn btn-success btn-sm" onclick="openNewModal()">
                      <i class="bi bi-plus-lg me-1"></i>Νέα Αίτηση
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0" id="appTable">
                      <thead class="table-light">
                        <tr>
                          <th>#</th>
                          <th>Τίτλος Αίτησης</th>
                          <th>Σχολή</th>
                          <th>Τμήμα</th>
                          <th>Αξιολογητής</th>
                          <th>Κατάσταση</th>
                          <th class="text-end">Ενέργειες</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($announcements)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-inbox fs-4 d-block mb-1"></i>Δεν υπάρχουν αιτήσεις ακόμα.</td></tr>
                        <?php else: ?>
                        <?php foreach ($announcements as $i => $ann):
                            $s = $statusMap[$ann['status']] ?? ['label' => $ann['status'], 'class' => 'bg-secondary'];
                        ?>
                        <tr data-status="<?= htmlspecialchars($ann['status']) ?>">
                          <td class="text-secondary small"><?= str_pad($i + 1, 3, '0', STR_PAD_LEFT) ?></td>
                          <td class="fw-semibold"><?= htmlspecialchars($ann['title']) ?></td>
                          <td><?= htmlspecialchars($ann['school_name'] ?? '—') ?></td>
                          <td><?= htmlspecialchars($ann['dept_name'] ?? '—') ?></td>
                          <td><?= htmlspecialchars($ann['evaluators'] ?? '—') ?></td>
                          <td><span class="badge <?= $s['class'] ?> rounded-pill px-3"><?= $s['label'] ?></span></td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" title="Επεξεργασία"
                              onclick="openEditModal(<?= htmlspecialchars(json_encode([
                                'id'            => $ann['id'],
                                'title'         => $ann['title'],
                                'school_id'     => $ann['school_id'],
                                'department_id' => $ann['department_id'],
                                'course_id'     => $ann['course_id'],
                                'period_id'     => $ann['period_id'],
                                'status'        => $ann['status'],
                                'description'   => $ann['description'] ?? '',
                                'number_of_positions' => $ann['number_of_positions'],
                              ]), ENT_QUOTES) ?>)">
                              <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary me-1" title="Ανάθεση Αξιολογητή"
                              onclick="openAssignModal(<?= $ann['id'] ?>, <?= htmlspecialchars(json_encode($ann['title']), ENT_QUOTES) ?>)">
                              <i class="bi bi-person-plus"></i>
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Να διαγραφεί η αγγελία «<?= htmlspecialchars($ann['title'], ENT_QUOTES) ?>»;');">
                              <input type="hidden" name="action" value="delete_announcement">
                              <input type="hidden" name="announcement_id" value="<?= $ann['id'] ?>">
                              <button type="submit" class="btn btn-sm btn-outline-danger" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: SCHOOLS ===== -->
              <div class="tab-pane fade" id="schools" role="tabpanel">
                <div class="admin-table-card bg-body shadow-sm">
                  <div class="admin-table-toolbar">
                    <div class="admin-table-search">
                      <i class="bi bi-search"></i>
                      <input type="text" class="form-control form-control-sm" placeholder="Αναζήτηση σχολής..." />
                    </div>
                    <button class="btn btn-success btn-sm" onclick="openSchoolModal(0,'')">
                      <i class="bi bi-plus-lg me-1"></i>Νέα Σχολή
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr><th>#</th><th>Όνομα Σχολής</th><th class="text-end">Ενέργειες</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($schools as $i => $school): ?>
                        <tr>
                          <td><?= $i + 1 ?></td>
                          <td class="fw-semibold"><?= htmlspecialchars($school['name']) ?></td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="openSchoolModal(<?= $school['id'] ?>, <?= htmlspecialchars(json_encode($school['name']), ENT_QUOTES) ?>)"><i class="bi bi-pencil"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Διαγραφή σχολής;');">
                              <input type="hidden" name="action" value="delete_school">
                              <input type="hidden" name="school_id" value="<?= $school['id'] ?>">
                              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($schools)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">Δεν υπάρχουν σχολές.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: DEPARTMENTS ===== -->
              <div class="tab-pane fade" id="departments" role="tabpanel">
                <div class="admin-table-card bg-body shadow-sm">
                  <div class="admin-table-toolbar">
                    <div class="admin-table-search">
                      <i class="bi bi-search"></i>
                      <input type="text" class="form-control form-control-sm" placeholder="Αναζήτηση τμήματος..." />
                    </div>
                    <button class="btn btn-success btn-sm" onclick="openDeptModal(0,'',0)">
                      <i class="bi bi-plus-lg me-1"></i>Νέο Τμήμα
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr><th>#</th><th>Τμήμα</th><th>Σχολή</th><th class="text-end">Ενέργειες</th></tr>
                      </thead>
                      <tbody>
                        <?php
                        $schoolById = array_column($schools, 'name', 'id');
                        foreach ($departments as $i => $dept): ?>
                        <tr>
                          <td><?= $i + 1 ?></td>
                          <td class="fw-semibold"><?= htmlspecialchars($dept['name']) ?></td>
                          <td><?= htmlspecialchars($schoolById[$dept['school_id']] ?? '—') ?></td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="openDeptModal(<?= $dept['id'] ?>, <?= htmlspecialchars(json_encode($dept['name']), ENT_QUOTES) ?>, <?= $dept['school_id'] ?>)"><i class="bi bi-pencil"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Διαγραφή τμήματος;');">
                              <input type="hidden" name="action" value="delete_department">
                              <input type="hidden" name="dept_id" value="<?= $dept['id'] ?>">
                              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($departments)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Δεν υπάρχουν τμήματα.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: COURSES ===== -->
              <div class="tab-pane fade" id="courses" role="tabpanel">
                <div class="admin-table-card bg-body shadow-sm">
                  <div class="admin-table-toolbar">
                    <div class="admin-table-search">
                      <i class="bi bi-search"></i>
                      <input type="text" class="form-control form-control-sm" placeholder="Αναζήτηση μαθήματος..." />
                    </div>
                    <button class="btn btn-success btn-sm" onclick="openCourseModal(0,'',0)">
                      <i class="bi bi-plus-lg me-1"></i>Νέο Μάθημα
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr><th>#</th><th>Μάθημα</th><th>Τμήμα</th><th class="text-end">Ενέργειες</th></tr>
                      </thead>
                      <tbody>
                        <?php
                        $deptById = array_column($departments, 'name', 'id');
                        foreach ($courses as $i => $course): ?>
                        <tr>
                          <td><?= $i + 1 ?></td>
                          <td class="fw-semibold"><?= htmlspecialchars($course['name']) ?></td>
                          <td><?= htmlspecialchars($deptById[$course['department_id']] ?? '—') ?></td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="openCourseModal(<?= $course['id'] ?>, <?= htmlspecialchars(json_encode($course['name']), ENT_QUOTES) ?>, <?= $course['department_id'] ?>)"><i class="bi bi-pencil"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Διαγραφή μαθήματος;');">
                              <input type="hidden" name="action" value="delete_course">
                              <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($courses)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Δεν υπάρχουν μαθήματα.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: PERIOD ===== -->
              <div class="tab-pane fade" id="period" role="tabpanel">
                <div class="row g-4">
                  <!-- Edit / New form -->
                  <div class="col-12 col-lg-7">
                    <div class="config-card bg-body shadow-sm">
                      <div class="config-card-header">
                        <i class="bi bi-calendar-range text-success"></i>
                        <span id="periodFormTitle"><?= $activePeriod ? 'Επεξεργασία Περιόδου' : 'Νέα Περίοδος Αιτήσεων' ?></span>
                      </div>
                      <div class="config-card-body">
                        <?php if ($activePeriod):
                            $ps = $periodStatusMap[$activePeriod['status']] ?? ['label'=>$activePeriod['status'],'class'=>'period-status-closed'];
                        ?>
                        <div class="d-flex align-items-center gap-2 mb-3">
                          <span class="period-status-badge <?= $ps['class'] ?>">
                            <i class="bi bi-circle-fill" style="font-size:.5rem;"></i> <?= $ps['label'] ?>
                          </span>
                          <small class="text-secondary"><?= htmlspecialchars($activePeriod['name']) ?></small>
                        </div>
                        <?php endif; ?>
                        <form method="POST" id="periodForm">
                          <input type="hidden" name="action" value="save_period">
                          <input type="hidden" name="period_id" id="periodFormId" value="<?= $activePeriod['id'] ?? 0 ?>">
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-semibold">Έναρξη Περιόδου <span class="text-danger">*</span></label>
                              <input type="date" class="form-control" name="start_date" id="periodStart" required value="<?= htmlspecialchars($activePeriod['start_date'] ?? '') ?>" />
                            </div>
                            <div class="col-md-6">
                              <label class="form-label fw-semibold">Λήξη Περιόδου <span class="text-danger">*</span></label>
                              <input type="date" class="form-control" name="end_date" id="periodEnd" required value="<?= htmlspecialchars($activePeriod['end_date'] ?? '') ?>" />
                            </div>
                            <div class="col-12">
                              <label class="form-label fw-semibold">Τίτλος Περιόδου <span class="text-danger">*</span></label>
                              <input type="text" class="form-control" name="name" id="periodName" required value="<?= htmlspecialchars($activePeriod['name'] ?? '') ?>" placeholder="π.χ. Εαρινό Εξάμηνο 2025–2026" />
                            </div>
                            <div class="col-12">
                              <label class="form-label fw-semibold">Περιγραφή</label>
                              <textarea class="form-control" name="description" id="periodDesc" rows="3"><?= htmlspecialchars($activePeriod['description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                              <label class="form-label fw-semibold">Κατάσταση</label>
                              <select class="form-select" name="status" id="periodStatus">
                                <option value="planning" <?= ($activePeriod['status'] ?? '') === 'planning' ? 'selected' : '' ?>>Προγραμματισμός</option>
                                <option value="active"   <?= ($activePeriod['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Ανοιχτή</option>
                                <option value="closed"   <?= ($activePeriod['status'] ?? '') === 'closed'   ? 'selected' : '' ?>>Κλειστή</option>
                                <option value="archived" <?= ($activePeriod['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Αρχειοθετήθηκε</option>
                              </select>
                            </div>
                            <div class="col-12 pt-1 d-flex gap-2 flex-wrap">
                              <button type="submit" class="btn btn-primary">
                                <i class="bi bi-floppy me-1"></i>Αποθήκευση
                              </button>
                              <button type="button" class="btn btn-outline-success" onclick="clearPeriodForm()">
                                <i class="bi bi-plus-lg me-1"></i>Νέα Περίοδος
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <!-- Periods list -->
                  <div class="col-12 col-lg-5">
                    <div class="config-card bg-body shadow-sm">
                      <div class="config-card-header"><i class="bi bi-clock-history text-warning"></i>Όλες οι Περίοδοι</div>
                      <div class="config-card-body p-0">
                        <ul class="list-group list-group-flush">
                          <?php foreach ($periods as $period):
                              $ps = $periodStatusMap[$period['status']] ?? ['label'=>$period['status'],'class'=>'period-status-closed'];
                          ?>
                          <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                            <div style="min-width:0;">
                              <div class="fw-semibold small text-truncate"><?= htmlspecialchars($period['name']) ?></div>
                              <div class="text-secondary" style="font-size:.78rem;">
                                <?= date('d/m/Y', strtotime($period['start_date'])) ?> – <?= date('d/m/Y', strtotime($period['end_date'])) ?>
                              </div>
                            </div>
                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                              <span class="period-status-badge <?= $ps['class'] ?>"><?= $ps['label'] ?></span>
                              <button class="btn btn-sm btn-outline-primary" title="Επεξεργασία"
                                onclick="editPeriod(<?= htmlspecialchars(json_encode($period), ENT_QUOTES) ?>)">
                                <i class="bi bi-pencil"></i>
                              </button>
                              <form method="POST" style="display:inline;" onsubmit="return confirm('Διαγραφή περιόδου;');">
                                <input type="hidden" name="action" value="delete_period">
                                <input type="hidden" name="period_id" value="<?= $period['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                              </form>
                            </div>
                          </li>
                          <?php endforeach; ?>
                          <?php if (empty($periods)): ?>
                          <li class="list-group-item text-muted text-center py-3">Δεν υπάρχουν περίοδοι.</li>
                          <?php endif; ?>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: EVALUATORS ===== -->
              <div class="tab-pane fade" id="evaluators" role="tabpanel">
                <div class="admin-table-card bg-body shadow-sm">
                  <div class="admin-table-toolbar">
                    <h6 class="mb-0 fw-semibold">Αναθέσεις Αξιολογητών</h6>
                    <button class="btn btn-success btn-sm" onclick="openAssignModal(0,'')">
                      <i class="bi bi-person-plus me-1"></i>Νέα Ανάθεση
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr><th>Αγγελία</th><th>Αξιολογητής</th><th>Ημ/νία Ανάθεσης</th><th class="text-end">Ενέργειες</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($evalAssignments as $ea): ?>
                        <tr>
                          <td class="fw-semibold"><?= htmlspecialchars($ea['ann_title']) ?></td>
                          <td><?= htmlspecialchars($ea['first_name'] . ' ' . $ea['last_name']) ?></td>
                          <td class="text-secondary small"><?= date('d/m/Y', strtotime($ea['created_at'])) ?></td>
                          <td class="text-end">
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Αφαίρεση αξιολογητή;');">
                              <input type="hidden" name="action" value="delete_evaluator">
                              <input type="hidden" name="ae_id" value="<?= $ea['ae_id'] ?>">
                              <button type="submit" class="btn btn-sm btn-outline-danger" title="Αφαίρεση"><i class="bi bi-person-dash"></i></button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($evalAssignments)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-person-x fs-4 d-block mb-1"></i>Δεν υπάρχουν αναθέσεις ακόμα.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

            </div>
            <!-- end tab-content -->

          </div>
        </div>
      </main>

      <?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

    </div>

    <!-- ===== MODALS ===== -->

    <!-- Add / Edit Announcement Modal -->
    <div class="modal fade" id="appModal" tabindex="-1" aria-labelledby="appModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="POST">
            <input type="hidden" name="action" value="save_announcement">
            <input type="hidden" name="announcement_id" id="modalAnnId" value="0">
            <div class="modal-header">
              <h5 class="modal-title" id="appModalLabel">Νέα Αγγελία</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label fw-semibold">Τίτλος Αγγελίας <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="title" id="modalTitle" required placeholder="π.χ. Καθηγητής Μαθηματικών Α' Τάξης" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Περίοδος <span class="text-danger">*</span></label>
                  <select class="form-select" name="period_id" id="modalPeriod" required>
                    <option value="">Επιλέξτε περίοδο...</option>
                    <?php foreach ($periods as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Κατάσταση</label>
                  <select class="form-select" name="status" id="modalStatus">
                    <option value="draft">Πρόχειρο</option>
                    <option value="published">Ανοιχτή</option>
                    <option value="closed">Κλειστή</option>
                    <option value="cancelled">Ακυρωμένη</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Σχολή <span class="text-danger">*</span></label>
                  <select class="form-select" name="school_id" id="modalSchool" required>
                    <option value="">Επιλέξτε σχολή...</option>
                    <?php foreach ($schools as $sc): ?>
                    <option value="<?= $sc['id'] ?>"><?= htmlspecialchars($sc['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Τμήμα <span class="text-danger">*</span></label>
                  <select class="form-select" name="department_id" id="modalDept" required>
                    <option value="">Επιλέξτε τμήμα...</option>
                    <?php foreach ($departments as $dep): ?>
                    <option value="<?= $dep['id'] ?>" data-school="<?= $dep['school_id'] ?>"><?= htmlspecialchars($dep['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Μάθημα <span class="text-danger">*</span></label>
                  <select class="form-select" name="course_id" id="modalCourse" required>
                    <option value="">Επιλέξτε μάθημα...</option>
                    <?php foreach ($courses as $co): ?>
                    <option value="<?= $co['id'] ?>" data-dept="<?= $co['department_id'] ?>"><?= htmlspecialchars($co['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Αριθμός Θέσεων</label>
                  <input type="number" class="form-control" name="number_of_positions" id="modalNumPos" min="1" value="1" />
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Περιγραφή</label>
                  <textarea class="form-control" name="description" id="modalDesc" rows="3" placeholder="Περιγραφή της αγγελίας..."></textarea>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
              <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Assign Evaluator Modal -->
    <div class="modal fade" id="evalModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST">
            <input type="hidden" name="action" value="assign_evaluator">
            <div class="modal-header">
              <h5 class="modal-title">Ανάθεση Αξιολογητή</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-semibold">Αγγελία</label>
                <select class="form-select" name="announcement_id" id="evalAnnSelect" required>
                  <option value="">Επιλέξτε αγγελία...</option>
                  <?php foreach ($announcements as $ann): ?>
                  <option value="<?= $ann['id'] ?>"><?= htmlspecialchars($ann['title']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Αξιολογητής</label>
                <select class="form-select" name="evaluator_id" required>
                  <option value="">Επιλέξτε αξιολογητή...</option>
                  <?php foreach ($evalUsers as $eu): ?>
                  <option value="<?= $eu['id'] ?>"><?= htmlspecialchars($eu['first_name'] . ' ' . $eu['last_name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
              <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Ανάθεση</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- School Modal -->
    <div class="modal fade" id="schoolModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
          <input type="hidden" name="action" value="save_school">
          <input type="hidden" name="school_id" id="schoolModalId" value="0">
          <div class="modal-header"><h5 class="modal-title" id="schoolModalTitle">Νέα Σχολή</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div class="mb-3"><label class="form-label fw-semibold">Όνομα Σχολής <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" id="schoolModalName" required placeholder="π.χ. Σχολή Θετικών Επιστημών" /></div>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button><button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button></div>
        </form>
      </div></div>
    </div>

    <!-- Department Modal -->
    <div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
          <input type="hidden" name="action" value="save_department">
          <input type="hidden" name="dept_id" id="deptModalId" value="0">
          <div class="modal-header"><h5 class="modal-title" id="deptModalTitle">Νέο Τμήμα</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div class="mb-3"><label class="form-label fw-semibold">Όνομα Τμήματος <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" id="deptModalName" required placeholder="π.χ. Τμήμα Μαθηματικών" /></div>
            <div class="mb-3"><label class="form-label fw-semibold">Σχολή <span class="text-danger">*</span></label>
              <select class="form-select" name="school_id" id="deptModalSchool" required>
                <option value="">Επιλέξτε σχολή...</option>
                <?php foreach ($schools as $sc): ?>
                <option value="<?= $sc['id'] ?>"><?= htmlspecialchars($sc['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button><button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button></div>
        </form>
      </div></div>
    </div>

    <!-- Course Modal -->
    <div class="modal fade" id="courseModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
          <input type="hidden" name="action" value="save_course">
          <input type="hidden" name="course_id" id="courseModalId" value="0">
          <div class="modal-header"><h5 class="modal-title" id="courseModalTitle">Νέο Μάθημα</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div class="mb-3"><label class="form-label fw-semibold">Τίτλος Μαθήματος <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" id="courseModalName" required placeholder="π.χ. Ανάλυση Ι" /></div>
            <div class="mb-3"><label class="form-label fw-semibold">Τμήμα <span class="text-danger">*</span></label>
              <select class="form-select" name="department_id" id="courseModalDept" required>
                <option value="">Επιλέξτε τμήμα...</option>
                <?php foreach ($departments as $dep): ?>
                <option value="<?= $dep['id'] ?>"><?= htmlspecialchars($dep['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button><button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button></div>
        </form>
      </div></div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/js/adminlte.js" defer></script>
    <script src="../../assets/js/changes.js" defer></script>
    <script>
      const departmentsData = <?= json_encode(array_map(static function ($dep) {
        return [
          'id' => (int)$dep['id'],
          'name' => $dep['name'],
          'school_id' => (int)$dep['school_id'],
        ];
      }, $departments), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

      const coursesData = <?= json_encode(array_map(static function ($course) {
        return [
          'id' => (int)$course['id'],
          'name' => $course['name'],
          'department_id' => (int)$course['department_id'],
        ];
      }, $courses), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

      function populateSelect(selectEl, items, placeholder, valueKey, labelKey, selectedValue, filterKey, filterValue) {
        const normalizedSelected = selectedValue ? String(selectedValue) : '';
        const normalizedFilter = filterValue ? String(filterValue) : '';

        selectEl.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder;
        selectEl.appendChild(placeholderOption);

        items
          .filter(item => !normalizedFilter || String(item[filterKey]) === normalizedFilter)
          .forEach(item => {
            const option = document.createElement('option');
            option.value = String(item[valueKey]);
            option.textContent = item[labelKey];
            if (option.value === normalizedSelected) {
              option.selected = true;
            }
            selectEl.appendChild(option);
          });
      }

      function populateDepartmentOptions(schoolId, selectedDeptId = '') {
        const deptSelect = document.getElementById('modalDept');
        populateSelect(
          deptSelect,
          departmentsData,
          'Επιλέξτε τμήμα...',
          'id',
          'name',
          selectedDeptId,
          'school_id',
          schoolId
        );
      }

      function populateCourseOptions(deptId, selectedCourseId = '') {
        const courseSelect = document.getElementById('modalCourse');
        populateSelect(
          courseSelect,
          coursesData,
          'Επιλέξτε μάθημα...',
          'id',
          'name',
          selectedCourseId,
          'department_id',
          deptId
        );
      }

      document.addEventListener('DOMContentLoaded', function () {
        const sw = document.querySelector('.sidebar-wrapper');
        if (sw && OverlayScrollbarsGlobal?.OverlayScrollbars) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sw, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
          });
        }
        const hash = window.location.hash;
        if (hash) {
          const tabBtn = document.querySelector('[data-bs-target="' + hash + '"]');
          if (tabBtn) new bootstrap.Tab(tabBtn).show();
        }

        // Search filter
        document.getElementById('appSearch').addEventListener('input', filterTable);
        document.getElementById('appStatusFilter').addEventListener('change', filterTable);

        function filterTable() {
          const search = document.getElementById('appSearch').value.toLowerCase();
          const status = document.getElementById('appStatusFilter').value;
          document.querySelectorAll('#appTable tbody tr[data-status]').forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.dataset.status;
            const matchText = text.includes(search);
            const matchStatus = !status || rowStatus === status;
            row.style.display = matchText && matchStatus ? '' : 'none';
          });
        }

        document.getElementById('modalSchool').addEventListener('change', function () {
          populateDepartmentOptions(this.value, '');
          populateCourseOptions('', '');
        });

        document.getElementById('modalDept').addEventListener('change', function () {
          populateCourseOptions(this.value, '');
        });

        populateDepartmentOptions('', '');
        populateCourseOptions('', '');
      });

      function switchTab(tabId) {
        const tabBtn = document.querySelector('[data-bs-target="#' + tabId + '"]');
        if (tabBtn) { new bootstrap.Tab(tabBtn).show(); window.location.hash = '#' + tabId; }
      }

      function openNewModal() {
        document.getElementById('appModalLabel').textContent = 'Νέα Αγγελία';
        document.getElementById('modalAnnId').value = '0';
        document.getElementById('modalTitle').value = '';
        document.getElementById('modalPeriod').value = '';
        document.getElementById('modalStatus').value = 'draft';
        document.getElementById('modalSchool').value = '';
        populateDepartmentOptions('', '');
        populateCourseOptions('', '');
        document.getElementById('modalNumPos').value = '1';
        document.getElementById('modalDesc').value = '';
        new bootstrap.Modal(document.getElementById('appModal')).show();
      }

      function openEditModal(ann) {
        document.getElementById('appModalLabel').textContent = 'Επεξεργασία Αγγελίας';
        document.getElementById('modalAnnId').value = ann.id;
        document.getElementById('modalTitle').value = ann.title;
        document.getElementById('modalPeriod').value = ann.period_id;
        document.getElementById('modalStatus').value = ann.status;
        document.getElementById('modalSchool').value = ann.school_id;
        populateDepartmentOptions(ann.school_id, ann.department_id);
        populateCourseOptions(ann.department_id, ann.course_id);
        document.getElementById('modalNumPos').value = ann.number_of_positions;
        document.getElementById('modalDesc').value = ann.description || '';
        new bootstrap.Modal(document.getElementById('appModal')).show();
      }

      function openAssignModal(annId, annTitle) {
        const sel = document.getElementById('evalAnnSelect');
        sel.value = annId || '';
        new bootstrap.Modal(document.getElementById('evalModal')).show();
      }

      function openSchoolModal(id, name) {
        document.getElementById('schoolModalTitle').textContent = id > 0 ? 'Επεξεργασία Σχολής' : 'Νέα Σχολή';
        document.getElementById('schoolModalId').value = id;
        document.getElementById('schoolModalName').value = name;
        new bootstrap.Modal(document.getElementById('schoolModal')).show();
      }

      function openDeptModal(id, name, schoolId) {
        document.getElementById('deptModalTitle').textContent = id > 0 ? 'Επεξεργασία Τμήματος' : 'Νέο Τμήμα';
        document.getElementById('deptModalId').value = id;
        document.getElementById('deptModalName').value = name;
        document.getElementById('deptModalSchool').value = schoolId;
        new bootstrap.Modal(document.getElementById('deptModal')).show();
      }

      function openCourseModal(id, name, deptId) {
        document.getElementById('courseModalTitle').textContent = id > 0 ? 'Επεξεργασία Μαθήματος' : 'Νέο Μάθημα';
        document.getElementById('courseModalId').value = id;
        document.getElementById('courseModalName').value = name;
        document.getElementById('courseModalDept').value = deptId;
        new bootstrap.Modal(document.getElementById('courseModal')).show();
      }

      function editPeriod(p) {
        document.getElementById('periodFormTitle').textContent = 'Επεξεργασία Περιόδου';
        document.getElementById('periodFormId').value  = p.id;
        document.getElementById('periodName').value    = p.name;
        document.getElementById('periodStart').value   = p.start_date;
        document.getElementById('periodEnd').value     = p.end_date;
        document.getElementById('periodStatus').value  = p.status;
        document.getElementById('periodDesc').value    = p.description || '';
        document.getElementById('periodForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      function clearPeriodForm() {
        document.getElementById('periodFormTitle').textContent = 'Νέα Περίοδος Αιτήσεων';
        document.getElementById('periodFormId').value  = '0';
        document.getElementById('periodName').value    = '';
        document.getElementById('periodStart').value   = '';
        document.getElementById('periodEnd').value     = '';
        document.getElementById('periodStatus').value  = 'planning';
        document.getElementById('periodDesc').value    = '';
      }
    </script>
  </body>
</html>

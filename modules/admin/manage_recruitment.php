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
$periods    = $pdo->query("SELECT id, name FROM recruitment_periods ORDER BY start_date DESC")->fetchAll();
$evalUsers  = $pdo->query("SELECT id, first_name, last_name FROM users ORDER BY last_name, first_name")->fetchAll();

$statusMap = [
    'published' => ['label' => 'Ανοιχτή',     'class' => 'bg-success'],
    'closed'    => ['label' => 'Κλειστή',      'class' => 'bg-secondary'],
    'draft'     => ['label' => 'Πρόχειρο',     'class' => 'bg-warning text-dark'],
    'cancelled' => ['label' => 'Ακυρωμένη',   'class' => 'bg-danger'],
];
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
                <img src="../../assets/images/avatar.png" class="user-image rounded-circle shadow" alt="Admin" />
                <span class="d-none d-md-inline">Administrator</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="../../assets/images/AdminLTELogo.png" class="rounded-circle shadow" alt="Admin" />
                  <p>Administrator<small>Διαχειριστής Συστήματος</small></p>
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
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#schoolModal">
                      <i class="bi bi-plus-lg me-1"></i>Νέα Σχολή
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>#</th><th>Όνομα Σχολής</th><th class="text-end">Ενέργειες</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($schools as $i => $school): ?>
                        <tr>
                          <td><?= $i + 1 ?></td>
                          <td class="fw-semibold"><?= htmlspecialchars($school['name']) ?></td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#deptModal">
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
                            <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#courseModal">
                      <i class="bi bi-plus-lg me-1"></i>Νέο Μάθημα
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr><th>Μάθημα</th><th>Τμήμα</th><th class="text-end">Ενέργειες</th></tr>
                      </thead>
                      <tbody>
                        <?php
                        $deptById = array_column($departments, 'name', 'id');
                        foreach ($courses as $course): ?>
                        <tr>
                          <td class="fw-semibold"><?= htmlspecialchars($course['name']) ?></td>
                          <td><?= htmlspecialchars($deptById[$course['department_id']] ?? '—') ?></td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($courses)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">Δεν υπάρχουν μαθήματα.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: PERIOD ===== -->
              <div class="tab-pane fade" id="period" role="tabpanel">
                <div class="row g-4">
                  <div class="col-12 col-lg-7">
                    <div class="config-card bg-body shadow-sm">
                      <div class="config-card-header">
                        <i class="bi bi-calendar-range text-success"></i>
                        Τρέχουσα Περίοδος Αιτήσεων
                      </div>
                      <div class="config-card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                          <span class="period-status-badge period-status-open">
                            <i class="bi bi-circle-fill" style="font-size:.5rem;"></i> Ανοιχτή
                          </span>
                          <small class="text-secondary">Η περίοδος αιτήσεων είναι ενεργή</small>
                        </div>
                        <form>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-semibold">Έναρξη Περιόδου</label>
                              <input type="date" class="form-control" value="2026-02-01" />
                            </div>
                            <div class="col-md-6">
                              <label class="form-label fw-semibold">Λήξη Περιόδου</label>
                              <input type="date" class="form-control" value="2026-04-30" />
                            </div>
                            <div class="col-12">
                              <label class="form-label fw-semibold">Τίτλος Περιόδου</label>
                              <input type="text" class="form-control" value="Εαρινό Εξάμηνο 2025–2026" />
                            </div>
                            <div class="col-12">
                              <label class="form-label fw-semibold">Περιγραφή</label>
                              <textarea class="form-control" rows="3">Περίοδος υποβολής αιτήσεων για το εαρινό εξάμηνο του ακαδημαϊκού έτους 2025-2026.</textarea>
                            </div>
                            <div class="col-md-6">
                              <label class="form-label fw-semibold">Κατάσταση</label>
                              <select class="form-select">
                                <option selected>Ανοιχτή</option>
                                <option>Κλειστή</option>
                                <option>Προσεχώς</option>
                              </select>
                            </div>
                            <div class="col-12 pt-1">
                              <button type="button" class="btn btn-primary">
                                <i class="bi bi-floppy me-1"></i>Αποθήκευση Αλλαγών
                              </button>
                              <button type="button" class="btn btn-danger ms-2">
                                <i class="bi bi-x-circle me-1"></i>Κλείσιμο Περιόδου
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-lg-5">
                    <div class="config-card bg-body shadow-sm">
                      <div class="config-card-header"><i class="bi bi-clock-history text-warning"></i>Ιστορικό Περιόδων</div>
                      <div class="config-card-body p-0">
                        <ul class="list-group list-group-flush">
                          <?php foreach ($periods as $period): ?>
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                              <div class="fw-semibold small"><?= htmlspecialchars($period['name']) ?></div>
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
                    <h6 class="mb-0 fw-semibold">Ανάθεση Αξιολογητών σε Αιτήσεις</h6>
                    <button class="btn btn-success btn-sm" onclick="openAssignModal(0,'')">
                      <i class="bi bi-person-plus me-1"></i>Νέα Ανάθεση
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr><th>Αίτηση</th><th>Αξιολογητής</th><th class="text-end">Ενέργειες</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($announcements as $ann): if (empty($ann['evaluators'])) continue; ?>
                        <tr>
                          <td><?= htmlspecialchars($ann['title']) ?></td>
                          <td><?= htmlspecialchars($ann['evaluators']) ?></td>
                          <td class="text-end"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-person-dash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
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

      <!-- ===== FOOTER ===== -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">BigBottleBOYS &copy; 2026</div>
        <strong>Copyright &copy; 2026 <a href="#" class="text-decoration-none">TheBigBottleBoys</a>.</strong> All rights reserved.
      </footer>

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

    <!-- Generic small modals -->
    <div class="modal fade" id="schoolModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Νέα Σχολή</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label fw-semibold">Όνομα Σχολής</label><input type="text" class="form-control" placeholder="π.χ. Σχολή Θετικών Επιστημών" /></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button><button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button></div>
      </div></div>
    </div>

    <div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Νέο Τμήμα</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label fw-semibold">Όνομα Τμήματος</label><input type="text" class="form-control" placeholder="π.χ. Τμήμα Μαθηματικών" /></div>
          <div class="mb-3"><label class="form-label fw-semibold">Σχολή</label>
            <select class="form-select">
              <option value="">Επιλέξτε σχολή...</option>
              <?php foreach ($schools as $sc): ?>
              <option value="<?= $sc['id'] ?>"><?= htmlspecialchars($sc['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button><button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button></div>
      </div></div>
    </div>

    <div class="modal fade" id="courseModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Νέο Μάθημα</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12"><label class="form-label fw-semibold">Τίτλος Μαθήματος</label><input type="text" class="form-control" placeholder="π.χ. Ανάλυση Ι" /></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Τμήμα</label>
              <select class="form-select">
                <option value="">Επιλέξτε...</option>
                <?php foreach ($departments as $dep): ?>
                <option value="<?= $dep['id'] ?>"><?= htmlspecialchars($dep['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button><button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button></div>
      </div></div>
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

        // Filter departments & courses in modal based on school selection
        document.getElementById('modalSchool').addEventListener('change', function () {
          const schoolId = this.value;
          const deptSel = document.getElementById('modalDept');
          deptSel.querySelectorAll('option[data-school]').forEach(opt => {
            opt.style.display = (!schoolId || opt.dataset.school === schoolId) ? '' : 'none';
          });
          deptSel.value = '';
          document.getElementById('modalCourse').value = '';
          document.getElementById('modalCourse').querySelectorAll('option[data-dept]').forEach(opt => {
            opt.style.display = 'none';
          });
        });

        document.getElementById('modalDept').addEventListener('change', function () {
          const deptId = this.value;
          document.getElementById('modalCourse').querySelectorAll('option[data-dept]').forEach(opt => {
            opt.style.display = (!deptId || opt.dataset.dept === deptId) ? '' : 'none';
          });
          document.getElementById('modalCourse').value = '';
        });
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
        document.getElementById('modalDept').value = '';
        document.getElementById('modalCourse').value = '';
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
        document.getElementById('modalDept').value = ann.department_id;
        document.getElementById('modalCourse').value = ann.course_id;
        document.getElementById('modalNumPos').value = ann.number_of_positions;
        document.getElementById('modalDesc').value = ann.description || '';
        new bootstrap.Modal(document.getElementById('appModal')).show();
      }

      function openAssignModal(annId, annTitle) {
        const sel = document.getElementById('evalAnnSelect');
        sel.value = annId || '';
        new bootstrap.Modal(document.getElementById('evalModal')).show();
      }
    </script>
  </body>
</html>
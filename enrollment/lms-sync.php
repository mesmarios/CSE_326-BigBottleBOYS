<?php
declare(strict_types=1);

$enrollment_allowed = ['admin', 'hr', 'ee_hired'];
require_once __DIR__ . '/includes/enrollment-guard.php';
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/admin-branding.php';

$brandingContext = adminGetBrandingContext($pdo);
$adminBrandText  = $brandingContext['brand_text'];
$adminLogo       = preg_replace('#^\.\./\.\./assets/#', '../assets/', (string)$brandingContext['logo']) ?? (string)$brandingContext['logo'];
$adminFavicon    = $brandingContext['favicon'];

$enrollFullName  = trim((string)(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')));
if ($enrollFullName === '') { $enrollFullName = 'Χρήστης'; }
$enrollAvatarSrc = enrollResolveAvatarSrc($pdo, (int)($_SESSION['user_id'] ?? 0));

$pageTitle         = 'LMS Sync';
$currentEnrollPage = 'lms-sync.php';

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

// ── Moodle settings from system_settings ──────────────────────────────────
$moodleUrl = '';
try {
    $s = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('moodle_url','moodle_api_key','moodle_token')");
    $s->execute();
    $moodleSettings = [];
    foreach ($s->fetchAll() as $row) { $moodleSettings[$row['setting_key']] = $row['setting_value']; }
    $moodleUrl = rtrim((string)($moodleSettings['moodle_url'] ?? ''), '/');
} catch (Throwable $e) {}

// ── Handle POST actions (admin/hr only) ───────────────────────────────────
$actionMsg  = trim((string)($_GET['msg'] ?? ''));
$actionType = (string)($_GET['type'] ?? 'success');
if ($actionMsg === '') {
    $actionMsg = null;
}
if (!in_array($actionType, ['success', 'danger', 'warning', 'info'], true)) {
    $actionType = 'success';
}

// ── Data for ee_hired view ─────────────────────────────────────────────────
$myAccess = null;
if ($_enrollment_role === 'ee_hired') {
    try {
        $stmt = $pdo->prepare(
            "SELECT la.status, la.granted_at, c.name AS course_name, c.id AS course_id
             FROM lms_access la
             LEFT JOIN courses c ON c.id = la.course_id
             WHERE la.user_id = ?
             ORDER BY la.status = 'active' DESC, la.updated_at DESC
             LIMIT 1"
        );
        $stmt->execute([(int)$_SESSION['user_id']]);
        $myAccess = $stmt->fetch() ?: null;
    } catch (Throwable $e) {}
}

// ── Data for admin/hr view ─────────────────────────────────────────────────
$eeUsers  = [];
$courses  = [];
$keyword  = '';
$statusFilter = '';

if (in_array($_enrollment_role, ['admin','hr'], true)) {
    $keyword      = trim($_GET['q'] ?? '');
    $statusFilter = in_array($_GET['status'] ?? '', ['active','inactive','none'], true) ? $_GET['status'] : '';

    try {
        $courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll();
    } catch (Throwable $e) {}

    try {
        $sql = "SELECT u.id, u.first_name, u.last_name, u.email,
                       la.status AS lms_status, la.course_id AS lms_course_id, la.granted_at,
                       c.name AS course_name
                FROM users u
                LEFT JOIN lms_access la ON la.user_id = u.id AND la.status = 'active'
                LEFT JOIN courses c ON c.id = la.course_id
                WHERE u.role = 'ee_hired'";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $kw = '%' . $keyword . '%';
            $params = array_merge($params, [$kw, $kw, $kw]);
        }

        if ($statusFilter === 'active') {
            $sql .= " AND la.status = 'active'";
        } elseif ($statusFilter === 'inactive') {
            $sql .= " AND (la.status = 'inactive' OR la.id IS NULL)";
        } elseif ($statusFilter === 'none') {
            $sql .= " AND la.id IS NULL";
        }

        $sql .= " ORDER BY u.last_name, u.first_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $eeUsers = $stmt->fetchAll();
    } catch (Throwable $e) {
        $eeUsers = [];
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>LMS Sync</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">LMS Sync</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">

      <?php if ($actionMsg !== null): ?>
      <div class="alert alert-<?= h($actionType) ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-<?= $actionType === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i><?= h($actionMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <?php if ($_enrollment_role === 'ee_hired'): ?>
      <!-- ── EE_HIRED VIEW ── -->
      <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header bg-body border-0">
              <h5 class="card-title mb-0 fw-semibold">
                <i class="bi bi-mortarboard me-2 text-primary"></i>Πρόσβαση Moodle
              </h5>
            </div>
            <div class="card-body">
              <?php if ($myAccess && $myAccess['lms_status'] === 'active'): ?>
              <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Ενεργή πρόσβαση</strong> στο μάθημα <strong><?= h((string)$myAccess['course_name']) ?></strong>.
              </div>
              <?php if ($moodleUrl !== ''): ?>
              <a href="<?= h($moodleUrl) ?>" target="_blank" rel="noopener" class="btn btn-primary">
                <i class="bi bi-box-arrow-up-right me-2"></i>Είσοδος στο Moodle
              </a>
              <?php endif; ?>
              <div class="mt-3 small text-secondary">
                Πρόσβαση εκχωρήθηκε: <?= h($myAccess['granted_at'] ? date('d/m/Y H:i', strtotime($myAccess['granted_at'])) : '—') ?>
              </div>
              <?php else: ?>
              <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Δεν υπάρχει ενεργή πρόσβαση Moodle</strong> για τον λογαριασμό σας.
                Επικοινωνήστε με τον διαχειριστή για εκχώρηση πρόσβασης.
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <?php else: ?>
      <!-- ── ADMIN / HR VIEW ── -->
      <div class="card shadow-sm">
        <div class="card-header bg-body border-0">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0 fw-semibold">
              <i class="bi bi-people-fill me-2 text-primary"></i>
              Ειδικοί Επιστήμονες — Πρόσβαση LMS
            </h5>
            <span class="badge text-bg-secondary"><?= count($eeUsers) ?> χρήστες</span>
          </div>
        </div>
        <div class="card-header bg-body border-0 pt-0">
          <form method="get" class="row g-2">
            <div class="col-md-5">
              <input type="text" name="q" class="form-control form-control-sm"
                     placeholder="Αναζήτηση ονόματος ή email…"
                     value="<?= h($keyword) ?>">
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select form-select-sm">
                <option value="">Όλες οι καταστάσεις</option>
                <option value="active"   <?= $statusFilter === 'active'   ? 'selected' : '' ?>>Ενεργοί</option>
                <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Ανενεργοί</option>
                <option value="none"     <?= $statusFilter === 'none'     ? 'selected' : '' ?>>Χωρίς εγγραφή</option>
              </select>
            </div>
            <div class="col-auto">
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-search me-1"></i>Φίλτρο
              </button>
              <a href="lms-sync.php" class="btn btn-outline-secondary btn-sm">Καθαρισμός</a>
            </div>
          </form>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0" id="lmsTable">
              <thead>
                <tr>
                  <th class="ps-4">Χρήστης</th>
                  <th>Email</th>
                  <th>Μάθημα</th>
                  <th>Κατάσταση LMS</th>
                  <th class="text-end pe-4">Ενέργειες</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($eeUsers === []): ?>
                <tr><td colspan="5" class="text-center py-4 text-secondary">Δεν βρέθηκαν χρήστες.</td></tr>
                <?php else: ?>
                <?php foreach ($eeUsers as $eu):
                  $isActive = ($eu['lms_status'] ?? '') === 'active';
                  $fullName = trim((string)$eu['first_name'] . ' ' . (string)$eu['last_name']);
                ?>
                <tr>
                  <td class="ps-4 fw-semibold"><?= h($fullName) ?></td>
                  <td class="text-secondary"><?= h((string)$eu['email']) ?></td>
                  <td><?= $eu['course_name'] ? h((string)$eu['course_name']) : '<span class="text-muted">—</span>' ?></td>
                  <td>
                    <?php if ($isActive): ?>
                    <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Ενεργή</span>
                    <?php else: ?>
                    <span class="badge text-bg-secondary"><i class="bi bi-slash-circle me-1"></i>Ανενεργή</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end pe-4">
                    <div class="d-flex gap-1 justify-content-end flex-wrap">
                      <!-- Enable -->
                      <button type="button" class="btn btn-success btn-sm"
                              data-bs-toggle="modal"
                              data-bs-target="#enableModal"
                              data-user-id="<?= (int)$eu['id'] ?>"
                              data-user-name="<?= h($fullName) ?>">
                        <i class="bi bi-check-lg"></i> Ενεργοποίηση
                      </button>
                      <!-- Disable -->
                      <?php if ($isActive): ?>
                      <form method="post" action="../api/enrollment.php" class="js-enrollment-api-form" style="display:inline;"
                            data-confirm="Απενεργοποίηση πρόσβασης για <?= h($fullName) ?>;">
                        <input type="hidden" name="lms_action" value="disable">
                        <input type="hidden" name="target_user_id" value="<?= (int)$eu['id'] ?>">
                        <button type="submit" class="btn btn-warning btn-sm">
                          <i class="bi bi-slash-circle"></i> Απενεργοποίηση
                        </button>
                      </form>
                      <?php endif; ?>
                      <!-- Change course -->
                      <button type="button" class="btn btn-outline-primary btn-sm"
                              data-bs-toggle="modal"
                              data-bs-target="#changeCourseModal"
                              data-user-id="<?= (int)$eu['id'] ?>"
                              data-user-name="<?= h($fullName) ?>">
                        <i class="bi bi-pencil"></i> Μάθημα
                      </button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal: Enable access -->
      <div class="modal fade" id="enableModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form method="post" action="../api/enrollment.php" class="js-enrollment-api-form">
              <input type="hidden" name="lms_action" value="enable">
              <input type="hidden" name="target_user_id" id="enableUserId">
              <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2 text-success"></i>Ενεργοποίηση Πρόσβασης</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p class="mb-3">Επιλέξτε μάθημα για τον/την <strong id="enableUserName"></strong>:</p>
                <select class="form-select" name="target_course_id" required>
                  <option value="">Επιλέξτε μάθημα…</option>
                  <?php foreach ($courses as $c): ?>
                  <option value="<?= (int)$c['id'] ?>"><?= h((string)$c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Ενεργοποίηση</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal: Change course -->
      <div class="modal fade" id="changeCourseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form method="post" action="../api/enrollment.php" class="js-enrollment-api-form">
              <input type="hidden" name="lms_action" value="change_course">
              <input type="hidden" name="target_user_id" id="changeCourseUserId">
              <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2 text-primary"></i>Αλλαγή Μαθήματος</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p class="mb-3">Νέο μάθημα για τον/την <strong id="changeCourseUserName"></strong>:</p>
                <select class="form-select" name="target_course_id" required>
                  <option value="">Επιλέξτε μάθημα…</option>
                  <?php foreach ($courses as $c): ?>
                  <option value="<?= (int)$c['id'] ?>"><?= h((string)$c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Αποθήκευση</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <?php endif; ?>

    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>

</div><!-- /.app-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="../assets/js/adminlte.js" defer></script>
<script src="../assets/js/changes.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var sw = document.querySelector('.sidebar-wrapper');
  if (sw && OverlayScrollbarsGlobal?.OverlayScrollbars) {
    OverlayScrollbarsGlobal.OverlayScrollbars(sw, { scrollbars: { theme:'os-theme-light', autoHide:'leave', clickScroll:true } });
  }

  // Populate enable modal
  document.getElementById('enableModal')?.addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('enableUserId').value   = btn.dataset.userId;
    document.getElementById('enableUserName').textContent = btn.dataset.userName;
  });

  // Populate change-course modal
  document.getElementById('changeCourseModal')?.addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('changeCourseUserId').value        = btn.dataset.userId;
    document.getElementById('changeCourseUserName').textContent = btn.dataset.userName;
  });

  // Client-side keyword filter
  var searchInput = document.querySelector('input[name="q"]');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var q = this.value.toLowerCase();
      document.querySelectorAll('#lmsTable tbody tr').forEach(function (row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }

  function redirectWithFlash(type, message) {
    var url = new URL(window.location.href);
    url.searchParams.set('type', type);
    url.searchParams.set('msg', message);
    window.location.href = url.toString();
  }

  document.querySelectorAll('form.js-enrollment-api-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();

      var confirmText = form.getAttribute('data-confirm');
      if (confirmText && !window.confirm(confirmText)) {
        return;
      }

      var submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      fetch(form.getAttribute('action') || '../api/enrollment.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form)
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.success) {
          redirectWithFlash('success', data.message || 'Enrollment action completed.');
          return;
        }
        redirectWithFlash('danger', (data && data.error) ? data.error : 'Enrollment action failed.');
      })
      .catch(function () {
        redirectWithFlash('danger', 'Network error while calling enrollment API.');
      })
      .finally(function () {
        if (submitBtn) submitBtn.disabled = false;
      });
    });
  });
});
</script>
</body>
</html>

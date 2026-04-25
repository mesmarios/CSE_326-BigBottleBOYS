<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/enrollment-guard.php';

$currentRole = normalizeAppRole((string)($_SESSION['role'] ?? 'candidate'));
$currentUserId = (int)($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && roleHasEnrollmentManagerAccess($currentRole)) {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'set_status') {
            $enrollmentId = (int)($_POST['enrollment_id'] ?? 0);
            $newStatus = (string)($_POST['access_status'] ?? '');
            $allowedStatuses = ['active', 'inactive', 'suspended', 'pending'];

            if ($enrollmentId > 0 && in_array($newStatus, $allowedStatuses, true)) {
                $stmt = $pdo->prepare('SELECT user_id, course_id FROM specialist_enrollments WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $enrollmentId]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $update = $pdo->prepare(
                        "
                        UPDATE specialist_enrollments
                        SET access_status = :status,
                            enrolled_at = CASE WHEN :status = 'active' AND enrolled_at IS NULL THEN NOW() ELSE enrolled_at END,
                            updated_at = NOW()
                        WHERE id = :id
                        "
                    );
                    $update->execute([':status' => $newStatus, ':id' => $enrollmentId]);

                    $log = $pdo->prepare(
                        "
                        INSERT INTO enrollment_logs
                            (user_id, action, action_type, target_id, target_type, details, status, performed_by)
                        VALUES
                            (:user_id, :action, :action_type, :target_id, 'specialist_enrollment', :details, 'success', :performed_by)
                        "
                    );
                    $log->execute([
                        ':user_id' => (int)$existing['user_id'],
                        ':action' => 'Status changed to ' . $newStatus,
                        ':action_type' => $newStatus === 'active' ? 'manual_enroll' : 'manual_unenroll',
                        ':target_id' => $enrollmentId,
                        ':details' => json_encode(['course_id' => (int)$existing['course_id'], 'new_status' => $newStatus], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ':performed_by' => $currentUserId,
                    ]);

                    $_SESSION['enrollment_flash'] = ['type' => 'success', 'message' => 'Η κατάσταση πρόσβασης ενημερώθηκε επιτυχώς.'];
                }
            }
        }

        if ($action === 'assign_course') {
            $specialistId = (int)($_POST['specialist_id'] ?? 0);
            $courseId = (int)($_POST['course_id'] ?? 0);

            if ($specialistId > 0 && $courseId > 0) {
                $check = $pdo->prepare('SELECT id FROM specialist_enrollments WHERE user_id = :user_id AND course_id = :course_id LIMIT 1');
                $check->execute([':user_id' => $specialistId, ':course_id' => $courseId]);
                $existingId = $check->fetchColumn();

                if ($existingId) {
                    $update = $pdo->prepare("UPDATE specialist_enrollments SET access_status = 'pending', updated_at = NOW() WHERE id = :id");
                    $update->execute([':id' => $existingId]);
                } else {
                    $insert = $pdo->prepare(
                        "
                        INSERT INTO specialist_enrollments (user_id, course_id, access_status)
                        VALUES (:user_id, :course_id, 'pending')
                        "
                    );
                    $insert->execute([':user_id' => $specialistId, ':course_id' => $courseId]);
                    $existingId = (int)$pdo->lastInsertId();
                }

                $log = $pdo->prepare(
                    "
                    INSERT INTO enrollment_logs
                        (user_id, action, action_type, target_id, target_type, details, status, performed_by)
                    VALUES
                        (:user_id, 'Course assigned for LMS access', 'manual_enroll', :target_id, 'specialist_enrollment', :details, 'success', :performed_by)
                    "
                );
                $log->execute([
                    ':user_id' => $specialistId,
                    ':target_id' => (int)$existingId,
                    ':details' => json_encode(['course_id' => $courseId], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ':performed_by' => $currentUserId,
                ]);

                $_SESSION['enrollment_flash'] = ['type' => 'success', 'message' => 'Το μάθημα ανατέθηκε και στάλθηκε για συγχρονισμό.'];
            }
        }

        if ($action === 'check_access') {
            $enrollmentId = (int)($_POST['enrollment_id'] ?? 0);
            if ($enrollmentId > 0) {
                $stmt = $pdo->prepare('SELECT user_id, access_status FROM specialist_enrollments WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $enrollmentId]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $log = $pdo->prepare(
                        "
                        INSERT INTO enrollment_logs
                            (user_id, action, action_type, target_id, target_type, details, status, performed_by)
                        VALUES
                            (:user_id, 'Access check completed', 'status_check', :target_id, 'specialist_enrollment', :details, 'success', :performed_by)
                        "
                    );
                    $log->execute([
                        ':user_id' => (int)$existing['user_id'],
                        ':target_id' => $enrollmentId,
                        ':details' => json_encode(['current_status' => (string)$existing['access_status']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ':performed_by' => $currentUserId,
                    ]);

                    $_SESSION['enrollment_flash'] = ['type' => 'success', 'message' => 'Ο έλεγχος πρόσβασης καταγράφηκε επιτυχώς.'];
                }
            }
        }
    } catch (Throwable $e) {
        $_SESSION['enrollment_flash'] = ['type' => 'danger', 'message' => 'Δεν ήταν δυνατή η ολοκλήρωση της ενέργειας.'];
    }

    header('Location: lms_sync.php');
    exit;
}

$enrollmentPageTitle = 'LMS Sync';
$enrollmentPageHeading = 'LMS Sync';
$enrollmentPageDescription = 'Τοπική διαχείριση πρόσβασης LMS χωρίς εξωτερική σύνδεση Moodle.';
$enrollmentActivePage = 'lms_sync';
require_once __DIR__ . '/../../includes/enrollment-top.php';

$lmsPortalUrl = 'https://moodle.tepak.cy';
$records = [];
$specialists = [];
$courses = [];

try {
    $settingStmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'lms_portal_url' LIMIT 1");
    $settingStmt->execute();
    $lmsPortalUrl = trim((string)($settingStmt->fetchColumn() ?: $lmsPortalUrl));

    if ($currentRole === 'specialist') {
        $stmt = $pdo->prepare(
            "
            SELECT
                se.id,
                se.access_status,
                se.enrolled_at,
                se.updated_at,
                c.code,
                c.name AS course_name,
                d.name AS department_name,
                s.name AS school_name
            FROM specialist_enrollments se
            INNER JOIN courses c ON c.id = se.course_id
            INNER JOIN departments d ON d.id = c.department_id
            INNER JOIN schools s ON s.id = d.school_id
            WHERE se.user_id = :user_id
            ORDER BY c.code ASC
            "
        );
        $stmt->execute([':user_id' => $currentUserId]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $records = $pdo->query(
            "
            SELECT
                se.id,
                se.user_id,
                CONCAT(u.first_name, ' ', u.last_name) AS specialist_name,
                u.email,
                se.access_status,
                se.enrolled_at,
                se.updated_at,
                c.code,
                c.name AS course_name,
                d.name AS department_name,
                s.name AS school_name
            FROM specialist_enrollments se
            INNER JOIN users u ON u.id = se.user_id
            INNER JOIN courses c ON c.id = se.course_id
            INNER JOIN departments d ON d.id = c.department_id
            INNER JOIN schools s ON s.id = d.school_id
            ORDER BY specialist_name ASC, c.code ASC
            "
        )->fetchAll(PDO::FETCH_ASSOC);

        $specialists = $pdo->query(
            "
            SELECT id, CONCAT(first_name, ' ', last_name) AS full_name
            FROM users
            WHERE role = 'specialist'
            ORDER BY first_name ASC, last_name ASC
            "
        )->fetchAll(PDO::FETCH_ASSOC);

        $courses = $pdo->query(
            "
            SELECT c.id, CONCAT(c.code, ' - ', c.name) AS full_name
            FROM courses c
            ORDER BY c.code ASC
            "
        )->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
}

function lmsSyncStatusMeta(string $status): array
{
    return match ($status) {
        'active' => ['label' => 'Active', 'class' => 'text-bg-success'],
        'pending' => ['label' => 'Pending', 'class' => 'text-bg-warning'],
        'inactive' => ['label' => 'Inactive', 'class' => 'text-bg-secondary'],
        'suspended' => ['label' => 'Suspended', 'class' => 'text-bg-danger'],
        default => ['label' => ucfirst($status), 'class' => 'text-bg-light'],
    };
}

function lmsSyncDate(?string $value): string
{
    if (!$value) {
        return '—';
    }

    try {
        return (new DateTimeImmutable($value))->format('d/m/Y H:i');
    } catch (Throwable $e) {
        return '—';
    }
}
?>
<?php if ($currentRole === 'specialist'): ?>
<div class="card enrollment-hero-card mb-4">
  <div class="card-body p-4">
    <div class="row g-4 align-items-center">
      <div class="col-lg-8">
        <span class="badge text-bg-light text-primary mb-3">Specialist Access</span>
        <h2 class="fw-bold mb-2">Έλεγχος προσωπικής πρόσβασης στο LMS</h2>
        <p class="mb-0 text-white-50">Εδώ βλέπεις άμεσα ποια μαθήματα έχουν ενεργοποιηθεί για σένα και το link σύνδεσης στο LMS portal.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="<?= enrollmentH($lmsPortalUrl) ?>" target="_blank" rel="noopener" class="btn btn-light fw-semibold">
          <i class="bi bi-box-arrow-up-right me-2"></i>Άνοιγμα LMS
        </a>
      </div>
    </div>
  </div>
</div>

<div class="card enrollment-section-card shadow-sm">
  <div class="card-header bg-white">
    <h5 class="mb-0">Η πρόσβασή μου</h5>
  </div>
  <div class="card-body">
    <?php if ($records === []): ?>
      <div class="text-center text-secondary py-5">
        <i class="bi bi-person-x fs-1 d-block mb-2"></i>
        Δεν υπάρχει ακόμη καταχωρημένη πρόσβαση για τον λογαριασμό σου.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Μάθημα</th>
              <th>Τμήμα</th>
              <th>Σχολή</th>
              <th>Κατάσταση</th>
              <th>Ενεργοποίηση</th>
              <th>Τελευταία ενημέρωση</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($records as $record): ?>
              <?php $status = lmsSyncStatusMeta((string)$record['access_status']); ?>
              <tr>
                <td><strong><?= enrollmentH((string)$record['code']) ?></strong> - <?= enrollmentH((string)$record['course_name']) ?></td>
                <td><?= enrollmentH((string)$record['department_name']) ?></td>
                <td><?= enrollmentH((string)$record['school_name']) ?></td>
                <td><span class="badge <?= enrollmentH($status['class']) ?>"><?= enrollmentH($status['label']) ?></span></td>
                <td><?= enrollmentH(lmsSyncDate((string)$record['enrolled_at'])) ?></td>
                <td><?= enrollmentH(lmsSyncDate((string)$record['updated_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php else: ?>
<div class="row g-4 mb-4">
  <div class="col-xl-4">
    <div class="card enrollment-section-card shadow-sm h-100">
      <div class="card-header bg-white">
        <h5 class="mb-0">Ανάθεση νέου μαθήματος</h5>
      </div>
      <div class="card-body">
        <form method="post" class="d-grid gap-3">
          <input type="hidden" name="action" value="assign_course">
          <div>
            <label class="form-label fw-semibold">Ειδικός Επιστήμονας</label>
            <select name="specialist_id" class="form-select" required>
              <option value="">Επιλέξτε ειδικό επιστήμονα...</option>
              <?php foreach ($specialists as $specialist): ?>
              <option value="<?= (int)$specialist['id'] ?>"><?= enrollmentH((string)$specialist['full_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label fw-semibold">Μάθημα</label>
            <select name="course_id" class="form-select" required>
              <option value="">Επιλέξτε μάθημα...</option>
              <?php foreach ($courses as $course): ?>
              <option value="<?= (int)$course['id'] ?>"><?= enrollmentH((string)$course['full_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Ανάθεση και Sync Queue
          </button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-xl-8">
    <div class="card enrollment-section-card shadow-sm h-100">
      <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h5 class="mb-0">Λίστα προσβάσεων LMS</h5>
          <div class="d-flex gap-2">
            <input type="text" id="syncSearch" class="form-control form-control-sm" placeholder="Αναζήτηση..." style="width:220px;">
            <select id="syncStatusFilter" class="form-select form-select-sm" style="width:170px;">
              <option value="">Όλες οι καταστάσεις</option>
              <option value="active">Active</option>
              <option value="pending">Pending</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="syncTable">
            <thead class="table-light">
              <tr>
                <th>Ειδικός Επιστήμονας</th>
                <th>Μάθημα</th>
                <th>Κατάσταση</th>
                <th>Έλεγχος</th>
                <th>Τελευταία ενημέρωση</th>
                <th class="text-end">Ενέργειες</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($records as $record): ?>
                <?php $status = lmsSyncStatusMeta((string)$record['access_status']); ?>
                <tr data-status="<?= enrollmentH((string)$record['access_status']) ?>">
                  <td>
                    <div class="fw-semibold"><?= enrollmentH((string)$record['specialist_name']) ?></div>
                    <div class="text-secondary small"><?= enrollmentH((string)$record['email']) ?></div>
                  </td>
                  <td>
                    <div><strong><?= enrollmentH((string)$record['code']) ?></strong> - <?= enrollmentH((string)$record['course_name']) ?></div>
                    <div class="text-secondary small"><?= enrollmentH((string)$record['department_name']) ?> / <?= enrollmentH((string)$record['school_name']) ?></div>
                  </td>
                  <td><span class="badge <?= enrollmentH($status['class']) ?>"><?= enrollmentH($status['label']) ?></span></td>
                  <td><?= enrollmentH(lmsSyncDate((string)$record['enrolled_at'])) ?></td>
                  <td><?= enrollmentH(lmsSyncDate((string)$record['updated_at'])) ?></td>
                  <td class="text-end">
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                      <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="check_access">
                        <input type="hidden" name="enrollment_id" value="<?= (int)$record['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search me-1"></i>Check</button>
                      </form>
                      <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="set_status">
                        <input type="hidden" name="enrollment_id" value="<?= (int)$record['id'] ?>">
                        <input type="hidden" name="access_status" value="active">
                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check2 me-1"></i>Activate</button>
                      </form>
                      <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="set_status">
                        <input type="hidden" name="enrollment_id" value="<?= (int)$record['id'] ?>">
                        <input type="hidden" name="access_status" value="inactive">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-slash-circle me-1"></i>Deactivate</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('syncSearch');
    const statusFilter = document.getElementById('syncStatusFilter');
    const rows = Array.from(document.querySelectorAll('#syncTable tbody tr'));

    function filterRows() {
      const search = (searchInput.value || '').toLowerCase().trim();
      const status = statusFilter.value;

      rows.forEach(function (row) {
        const textMatch = row.textContent.toLowerCase().includes(search);
        const statusMatch = !status || row.dataset.status === status;
        row.style.display = textMatch && statusMatch ? '' : 'none';
      });
    }

    searchInput.addEventListener('input', filterRows);
    statusFilter.addEventListener('change', filterRows);
  });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/enrollment-bottom.php'; ?>


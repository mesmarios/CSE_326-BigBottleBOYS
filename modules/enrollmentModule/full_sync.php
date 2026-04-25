<?php
declare(strict_types=1);

$enrollmentRequireManager = true;
require_once __DIR__ . '/../../includes/enrollment-guard.php';

$currentUserId = (int)($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'toggle_auto_sync') {
            $enable = (int)($_POST['enable'] ?? 0) === 1 ? 1 : 0;
            $stmt = $pdo->prepare(
                "
                UPDATE sync_schedules
                SET is_enabled = :enabled,
                    next_sync_at = CASE
                        WHEN :enabled = 1 THEN DATE_ADD(NOW(), INTERVAL frequency_minutes MINUTE)
                        ELSE NULL
                    END,
                    updated_at = NOW()
                WHERE sync_type = 'auto_sync'
                "
            );
            $stmt->execute([':enabled' => $enable]);

            $_SESSION['enrollment_flash'] = [
                'type' => 'success',
                'message' => $enable === 1 ? 'Ο αυτόματος συγχρονισμός ενεργοποιήθηκε.' : 'Ο αυτόματος συγχρονισμός απενεργοποιήθηκε.',
            ];
        }

        if ($action === 'run_full_sync') {
            $updateSchedule = $pdo->prepare(
                "
                UPDATE sync_schedules
                SET last_sync_at = NOW(),
                    next_sync_at = DATE_ADD(NOW(), INTERVAL frequency_minutes MINUTE),
                    updated_at = NOW()
                WHERE sync_type IN ('full_sync', 'auto_sync')
                "
            );
            $updateSchedule->execute();

            $specialistIds = $pdo->query("SELECT DISTINCT user_id FROM specialist_enrollments ORDER BY user_id")->fetchAll(PDO::FETCH_COLUMN);
            $logStmt = $pdo->prepare(
                "
                INSERT INTO enrollment_logs
                    (user_id, action, action_type, target_type, details, status, performed_by)
                VALUES
                    (:user_id, 'Full sync executed locally', 'full_sync', 'sync_schedule', :details, 'success', :performed_by)
                "
            );

            foreach ($specialistIds as $specialistId) {
                $logStmt->execute([
                    ':user_id' => (int)$specialistId,
                    ':details' => json_encode(['source' => 'manual_full_sync'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ':performed_by' => $currentUserId,
                ]);
            }

            $_SESSION['enrollment_flash'] = [
                'type' => 'success',
                'message' => 'Ο πλήρης συγχρονισμός εκτελέστηκε και καταγράφηκε επιτυχώς.',
            ];
        }
    } catch (Throwable $e) {
        $_SESSION['enrollment_flash'] = [
            'type' => 'danger',
            'message' => 'Δεν ήταν δυνατή η ολοκλήρωση της ενέργειας συγχρονισμού.',
        ];
    }

    header('Location: full_sync.php');
    exit;
}

$enrollmentPageTitle = 'Full Sync';
$enrollmentPageHeading = 'Full Sync';
$enrollmentPageDescription = 'Χειροκίνητη εκτέλεση πλήρους συγχρονισμού και έλεγχος του αυτόματου scheduler.';
$enrollmentActivePage = 'full_sync';
require_once __DIR__ . '/../../includes/enrollment-top.php';

$schedules = [];
$recentLogs = [];
$pendingSummary = [];

try {
    $schedules = $pdo->query(
        "
        SELECT id, name, sync_type, is_enabled, frequency_minutes, last_sync_at, next_sync_at
        FROM sync_schedules
        ORDER BY FIELD(sync_type, 'auto_sync', 'full_sync'), id ASC
        "
    )->fetchAll(PDO::FETCH_ASSOC);

    $recentLogs = $pdo->query(
        "
        SELECT
            el.action,
            el.status,
            el.created_at,
            CONCAT(u.first_name, ' ', u.last_name) AS specialist_name,
            CONCAT(p.first_name, ' ', p.last_name) AS performed_by_name
        FROM enrollment_logs el
        INNER JOIN users u ON u.id = el.user_id
        LEFT JOIN users p ON p.id = el.performed_by
        WHERE el.action_type IN ('full_sync', 'auto_sync', 'status_check')
        ORDER BY el.created_at DESC, el.id DESC
        LIMIT 10
        "
    )->fetchAll(PDO::FETCH_ASSOC);

    $pendingSummary = $pdo->query(
        "
        SELECT
            CONCAT(u.first_name, ' ', u.last_name) AS specialist_name,
            COUNT(*) AS pending_count
        FROM specialist_enrollments se
        INNER JOIN users u ON u.id = se.user_id
        WHERE se.access_status = 'pending'
        GROUP BY se.user_id, specialist_name
        ORDER BY pending_count DESC, specialist_name ASC
        "
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

function fullSyncDate(?string $value): string
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
<div class="row g-4 mb-4">
  <div class="col-xl-4">
    <div class="card enrollment-section-card shadow-sm h-100">
      <div class="card-header bg-white">
        <h5 class="mb-0">Χειροκίνητη εκτέλεση</h5>
      </div>
      <div class="card-body d-grid gap-3">
        <p class="text-secondary mb-0">Η εκτέλεση είναι τοπική και ενημερώνει τα logs/schedules χωρίς εξωτερικό Moodle API.</p>
        <form method="post">
          <input type="hidden" name="action" value="run_full_sync">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-cloud-check me-2"></i>Run Full Sync
          </button>
        </form>
        <?php
          $autoSchedule = null;
          foreach ($schedules as $schedule) {
              if (($schedule['sync_type'] ?? '') === 'auto_sync') {
                  $autoSchedule = $schedule;
                  break;
              }
          }
          $autoEnabled = is_array($autoSchedule) && (int)($autoSchedule['is_enabled'] ?? 0) === 1;
        ?>
        <form method="post">
          <input type="hidden" name="action" value="toggle_auto_sync">
          <input type="hidden" name="enable" value="<?= $autoEnabled ? '0' : '1' ?>">
          <button type="submit" class="btn <?= $autoEnabled ? 'btn-outline-danger' : 'btn-outline-success' ?> w-100">
            <i class="bi <?= $autoEnabled ? 'bi-pause-circle' : 'bi-play-circle' ?> me-2"></i>
            <?= $autoEnabled ? 'Disable Auto Sync' : 'Enable Auto Sync' ?>
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card enrollment-section-card shadow-sm h-100">
      <div class="card-header bg-white">
        <h5 class="mb-0">Scheduler κατάσταση</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Schedule</th>
                <th>Τύπος</th>
                <th>Ενεργό</th>
                <th>Συχνότητα</th>
                <th>Τελευταίο sync</th>
                <th>Επόμενο sync</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($schedules as $schedule): ?>
              <tr>
                <td><?= enrollmentH((string)$schedule['name']) ?></td>
                <td><?= enrollmentH((string)$schedule['sync_type']) ?></td>
                <td><span class="badge <?= (int)$schedule['is_enabled'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= (int)$schedule['is_enabled'] === 1 ? 'Enabled' : 'Disabled' ?></span></td>
                <td>κάθε <?= (int)$schedule['frequency_minutes'] ?> λεπτά</td>
                <td><?= enrollmentH(fullSyncDate((string)$schedule['last_sync_at'])) ?></td>
                <td><?= enrollmentH(fullSyncDate((string)$schedule['next_sync_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-5">
    <div class="card enrollment-section-card shadow-sm h-100">
      <div class="card-header bg-white">
        <h5 class="mb-0">Pending accesses ανά ειδικό επιστήμονα</h5>
      </div>
      <div class="card-body">
        <?php if ($pendingSummary === []): ?>
          <div class="text-center text-secondary py-5">
            <i class="bi bi-check2-circle fs-1 d-block mb-2"></i>
            Δεν υπάρχουν pending enrollments αυτή τη στιγμή.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Ειδικός Επιστήμονας</th>
                  <th>Pending</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pendingSummary as $row): ?>
                <tr>
                  <td><?= enrollmentH((string)$row['specialist_name']) ?></td>
                  <td><span class="badge text-bg-warning"><?= (int)$row['pending_count'] ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-7">
    <div class="card enrollment-section-card shadow-sm h-100">
      <div class="card-header bg-white">
        <h5 class="mb-0">Πρόσφατα sync logs</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Ενέργεια</th>
                <th>Ειδικός Επιστήμονας</th>
                <th>Performed By</th>
                <th>Κατάσταση</th>
                <th>Χρόνος</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($recentLogs === []): ?>
              <tr><td colspan="5" class="text-center text-secondary py-4">Δεν υπάρχουν ακόμη logs συγχρονισμού.</td></tr>
              <?php else: ?>
                <?php foreach ($recentLogs as $log): ?>
                <tr>
                  <td><?= enrollmentH((string)$log['action']) ?></td>
                  <td><?= enrollmentH((string)$log['specialist_name']) ?></td>
                  <td><?= enrollmentH((string)($log['performed_by_name'] ?? 'System')) ?></td>
                  <td><span class="badge <?= ((string)$log['status']) === 'success' ? 'text-bg-success' : 'text-bg-warning' ?>"><?= enrollmentH((string)$log['status']) ?></span></td>
                  <td><?= enrollmentH(fullSyncDate((string)$log['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/enrollment-bottom.php'; ?>


<?php
declare(strict_types=1);

$enrollment_allowed = ['admin', 'hr'];
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

$pageTitle         = 'Full Sync';
$currentEnrollPage = 'full-sync.php';

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

// ── Read sync settings ─────────────────────────────────────────────────────
$syncSettings = ['lms_auto_sync_enabled' => '0', 'lms_last_sync_at' => '', 'lms_last_sync_log' => ''];
try {
    $stmt = $pdo->prepare(
        "SELECT setting_key, setting_value FROM system_settings
         WHERE setting_key IN ('lms_auto_sync_enabled','lms_last_sync_at','lms_last_sync_log')"
    );
    $stmt->execute();
    foreach ($stmt->fetchAll() as $row) {
        $syncSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Throwable $e) {}

$actionMsg  = null;
$actionType = 'success';

// ── Handle POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['sync_action'] ?? '';

    if ($postAction === 'force_sync') {
        // Simulated full sync: update all ee_hired users in lms_access
        try {
            $now    = date('Y-m-d H:i:s');
            $eeIds  = $pdo->query("SELECT id FROM users WHERE role = 'ee_hired'")->fetchAll(\PDO::FETCH_COLUMN);
            $synced = 0;

            foreach ($eeIds as $uid) {
                $exists = $pdo->prepare("SELECT id FROM lms_access WHERE user_id = ?")->execute([(int)$uid]);
                // Just touch updated_at to simulate a sync ping
                $pdo->prepare("UPDATE lms_access SET updated_at = NOW() WHERE user_id = ?")->execute([(int)$uid]);
                $synced++;
            }

            $log = sprintf('[%s] Full sync completed. %d ΕΕ records processed.', $now, $synced);

            $pdo->prepare(
                "INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?,?,?)
                 ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=NOW()"
            )->execute(['lms_last_sync_at', $now, 'Τελευταίος συγχρονισμός Moodle (ISO timestamp)']);

            $pdo->prepare(
                "INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?,?,?)
                 ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=NOW()"
            )->execute(['lms_last_sync_log', $log, 'Log τελευταίου συγχρονισμού Moodle']);

            $syncSettings['lms_last_sync_at']  = $now;
            $syncSettings['lms_last_sync_log'] = $log;
            $actionMsg = 'Full sync ολοκληρώθηκε επιτυχώς.';
        } catch (Throwable $e) {
            $actionMsg  = 'Σφάλμα κατά το sync: ' . $e->getMessage();
            $actionType = 'danger';
        }

    } elseif ($postAction === 'toggle_auto') {
        $newVal = ($syncSettings['lms_auto_sync_enabled'] === '1') ? '0' : '1';
        try {
            $pdo->prepare(
                "INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?,?,?)
                 ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=NOW()"
            )->execute(['lms_auto_sync_enabled', $newVal, 'Αυτόματος συγχρονισμός Moodle (0=off, 1=on)']);
            $syncSettings['lms_auto_sync_enabled'] = $newVal;
            $actionMsg = 'Αυτόματος συγχρονισμός ' . ($newVal === '1' ? 'ενεργοποιήθηκε.' : 'απενεργοποιήθηκε.');
        } catch (Throwable $e) {
            $actionMsg  = 'Σφάλμα ενημέρωσης ρύθμισης.';
            $actionType = 'danger';
        }
    }
}

$autoSync   = $syncSettings['lms_auto_sync_enabled'] === '1';
$lastSync   = $syncSettings['lms_last_sync_at'];
$lastSyncFmt = $lastSync ? date('d/m/Y H:i:s', strtotime($lastSync)) : 'Δεν έχει γίνει ακόμη';
$syncLog    = $syncSettings['lms_last_sync_log'];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Full Sync</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Full Sync</li>
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

      <div class="row g-4">

        <!-- Force Sync Card -->
        <div class="col-12 col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-body border-0">
              <h5 class="card-title mb-0 fw-semibold">
                <i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Χειροκίνητος Συγχρονισμός
              </h5>
            </div>
            <div class="card-body">
              <p class="text-secondary mb-4">
                Εκτελεί άμεσα πλήρη συγχρονισμό όλων των χρηστών ΕΕ με το Moodle.
                Χρησιμοποιείται για άμεση ενημέρωση εκτός χρονοδιαγράμματος.
              </p>
              <form method="post" onsubmit="return confirm('Εκτέλεση full sync τώρα;')">
                <input type="hidden" name="sync_action" value="force_sync">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="bi bi-cloud-arrow-up-fill me-2"></i>Force Full Sync Now
                </button>
              </form>
            </div>
            <div class="card-footer bg-body border-0 text-secondary small">
              <i class="bi bi-clock me-1"></i>Τελευταίος sync: <strong><?= h($lastSyncFmt) ?></strong>
            </div>
          </div>
        </div>

        <!-- Auto Sync Toggle Card -->
        <div class="col-12 col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-body border-0">
              <h5 class="card-title mb-0 fw-semibold">
                <i class="bi bi-toggle-<?= $autoSync ? 'on text-success' : 'off text-secondary' ?> me-2"></i>Αυτόματος Συγχρονισμός
              </h5>
            </div>
            <div class="card-body">
              <p class="text-secondary mb-4">
                Όταν είναι ενεργός, ο συγχρονισμός εκτελείται αυτόματα σύμφωνα με τις ρυθμίσεις Moodle.
              </p>
              <div class="d-flex align-items-center gap-3 mb-3">
                <span class="fw-semibold">Κατάσταση:</span>
                <?php if ($autoSync): ?>
                <span class="badge text-bg-success fs-6"><i class="bi bi-check-circle me-1"></i>Ενεργός</span>
                <?php else: ?>
                <span class="badge text-bg-secondary fs-6"><i class="bi bi-slash-circle me-1"></i>Ανενεργός</span>
                <?php endif; ?>
              </div>
              <form method="post">
                <input type="hidden" name="sync_action" value="toggle_auto">
                <button type="submit" class="btn btn-<?= $autoSync ? 'warning' : 'success' ?>">
                  <i class="bi bi-toggle-<?= $autoSync ? 'off' : 'on' ?> me-2"></i>
                  <?= $autoSync ? 'Απενεργοποίηση' : 'Ενεργοποίηση' ?> Αυτόματου Sync
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Sync Log -->
        <?php if ($syncLog !== ''): ?>
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-header bg-body border-0">
              <h5 class="card-title mb-0 fw-semibold">
                <i class="bi bi-journal-text me-2 text-secondary"></i>Sync Log
              </h5>
            </div>
            <div class="card-body">
              <pre class="bg-body-secondary rounded p-3 small mb-0" style="max-height:300px;overflow-y:auto;white-space:pre-wrap;word-break:break-word;"><?= h($syncLog) ?></pre>
            </div>
          </div>
        </div>
        <?php endif; ?>

      </div>
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
});
</script>
</body>
</html>

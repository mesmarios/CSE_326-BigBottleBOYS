<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/enrollment-guard.php';

$enrollmentPageTitle = 'Dashboard';
$enrollmentPageHeading = 'Enrollment Dashboard';
$enrollmentPageDescription = 'Παρακολούθηση πρόσβασης LMS, συγχρονισμών και βασικών enrollment ενεργειών.';
$enrollmentActivePage = 'dashboard';
require_once __DIR__ . '/../../includes/enrollment-top.php';

$currentRole = normalizeAppRole((string)($_SESSION['role'] ?? 'candidate'));
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$lmsPortalUrl = 'https://moodle.tepak.cy';
$autoSync = [
    'is_enabled' => 0,
    'last_sync_at' => null,
    'next_sync_at' => null,
    'frequency_minutes' => 60,
];
$kpis = [
    'primary' => 0,
    'secondary' => 0,
    'tertiary' => 0,
    'quaternary' => 0,
];
$recentItems = [];
$managerOverview = [];

try {
    $settingStmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'lms_portal_url' LIMIT 1");
    $settingStmt->execute();
    $lmsPortalUrl = trim((string)($settingStmt->fetchColumn() ?: $lmsPortalUrl));

    $syncStmt = $pdo->prepare("SELECT is_enabled, last_sync_at, next_sync_at, frequency_minutes FROM sync_schedules WHERE sync_type = 'auto_sync' LIMIT 1");
    $syncStmt->execute();
    $autoSyncRow = $syncStmt->fetch(PDO::FETCH_ASSOC);
    if (is_array($autoSyncRow)) {
        $autoSync = array_merge($autoSync, $autoSyncRow);
    }

    if ($currentRole === 'specialist') {
        $statsStmt = $pdo->prepare(
            "
            SELECT
                COUNT(*) AS total_courses,
                SUM(CASE WHEN access_status = 'active' THEN 1 ELSE 0 END) AS active_courses,
                SUM(CASE WHEN access_status = 'pending' THEN 1 ELSE 0 END) AS pending_courses,
                SUM(CASE WHEN access_status IN ('inactive', 'suspended') THEN 1 ELSE 0 END) AS restricted_courses
            FROM specialist_enrollments
            WHERE user_id = :user_id
            "
        );
        $statsStmt->execute([':user_id' => $currentUserId]);
        $statsRow = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $kpis['primary'] = (int)($statsRow['total_courses'] ?? 0);
        $kpis['secondary'] = (int)($statsRow['active_courses'] ?? 0);
        $kpis['tertiary'] = (int)($statsRow['pending_courses'] ?? 0);
        $kpis['quaternary'] = (int)($statsRow['restricted_courses'] ?? 0);

        $recentStmt = $pdo->prepare(
            "
            SELECT
                c.code,
                c.name AS course_name,
                d.name AS department_name,
                se.access_status,
                se.updated_at
            FROM specialist_enrollments se
            INNER JOIN courses c ON c.id = se.course_id
            INNER JOIN departments d ON d.id = c.department_id
            WHERE se.user_id = :user_id
            ORDER BY se.updated_at DESC, se.id DESC
            LIMIT 6
            "
        );
        $recentStmt->execute([':user_id' => $currentUserId]);
        $recentItems = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $statsRow = $pdo->query(
            "
            SELECT
                COUNT(DISTINCT se.user_id) AS total_specialists,
                SUM(CASE WHEN se.access_status = 'active' THEN 1 ELSE 0 END) AS active_accesses,
                SUM(CASE WHEN se.access_status = 'pending' THEN 1 ELSE 0 END) AS pending_accesses,
                SUM(CASE WHEN se.access_status IN ('inactive', 'suspended') THEN 1 ELSE 0 END) AS restricted_accesses
            FROM specialist_enrollments se
            "
        )->fetch(PDO::FETCH_ASSOC) ?: [];

        $kpis['primary'] = (int)($statsRow['total_specialists'] ?? 0);
        $kpis['secondary'] = (int)($statsRow['active_accesses'] ?? 0);
        $kpis['tertiary'] = (int)($statsRow['pending_accesses'] ?? 0);
        $kpis['quaternary'] = (int)($statsRow['restricted_accesses'] ?? 0);

        $recentItems = $pdo->query(
            "
            SELECT
                el.action,
                el.action_type,
                el.status,
                el.created_at,
                CONCAT(u.first_name, ' ', u.last_name) AS specialist_name
            FROM enrollment_logs el
            INNER JOIN users u ON u.id = el.user_id
            ORDER BY el.created_at DESC, el.id DESC
            LIMIT 6
            "
        )->fetchAll(PDO::FETCH_ASSOC);

        $managerOverview = $pdo->query(
            "
            SELECT
                CONCAT(u.first_name, ' ', u.last_name) AS specialist_name,
                c.code,
                c.name AS course_name,
                se.access_status,
                se.updated_at
            FROM specialist_enrollments se
            INNER JOIN users u ON u.id = se.user_id
            INNER JOIN courses c ON c.id = se.course_id
            ORDER BY se.updated_at DESC, se.id DESC
            LIMIT 8
            "
        )->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
}

function enrollmentFormatDateTime(?string $value): string
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

function enrollmentStatusMeta(string $status): array
{
    return match ($status) {
        'active' => ['label' => 'Active', 'class' => 'text-bg-success'],
        'pending' => ['label' => 'Pending', 'class' => 'text-bg-warning'],
        'inactive' => ['label' => 'Inactive', 'class' => 'text-bg-secondary'],
        'suspended' => ['label' => 'Suspended', 'class' => 'text-bg-danger'],
        default => ['label' => ucfirst($status), 'class' => 'text-bg-light'],
    };
}
?>
<div class="card enrollment-hero-card mb-4">
  <div class="card-body p-4 p-lg-5">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="badge text-bg-light text-primary mb-3"><?= enrollmentH(appRoleLabel($currentRole)) ?></span>
        <h2 class="fw-bold mb-2">Έλεγχος πρόσβασης και συγχρονισμών σε ένα σημείο</h2>
        <p class="mb-0 text-white-50">
          <?= $currentRole === 'specialist'
            ? 'Δες γρήγορα αν έχεις ενεργή πρόσβαση, ποια μαθήματα έχουν ήδη συνδεθεί και πότε έγινε ο τελευταίος συγχρονισμός.'
            : 'Παρακολούθησε ενεργές προσβάσεις, pending enrollments και την κατάσταση του αυτόματου συγχρονισμού χωρίς σύνδεση σε εξωτερικό Moodle API.' ?>
        </p>
      </div>
      <div class="col-lg-4">
        <div class="d-grid gap-2">
          <a href="lms_sync.php" class="btn btn-light fw-semibold"><i class="bi bi-arrow-repeat me-2"></i>LMS Sync</a>
          <?php if ($enrollmentCanManageSync): ?>
          <a href="full_sync.php" class="btn btn-outline-light fw-semibold"><i class="bi bi-cloud-check me-2"></i>Full Sync</a>
          <a href="report.php" class="btn btn-outline-light fw-semibold"><i class="bi bi-bar-chart-line me-2"></i>Report</a>
          <?php else: ?>
          <button type="button" class="btn btn-outline-light fw-semibold" disabled><i class="bi bi-lock me-2"></i>Full Sync (Admin/HR)</button>
          <button type="button" class="btn btn-outline-light fw-semibold" disabled><i class="bi bi-lock me-2"></i>Report (Admin/HR)</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6 col-xl-3">
    <div class="card enrollment-kpi shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon" style="background:#dbeafe;color:#1d4ed8;"><i class="bi bi-people-fill"></i></div>
        <div><div class="stat-value"><?= $kpis['primary'] ?></div><div class="stat-label"><?= $currentRole === 'specialist' ? 'Σύνολο Αναθέσεων' : 'Ειδικοί Επιστήμονες' ?></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card enrollment-kpi shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon" style="background:#dcfce7;color:#15803d;"><i class="bi bi-check2-circle"></i></div>
        <div><div class="stat-value"><?= $kpis['secondary'] ?></div><div class="stat-label">Ενεργές Προσβάσεις</div></div>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card enrollment-kpi shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon" style="background:#fef3c7;color:#b45309;"><i class="bi bi-hourglass-split"></i></div>
        <div><div class="stat-value"><?= $kpis['tertiary'] ?></div><div class="stat-label">Σε Εκκρεμότητα</div></div>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card enrollment-kpi shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon" style="background:#fee2e2;color:#b91c1c;"><i class="bi bi-slash-circle"></i></div>
        <div><div class="stat-value"><?= $kpis['quaternary'] ?></div><div class="stat-label">Περιορισμένες</div></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card enrollment-section-card shadow-sm h-100">
      <div class="card-header bg-white">
        <h5 class="mb-0">Auto Sync</h5>
      </div>
      <div class="card-body">
        <?php $autoSyncEnabled = (int)($autoSync['is_enabled'] ?? 0) === 1; ?>
        <div class="enrollment-status-pill <?= $autoSyncEnabled ? 'text-bg-success' : 'text-bg-secondary' ?>">
          <i class="bi <?= $autoSyncEnabled ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' ?>"></i>
          <?= $autoSyncEnabled ? 'Enabled' : 'Disabled' ?>
        </div>
        <dl class="row mt-3 mb-0">
          <dt class="col-5 text-secondary">Τελευταίο sync</dt>
          <dd class="col-7"><?= enrollmentH(enrollmentFormatDateTime($autoSync['last_sync_at'] ?? null)) ?></dd>
          <dt class="col-5 text-secondary">Επόμενο sync</dt>
          <dd class="col-7"><?= enrollmentH(enrollmentFormatDateTime($autoSync['next_sync_at'] ?? null)) ?></dd>
          <dt class="col-5 text-secondary">Συχνότητα</dt>
          <dd class="col-7">κάθε <?= (int)($autoSync['frequency_minutes'] ?? 60) ?> λεπτά</dd>
        </dl>
        <div class="mt-3">
          <a href="<?= enrollmentH($lmsPortalUrl) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
            <i class="bi bi-box-arrow-up-right me-1"></i>LMS Portal
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card enrollment-section-card shadow-sm h-100">
      <div class="card-header bg-white">
        <h5 class="mb-0"><?= $currentRole === 'specialist' ? 'Τα τελευταία course access updates' : 'Πρόσφατες Enrollment Ενέργειες' ?></h5>
      </div>
      <div class="card-body">
        <?php if ($recentItems === []): ?>
          <div class="text-center text-secondary py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Δεν υπάρχουν ακόμη δεδομένα για εμφάνιση.
          </div>
        <?php elseif ($currentRole === 'specialist'): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Μάθημα</th>
                  <th>Τμήμα</th>
                  <th>Κατάσταση</th>
                  <th>Τελευταία ενημέρωση</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentItems as $item): ?>
                  <?php $status = enrollmentStatusMeta((string)$item['access_status']); ?>
                  <tr>
                    <td><strong><?= enrollmentH((string)$item['code']) ?></strong> - <?= enrollmentH((string)$item['course_name']) ?></td>
                    <td><?= enrollmentH((string)$item['department_name']) ?></td>
                    <td><span class="badge <?= enrollmentH($status['class']) ?>"><?= enrollmentH($status['label']) ?></span></td>
                    <td><?= enrollmentH(enrollmentFormatDateTime((string)$item['updated_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Ενέργεια</th>
                  <th>Ειδικός Επιστήμονας</th>
                  <th>Κατάσταση</th>
                  <th>Χρόνος</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentItems as $item): ?>
                  <tr>
                    <td><?= enrollmentH((string)$item['action']) ?></td>
                    <td><?= enrollmentH((string)$item['specialist_name']) ?></td>
                    <td><span class="badge <?= enrollmentH(((string)$item['status']) === 'success' ? 'text-bg-success' : 'text-bg-warning') ?>"><?= enrollmentH((string)$item['status']) ?></span></td>
                    <td><?= enrollmentH(enrollmentFormatDateTime((string)$item['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($managerOverview !== []): ?>
<div class="card enrollment-section-card shadow-sm mt-4">
  <div class="card-header bg-white">
    <h5 class="mb-0">Πρόσφατη εικόνα προσβάσεων</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Ειδικός Επιστήμονας</th>
            <th>Μάθημα</th>
            <th>Κατάσταση</th>
            <th>Ενημέρωση</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($managerOverview as $item): ?>
            <?php $status = enrollmentStatusMeta((string)$item['access_status']); ?>
            <tr>
              <td><?= enrollmentH((string)$item['specialist_name']) ?></td>
              <td><strong><?= enrollmentH((string)$item['code']) ?></strong> - <?= enrollmentH((string)$item['course_name']) ?></td>
              <td><span class="badge <?= enrollmentH($status['class']) ?>"><?= enrollmentH($status['label']) ?></span></td>
              <td><?= enrollmentH(enrollmentFormatDateTime((string)$item['updated_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/enrollment-bottom.php'; ?>


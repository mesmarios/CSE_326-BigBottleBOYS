<?php
require_once __DIR__ . '/../../includes/role-access.php';
include('../../includes/layout.php');
include('../../includes/header.php');
include('../../includes/nav.php');

$dashboardUser = [];
$dashboardStats = [
  'total' => 0,
  'drafts' => 0,
  'underReview' => 0,
  'upcomingDeadlines' => 0,
];
$dashboardSubmissions = [];
$dashboardRoleLabel = appRoleLabel($_SESSION['role'] ?? 'candidate');
if (!empty($_SESSION['user_id']) && isset($pdo)) {
  $candidateId = (int)$_SESSION['user_id'];

  try {
    $userStmt = $pdo->prepare('SELECT first_name, last_name, email FROM users WHERE id = :id LIMIT 1');
    $userStmt->execute([':id' => $candidateId]);
    $userRow = $userStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $dashboardUser = [
      'name' => $userRow['first_name'] ?? '',
      'surname' => $userRow['last_name'] ?? '',
      'email' => $userRow['email'] ?? '',
    ];

    $statsStmt = $pdo->prepare(
      "SELECT
         COUNT(*) AS total,
         SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS drafts,
         SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) AS under_review
       FROM candidate_applications
       WHERE candidate_id = :candidate_id"
    );
    $statsStmt->execute([':candidate_id' => $candidateId]);
    $statsRow = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $dashboardStats['total'] = (int)($statsRow['total'] ?? 0);
    $dashboardStats['drafts'] = (int)($statsRow['drafts'] ?? 0);
    $dashboardStats['underReview'] = (int)($statsRow['under_review'] ?? 0);

    $submissionsStmt = $pdo->prepare(
      "SELECT
         ca.announcement_id,
         ca.status,
         ca.submitted_at,
         ca.updated_at,
         ja.title
       FROM candidate_applications ca
       INNER JOIN job_announcements ja ON ja.id = ca.announcement_id
       WHERE ca.candidate_id = :candidate_id
       ORDER BY COALESCE(ca.submitted_at, ca.updated_at, ca.created_at) DESC"
    );
    $submissionsStmt->execute([':candidate_id' => $candidateId]);
    foreach ($submissionsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $statusMap = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'accepted' => 'Approved',
        'rejected' => 'Rejected',
        'withdrawn' => 'Rejected',
      ];

      $dashboardSubmissions[] = [
        'callId' => (string)$row['announcement_id'],
        'title' => $row['title'],
        'status' => $statusMap[$row['status']] ?? 'Submitted',
        'submittedDate' => $row['submitted_at'] ? date('c', strtotime($row['submitted_at'])) : null,
        'updatedDate' => $row['updated_at'] ? date('c', strtotime($row['updated_at'])) : null,
      ];
    }

    $callsStmt = $pdo->prepare(
      "SELECT
         ja.id,
         ja.title,
         COALESCE(d.name, '—') AS department,
         rp.end_date
       FROM job_announcements ja
       INNER JOIN recruitment_periods rp ON rp.id = ja.period_id
       LEFT JOIN departments d ON d.id = ja.department_id
       WHERE ja.status = 'published'
         AND rp.status = 'active'
         AND rp.start_date <= CURRENT_DATE()
         AND rp.end_date >= CURRENT_DATE()
         AND NOT EXISTS (
           SELECT 1
           FROM candidate_applications ca
           WHERE ca.announcement_id = ja.id
             AND ca.candidate_id = ?
             AND ca.status <> 'draft'
         )
       ORDER BY rp.end_date ASC, ja.id DESC"
    );
    $callsStmt->execute([$candidateId]);

    $today = new DateTimeImmutable('today');
    foreach ($callsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      if (!empty($row['end_date'])) {
        $deadline = new DateTimeImmutable($row['end_date']);
        $diffDays = (int)$today->diff($deadline)->format('%r%a');
        if ($diffDays >= 0 && $diffDays <= 7) {
          $dashboardStats['upcomingDeadlines']++;
        }
      }
    }
  } catch (Throwable $e) {
    $dashboardUser = [];
    $dashboardStats = [
      'total' => 0,
      'drafts' => 0,
      'underReview' => 0,
      'upcomingDeadlines' => 0,
    ];
    $dashboardSubmissions = [];
  }
}
?>
<link rel="stylesheet" href="../../recruitment/assets/css/index.css">
<link rel="stylesheet" href="../../assets/css/user-ui.css">

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Home</h3></div>
              <div class="col-sm-6">
                </ol>
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <div class="container-fluid">

            <!-- ── Welcome Banner ──────────────────────────────────── -->
            <div class="dash-welcome mb-4">
              <div class="welcome-role-badge">
                <i class="bi bi-person-badge-fill"></i>
                <?= htmlspecialchars($dashboardRoleLabel, ENT_QUOTES, 'UTF-8') ?>
              </div>
              <h2 class="mb-1">Welcome back, <span id="dashWelcomeName"><?= htmlspecialchars($dashboardRoleLabel, ENT_QUOTES, 'UTF-8') ?></span>!</h2>
              <p>Here's a quick overview of your recruitment activity. Track your applications,
                 upcoming deadlines, and open positions — all in one place.</p>
            </div>

            <!-- ── Summary Cards ───────────────────────────────────── -->
            <div class="row g-3 mb-4">

              <!-- Total Applications -->
              <div class="col-sm-6 col-xl-3">
                <div class="card stat-card">
                  <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                      <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div>
                      <div class="stat-value" id="statTotal">0</div>
                      <div class="stat-label">Total Applications</div>
                    </div>
                  </div>
                  <div class="stat-footer d-flex align-items-center gap-1">
                    <i class="bi bi-info-circle text-primary"></i>
                    All submissions including drafts
                  </div>
                </div>
              </div>

              <!-- Draft -->
              <div class="col-sm-6 col-xl-3">
                <div class="card stat-card">
                  <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                      <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                      <div class="stat-value" id="statDraft">0</div>
                      <div class="stat-label">Drafts</div>
                    </div>
                  </div>
                  <div class="stat-footer d-flex align-items-center gap-1">
                    <i class="bi bi-clock text-secondary"></i>
                    Saved but not submitted
                  </div>
                </div>
              </div>

              <!-- Under Review -->
              <div class="col-sm-6 col-xl-3">
                <div class="card stat-card">
                  <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                      <i class="bi bi-eye-fill"></i>
                    </div>
                    <div>
                      <div class="stat-value" id="statReview">0</div>
                      <div class="stat-label">Under Review</div>
                    </div>
                  </div>
                  <div class="stat-footer d-flex align-items-center gap-1">
                    <i class="bi bi-hourglass-split text-warning"></i>
                    Awaiting committee decision
                  </div>
                </div>
              </div>

              <!-- Upcoming Deadlines -->
              <div class="col-sm-6 col-xl-3">
                <div class="card stat-card">
                  <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                      <i class="bi bi-calendar-event-fill"></i>
                    </div>
                    <div>
                      <div class="stat-value" id="statDeadlines">0</div>
                      <div class="stat-label">Upcoming Deadlines</div>
                    </div>
                  </div>
                  <div class="stat-footer d-flex align-items-center gap-1">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                    Due within 7 days
                  </div>
                </div>
              </div>
            </div><!-- /row summary cards -->

            <!-- ── Quick Actions + Recent Activity ────────────────── -->
            <div class="row g-3 mb-4">

              <!-- Quick Actions -->
              <div class="col-lg-5">
                <div class="card quick-action-card h-100">
                  <div class="card-header bg-white border-bottom d-flex align-items-center gap-2"
                       style="border-radius:.85rem .85rem 0 0 !important; padding:.85rem 1.25rem;">
                    
                    <span class="section-heading" style="border:none; padding:0;">Quick Actions</span>
                  </div>
                  <div class="card-body d-flex flex-column gap-2 p-3">
                    <a href="./myapplication.php" class="quick-action-btn">
                      <div class="qa-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-plus-circle-fill"></i>
                      </div>
                      <div>
                        <div class="qa-title">Apply for a New Position</div>
                        <div class="qa-sub">Browse and submit to open calls</div>
                      </div>
                      <i class="bi bi-chevron-right ms-auto text-muted" style="font-size:.75rem;"></i>
                    </a>
                    <a href="./myapplication.php#drafts" class="quick-action-btn" id="continueDraftBtn">
                      <div class="qa-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-pencil-fill"></i>
                      </div>
                      <div>
                        <div class="qa-title">Continue a Draft Application</div>
                        <div class="qa-sub">Pick up where you left off</div>
                      </div>
                      <i class="bi bi-chevron-right ms-auto text-muted" style="font-size:.75rem;"></i>
                    </a>
                    <a href="./applicationstatus.php" class="quick-action-btn">
                      <div class="qa-icon bg-secondary bg-opacity-10 text-warning">
                          <i class="bi bi-bar-chart-steps text-warning"></i>
                      </div>
                      <div>
                        <div class="qa-title">View Application Status</div>
                        <div class="qa-sub">Track progress of your submissions</div>
                      </div>
                      <i class="bi bi-chevron-right ms-auto text-muted" style="font-size:.75rem;"></i>
                    </a>
                    <a href="./myprofile.php" class="quick-action-btn">
                      <div class="qa-icon bg-secondary bg-opacity-10 text-success">
                        <i class="bi bi-person-fill-gear"></i>
                      </div>
                      <div>
                        <div class="qa-title">Update Profile</div>
                        <div class="qa-sub">Keep your information current</div>
                      </div>
                      <i class="bi bi-chevron-right ms-auto text-muted" style="font-size:.75rem;"></i>
                    </a>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              <div class="col-lg-7">
                <div class="card info-card h-100">
                  <div class="card-header d-flex align-items-center justify-content-between">
                    <h6><i class="bi bi-activity me-2 text-primary"></i>Recent Activity</h6>
                  </div>
                  <div class="card-body p-3">
                    <div id="activityList">
                      <!-- populated by JS -->
                    </div>
                    <p id="activityEmpty" class="text-center text-muted py-3 mb-0 d-none" style="font-size:.84rem;">
                      No recent activity to show.
                    </p>
                  </div>
                </div>
              </div>

            </div><!-- /row quick actions + activity -->

          </div><!-- /container-fluid -->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

<script>
  window.RECRUITMENT_INDEX_BOOTSTRAP = {
    user: <?= json_encode($dashboardUser, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    stats: <?= json_encode($dashboardStats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    submissions: <?= json_encode($dashboardSubmissions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    roleLabel: <?= json_encode($dashboardRoleLabel, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
  };
</script>
<script src="../../recruitment/assets/js/index.js"></script>

<?php include('../../includes/footer.php'); ?>

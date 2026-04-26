<?php
declare(strict_types=1);

$enrollment_allowed = ['admin', 'hr', 'ee_hired'];
require_once __DIR__ . '/includes/enrollment-guard.php';
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/admin-branding.php';

$brandingContext = adminGetBrandingContext($pdo);
$adminBrandText  = $brandingContext['brand_text'];
$adminLogo       = $brandingContext['logo'];
$adminFavicon    = $brandingContext['favicon'];

$enrollFullName  = trim((string)(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')));
if ($enrollFullName === '') { $enrollFullName = 'Χρήστης'; }
$enrollAvatarSrc = enrollResolveAvatarSrc($pdo, (int)($_SESSION['user_id'] ?? 0));

$pageTitle           = 'Dashboard';
$currentEnrollPage   = 'dashboard.php';

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12 text-center"><h3 class="mb-0"><i class="bi bi-mortarboard-fill me-2"></i>Enrollment Dashboard</h3></div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">

      <div class="row justify-content-center mb-4">
        <div class="col-12 col-lg-10">
          <div class="config-card bg-body shadow-sm mb-2">
            <div class="config-card-body py-3 px-4 text-center">
              <div class="fw-semibold text-dark mb-1" style="font-size:1.04rem;">
                Καλώς ήρθες, <?= h($enrollFullName) ?>.
              </div>
              <p class="text-secondary mb-0">
                Ενότητα διαχείρισης εγγραφών, συγχρονισμού LMS και παρακολούθησης πρόσβασης Moodle για Ειδικούς Επιστήμονες.
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4 justify-content-center">

        <!-- LMS Sync -->
        <div class="col-12 col-sm-6 col-xl-4">
          <a href="lms-sync.php" class="admin-nav-card admin-nav-card-recruitment">
            <div class="card h-100 text-center shadow-sm">
              <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
              <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                <div class="admin-nav-icon-wrap">
                  <i class="bi bi-arrow-repeat"></i>
                </div>
                <h5 class="card-title fw-bold mb-1">LMS Sync</h5>
                <p class="card-text text-secondary small mb-0">
                  <?= $_enrollment_role === 'ee_hired'
                      ? 'Προβολή πρόσβασης Moodle και σύνδεσμος εισόδου'
                      : 'Διαχείριση πρόσβασης Moodle για ΕΕ — ενεργοποίηση, απενεργοποίηση, αλλαγή μαθήματος' ?>
                </p>
              </div>
            </div>
          </a>
        </div>

        <?php if ($_enrollment_role !== 'ee_hired'): ?>
        <!-- Full Sync -->
        <div class="col-12 col-sm-6 col-xl-4">
          <a href="full-sync.php" class="admin-nav-card admin-nav-card-config">
            <div class="card h-100 text-center shadow-sm">
              <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
              <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                <div class="admin-nav-icon-wrap">
                  <i class="bi bi-cloud-arrow-up-fill"></i>
                </div>
                <h5 class="card-title fw-bold mb-1">Full Sync</h5>
                <p class="card-text text-secondary small mb-0">
                  Πλήρης συγχρονισμός χρηστών με το Moodle — χειροκίνητος ή αυτόματος
                </p>
              </div>
            </div>
          </a>
        </div>

        <!-- Report -->
        <div class="col-12 col-sm-6 col-xl-4">
          <a href="report.php" class="admin-nav-card admin-nav-card-report">
            <div class="card h-100 text-center shadow-sm">
              <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
              <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                <div class="admin-nav-icon-wrap">
                  <i class="bi bi-file-earmark-bar-graph-fill"></i>
                </div>
                <h5 class="card-title fw-bold mb-1">Report</h5>
                <p class="card-text text-secondary small mb-0">
                  Στατιστικά πρόσβασης, κατάσταση εγγραφών και αναφορές Moodle
                </p>
              </div>
            </div>
          </a>
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
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js" defer></script>
<script src="../assets/js/changes.js" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const sw = document.querySelector('.sidebar-wrapper');
    if (sw && OverlayScrollbarsGlobal?.OverlayScrollbars) {
      OverlayScrollbarsGlobal.OverlayScrollbars(sw, {
        scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
      });
    }
  });
</script>
</body>
</html>

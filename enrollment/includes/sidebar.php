<?php
// Variables expected from the including page:
//   $adminLogo          string — logo path
//   $adminBrandText     string — brand name
//   $currentEnrollPage  string — basename of current page (e.g. 'dashboard.php')
//   $_enrollment_role   string — role from guard

$_ep = $currentEnrollPage ?? basename($_SERVER['PHP_SELF']);
$_er = $_enrollment_role ?? (string)($_SESSION['role'] ?? '');

function _enav(string $page, string $current): string
{
    return $page === $current ? 'nav-link active' : 'nav-link';
}
?>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <div class="sidebar-brand">
    <a href="dashboard.php" class="brand-link">
      <img src="<?= htmlspecialchars($adminLogo ?? '../assets/images/AdminLTELogo.png', ENT_QUOTES, 'UTF-8') ?>"
           alt="Logo" class="brand-image opacity-75 shadow" />
      <span class="brand-text fw-light"><?= htmlspecialchars($adminBrandText ?? 'CareerTrack', ENT_QUOTES, 'UTF-8') ?></span>
    </a>
  </div>
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Enrollment navigation" data-accordion="false">
        <li class="nav-header">ENROLLMENT MODULE</li>
        <li class="nav-item">
          <a href="dashboard.php" class="<?= _enav('dashboard.php', $_ep) ?>">
            <i class="nav-icon bi bi-speedometer2"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <li class="nav-header">ΕΝΕΡΓΕΙΕΣ</li>
        <li class="nav-item">
          <a href="lms-sync.php" class="<?= _enav('lms-sync.php', $_ep) ?>">
            <i class="nav-icon bi bi-arrow-repeat"></i>
            <p>LMS Sync</p>
          </a>
        </li>
        <?php if ($_er !== 'ee_hired'): ?>
        <li class="nav-item">
          <a href="full-sync.php" class="<?= _enav('full-sync.php', $_ep) ?>">
            <i class="nav-icon bi bi-cloud-arrow-up-fill"></i>
            <p>Full Sync</p>
          </a>
        </li>
        <li class="nav-header">ΑΝΑΦΟΡΕΣ</li>
        <li class="nav-item">
          <a href="report.php" class="<?= _enav('report.php', $_ep) ?>">
            <i class="nav-icon bi bi-bar-chart-fill"></i>
            <p>Report</p>
          </a>
        </li>
        <?php endif; ?>
        <?php if (in_array($_er, ['admin', 'hr'], true)): ?>
        <li class="nav-header">ΑΛΛΑΓΗ ΕΝΟΤΗΤΑΣ</li>
        <li class="nav-item">
          <a href="../module-select.php" class="nav-link">
            <i class="nav-icon bi bi-grid-3x3-gap-fill"></i>
            <p>Switch Module</p>
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</aside>

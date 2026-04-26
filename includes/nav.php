<?php
// Sidebar navigation include — outputs only the <aside> sidebar.
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <div class="sidebar-brand">
    <a href="./index.php" class="brand-link">
      <img src="../../recruitment/assets/images/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow"/>
      <span class="brand-text fw-light">CareerTrack</span>
    </a>
  </div>
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <ul class="nav sidebar-menu flex-column" role="navigation" aria-label="Main navigation" id="navigation">
        <li class="nav-item menu-open">
          <?php // parent stays open always ?>
          <ul class="nav nav-treeview" style="display:block;">
            <li class="nav-item">
              <a href="./index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <i class="nav-icon bi bi-house-fill"></i>
                <p>Home</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./myprofile.php" class="nav-link <?php echo $currentPage === 'myprofile.php' ? 'active' : ''; ?>">
                <i class="nav-icon bi bi-circle"></i>
                <p>My Profile</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./myapplication.php" class="nav-link <?php echo $currentPage === 'myapplication.php' ? 'active' : ''; ?>">
                <i class="nav-icon bi bi-circle"></i>
                <p>My Application</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="./applicationstatus.php" class="nav-link <?php echo $currentPage === 'applicationstatus.php' ? 'active' : ''; ?>">
                <i class="nav-icon bi bi-circle"></i>
                <p>Application Status</p>
              </a>
            </li>
            <?php if (($_SESSION['role'] ?? '') === 'hr'): ?>
            <li class="nav-item" style="margin-top:.5rem;border-top:1px solid rgba(255,255,255,.1);padding-top:.5rem;">
              <a href="../../module-select.php" class="nav-link">
                <i class="nav-icon bi bi-grid-3x3-gap-fill"></i>
                <p>Switch Module</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
    </nav>
  </div>
</aside>
<!--end::Sidebar-->


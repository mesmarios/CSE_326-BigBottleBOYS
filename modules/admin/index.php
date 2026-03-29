<?php require_once __DIR__ . '/../../includes/admin-guard.php'; ?>
<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" media="print" onload="this.media='all'" />
    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <!-- AdminLTE -->
    <link rel="stylesheet" href="../../assets/css/adminlte.css" />
    <!-- Custom styles -->
    <link rel="stylesheet" href="../../assets/css/card-nav.css" />
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
              <a class="nav-link" href="#" role="button" onclick="toggleSidebar(event)" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="index.php" class="nav-link fw-semibold">
                <i class="bi bi-house me-1"></i>Dashboard
              </a>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <!-- Notifications -->
            <li class="nav-item dropdown">
              <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-label="Notifications">
                <i class="bi bi-bell-fill"></i>
                <span class="navbar-badge badge text-bg-warning">3</span>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <span class="dropdown-item dropdown-header">3 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="manage_users.php" class="dropdown-item">
                  <i class="bi bi-people me-2 text-primary"></i>2 νέοι χρήστες
                  <span class="float-end text-secondary fs-7">1 ώρα</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="manage_recruitment.php" class="dropdown-item">
                  <i class="bi bi-clipboard-check me-2 text-success"></i>5 νέες αιτήσεις
                  <span class="float-end text-secondary fs-7">3 ώρες</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="report.php" class="dropdown-item dropdown-footer">Δες όλες</a>
              </div>
            </li>
            <!-- Fullscreen -->
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen" aria-label="Fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display:none"></i>
              </a>
            </li>
            <!-- User Menu -->
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img src="../../assets/images/avatar.png" class="user-image rounded-circle shadow" alt="Admin" />
                <span class="d-none d-md-inline">Administrator</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="../../assets/images/AdminLTELogo.png" class="rounded-circle shadow" alt="Admin" />
                  <p>
                    Administrator
                    <small>Διαχειριστής Συστήματος</small>
                  </p>
                </li>
                <li class="user-footer">
                  <a href="my_profile.php" class="btn btn-default btn-flat">
                    <i class="bi bi-person me-1"></i>Προφίλ
                  </a>
                  <a href="../../logout.php" class="btn btn-default btn-flat float-end">
                    <i class="bi bi-box-arrow-right me-1"></i>Αποσύνδεση
                  </a>
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
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false">
              <li class="nav-header">ΚΥΡΙΟ ΜΕΝΟΥ</li>
              <li class="nav-item">
                <a href="index.php" class="nav-link active">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <li class="nav-header">ΔΙΑΧΕΙΡΙΣΗ</li>
              <li class="nav-item">
                <a href="manage_users.php" class="nav-link">
                  <i class="nav-icon bi bi-people"></i>
                  <p>Manage Users</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="manage_recruitment.php" class="nav-link">
                  <i class="nav-icon bi bi-clipboard-check"></i>
                  <p>
                    Manage Recruitment
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="manage_recruitment.php#applications" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i><p>Αιτήσεις</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="manage_recruitment.php#schools" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i><p>Σχολές</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="manage_recruitment.php#departments" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i><p>Τμήματα</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="manage_recruitment.php#courses" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i><p>Μαθήματα</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="manage_recruitment.php#period" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i><p>Περίοδος Αιτήσεων</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="configure_system.php" class="nav-link">
                  <i class="nav-icon bi bi-gear"></i>
                  <p>Configure System</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="report.php" class="nav-link">
                  <i class="nav-icon bi bi-bar-chart"></i>
                  <p>Reports</p>
                </a>
              </li>
              <li class="nav-header">ΛΟΓΑΡΙΑΣΜΟΣ</li>
              <li class="nav-item">
                <a href="my_profile.php" class="nav-link">
                  <i class="nav-icon bi bi-person-circle"></i>
                  <p>My Profile</p>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </aside>

      <!-- ===== MAIN ===== -->
      <main class="app-main">
        <div id="cardNavMount"></div>

        <!-- Page Title -->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row justify-content-center mt-2">
              <div class="col-12 col-md-10 col-lg-8">
                <div class="text-center py-1">
                  <h2 class="dashboard-hero-title mb-0">
                    <span class="dashboard-hero-pill">
                      <span class="dashboard-word-wrap">
                        <span id="dashWordA" class="dashboard-word">Admin</span>
                      </span>
                      <span class="dashboard-word-wrap">
                        <span id="dashWordB" class="dashboard-word">Dashboard</span>
                      </span>
                    </span>
                  </h2>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Page Content -->
        <div class="app-content">
          <div class="container-fluid">

            <!-- Welcome message -->
            <div class="row justify-content-center mb-4">
              <div class="col-12 col-lg-10">
                <p class="text-center text-secondary mb-0">
                  Καλώς ήρθατε στον πίνακα διαχείρισης. Επιλέξτε μια ενότητα για να ξεκινήσετε.
                </p>
              </div>
            </div>

            <!-- 4 Navigation Icon Cards -->
            <div class="row g-4 justify-content-center">

              <!-- Manage Users -->
              <div class="col-12 col-sm-6 col-xl-3">
                <a href="manage_users.php" class="admin-nav-card admin-nav-card-users">
                  <div class="card h-100 text-center shadow-sm">
                    <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                      <div class="admin-nav-icon-wrap">
                        <i class="bi bi-people-fill"></i>
                      </div>
                      <h5 class="card-title fw-bold mb-1">Manage Users</h5>
                      <p class="card-text text-secondary small mb-0">
                        Προβολή, προσθήκη, επεξεργασία και ανάθεση ρόλων σε χρήστες
                      </p>
                    </div>
                  </div>
                </a>
              </div>

              <!-- Manage Recruitment -->
              <div class="col-12 col-sm-6 col-xl-3">
                <a href="manage_recruitment.php" class="admin-nav-card admin-nav-card-recruitment">
                  <div class="card h-100 text-center shadow-sm">
                    <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                      <div class="admin-nav-icon-wrap">
                        <i class="bi bi-clipboard-check-fill"></i>
                      </div>
                      <h5 class="card-title fw-bold mb-1">Manage Recruitment</h5>
                      <p class="card-text text-secondary small mb-0">
                        Αιτήσεις, σχολές, τμήματα, μαθήματα και περίοδοι
                      </p>
                    </div>
                  </div>
                </a>
              </div>

              <!-- Configure System -->
              <div class="col-12 col-sm-6 col-xl-3">
                <a href="configure_system.php" class="admin-nav-card admin-nav-card-config">
                  <div class="card h-100 text-center shadow-sm">
                    <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                      <div class="admin-nav-icon-wrap">
                        <i class="bi bi-gear-fill"></i>
                      </div>
                      <h5 class="card-title fw-bold mb-1">Configure System</h5>
                      <p class="card-text text-secondary small mb-0">
                        Θέμα, λογότυπα, σύνδεση με Moodle και λοιπές ρυθμίσεις
                      </p>
                    </div>
                  </div>
                </a>
              </div>

              <!-- Reports -->
              <div class="col-12 col-sm-6 col-xl-3">
                <a href="report.php" class="admin-nav-card admin-nav-card-report">
                  <div class="card h-100 text-center shadow-sm">
                    <div class="nav-card-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                      <div class="admin-nav-icon-wrap">
                        <i class="bi bi-bar-chart-fill"></i>
                      </div>
                      <h5 class="card-title fw-bold mb-1">Reports</h5>
                      <p class="card-text text-secondary small mb-0">
                        Στατιστικά αιτήσεων, γραφήματα και αναλυτικά δεδομένα
                      </p>
                    </div>
                  </div>
                </a>
              </div>

            </div>
            <!-- end row -->

            <!-- Quick Stats Row -->
            <div class="row g-3 mt-2 justify-content-center">
              <div class="col-12 col-lg-10">
                <div class="row g-3">
                  <div class="col-6 col-md-3">
                    <div class="stat-card bg-body shadow-sm">
                      <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrap" style="background:#dbeafe;color:#1d4ed8;">
                          <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                          <div class="stat-value">124</div>
                          <div class="stat-label">Σύνολο Χρηστών</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="stat-card bg-body shadow-sm">
                      <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrap" style="background:#dcfce7;color:#15803d;">
                          <i class="bi bi-clipboard-check-fill"></i>
                        </div>
                        <div>
                          <div class="stat-value">47</div>
                          <div class="stat-label">Ενεργές Αιτήσεις</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="stat-card bg-body shadow-sm">
                      <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrap" style="background:#fef3c7;color:#b45309;">
                          <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div>
                          <div class="stat-value">12</div>
                          <div class="stat-label">Τμήματα</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="stat-card bg-body shadow-sm">
                      <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrap" style="background:#ede9fe;color:#6d28d9;">
                          <i class="bi bi-book-fill"></i>
                        </div>
                        <div>
                          <div class="stat-value">38</div>
                          <div class="stat-label">Μαθήματα</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- end quick stats -->

          </div>
        </div>
      </main>

      <!-- ===== FOOTER ===== -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">BigBottleBOYS &copy; 2026</div>
        <strong>Copyright &copy; 2026 <a href="#" class="text-decoration-none">TheBigBottleBoys</a>.</strong>
        All rights reserved.
      </footer>

    </div>
    <!-- end app-wrapper -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/js/adminlte.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js" defer></script>
    <script src="../../assets/js/changes.js" defer></script>
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
          });
        }
      });
    </script>
  </body>
</html>

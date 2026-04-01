<?php require_once __DIR__ . '/../../includes/admin-guard.php'; ?>
<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin | Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../assets/css/adminlte.css" />
    <link rel="stylesheet" href="../../assets/css/admin-pages.css" />
    <link rel="stylesheet" href="../../assets/css/admin-ui.css" />
    <!-- ApexCharts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" crossorigin="anonymous" />
  </head>
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">

      <!-- ===== NAVBAR ===== -->
      <header class="app-header">
        <nav class="navbar navbar-expand bg-body h-100" aria-label="Primary">
          <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="toggleSidebar(event)"><i class="bi bi-list"></i></a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="index.php" class="nav-link"><i class="bi bi-house me-1"></i>Dashboard</a>
            </li>
            <li class="nav-item d-none d-md-block">
              <span class="nav-link text-secondary"><i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>Reports</span>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <!-- Export buttons -->
            <li class="nav-item d-none d-md-flex align-items-center me-1">
              <button class="btn btn-outline-secondary btn-sm me-1" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Εκτύπωση
              </button>
              <button class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Export
              </button>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display:none"></i>
              </a>
            </li>
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img src="../../assets/images/avatar.png" class="user-image rounded-circle shadow" alt="Admin" />
                <span class="d-none d-md-inline">Administrator</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="../../assets/images/AdminLTELogo.png" class="rounded-circle shadow" alt="Admin" />
                  <p>Administrator<small>Διαχειριστής Συστήματος</small></p>
                </li>
                <li class="user-footer">
                  <a href="my_profile.php" class="btn btn-default btn-flat"><i class="bi bi-person me-1"></i>Προφίλ</a>
                  <a href="../../logout.php" class="btn btn-default btn-flat float-end"><i class="bi bi-box-arrow-right me-1"></i>Αποσύνδεση</a>
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
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
              <li class="nav-header">ΚΥΡΙΟ ΜΕΝΟΥ</li>
              <li class="nav-item"><a href="index.php" class="nav-link"><i class="nav-icon bi bi-speedometer2"></i><p>Dashboard</p></a></li>
              <li class="nav-header">ΔΙΑΧΕΙΡΙΣΗ</li>
              <li class="nav-item"><a href="manage_users.php" class="nav-link"><i class="nav-icon bi bi-people"></i><p>Manage Users</p></a></li>
              <li class="nav-item">
                <a href="manage_recruitment.php" class="nav-link">
                  <i class="nav-icon bi bi-clipboard-check"></i>
                  <p>Manage Recruitment<i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item"><a href="manage_recruitment.php#applications" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Αιτήσεις</p></a></li>
                  <li class="nav-item"><a href="manage_recruitment.php#schools" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Σχολές</p></a></li>
                  <li class="nav-item"><a href="manage_recruitment.php#departments" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Τμήματα</p></a></li>
                  <li class="nav-item"><a href="manage_recruitment.php#courses" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Μαθήματα</p></a></li>
                  <li class="nav-item"><a href="manage_recruitment.php#period" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Περίοδος Αιτήσεων</p></a></li>
                </ul>
              </li>
              <li class="nav-item"><a href="configure_system.php" class="nav-link"><i class="nav-icon bi bi-gear"></i><p>Configure System</p></a></li>
              <li class="nav-item"><a href="report.php" class="nav-link active"><i class="nav-icon bi bi-bar-chart"></i><p>Reports</p></a></li>
              <li class="nav-header">ΛΟΓΑΡΙΑΣΜΟΣ</li>
              <li class="nav-item"><a href="my_profile.php" class="nav-link"><i class="nav-icon bi bi-person-circle"></i><p>My Profile</p></a></li>
            </ul>
          </nav>
        </div>
      </aside>

      <!-- ===== MAIN ===== -->
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row align-items-center py-2">
              <div class="col">
                <h4 class="mb-0 fw-bold d-flex align-items-center gap-2">
                  <span class="admin-page-title-icon" style="background:#ede9fe;color:#6d28d9;">
                    <i class="bi bi-bar-chart-fill"></i>
                  </span>
                  Reports & Στατιστικά
                </h4>
              </div>
              <div class="col-auto d-flex align-items-center gap-2">
                <!-- Period filter -->
                <select class="form-select form-select-sm" style="width:auto;" id="periodFilter" onchange="refreshCharts()">
                  <option value="current" selected>Εαρινό 2026</option>
                  <option value="prev">Χειμερινό 2025</option>
                  <option value="all">Όλες οι περίοδοι</option>
                </select>
                <ol class="breadcrumb mb-0 d-none d-md-flex">
                  <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                  <li class="breadcrumb-item active">Reports</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <!-- KPI Stats Row -->
            <div class="row g-3 mb-4">
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="stat-icon-wrap mb-2" style="background:#dbeafe;color:#1d4ed8;"><i class="bi bi-file-earmark-text-fill"></i></div>
                  <div class="stat-value" id="kpiTotal">47</div>
                  <div class="stat-label">Σύνολο Αιτήσεων</div>
                  <div class="mt-1"><span class="badge bg-success bg-opacity-10 text-success" style="font-size:.75rem;">+12% ↑</span></div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="stat-icon-wrap mb-2" style="background:#dcfce7;color:#15803d;"><i class="bi bi-check-circle-fill"></i></div>
                  <div class="stat-value" id="kpiApproved">23</div>
                  <div class="stat-label">Εγκεκριμένες</div>
                  <div class="mt-1"><span class="badge bg-success bg-opacity-10 text-success" style="font-size:.75rem;">48.9%</span></div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="stat-icon-wrap mb-2" style="background:#fef3c7;color:#b45309;"><i class="bi bi-hourglass-split"></i></div>
                  <div class="stat-value" id="kpiPending">18</div>
                  <div class="stat-label">Υπό Αξιολόγηση</div>
                  <div class="mt-1"><span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.75rem;">38.3%</span></div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="stat-icon-wrap mb-2" style="background:#fee2e2;color:#b91c1c;"><i class="bi bi-x-circle-fill"></i></div>
                  <div class="stat-value" id="kpiRejected">6</div>
                  <div class="stat-label">Απορριφθείσες</div>
                  <div class="mt-1"><span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:.75rem;">12.8%</span></div>
                </div>
              </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="row g-4 mb-4">
              <!-- Applications Timeline -->
              <div class="col-12 col-lg-8">
                <div class="chart-card bg-body shadow-sm">
                  <div class="chart-card-header">
                    <div>
                      <span class="fw-semibold">Εξέλιξη Αιτήσεων ανά Μήνα</span>
                      <small class="text-secondary d-block">Αριθμός αιτήσεων κατά τη διάρκεια της περιόδου</small>
                    </div>
                    <div class="d-flex gap-2">
                      <span class="badge bg-primary bg-opacity-10 text-primary">Εγκεκριμένες</span>
                      <span class="badge bg-warning bg-opacity-10 text-warning">Υπό Αξιολόγηση</span>
                      <span class="badge bg-danger bg-opacity-10 text-danger">Απορριφθείσες</span>
                    </div>
                  </div>
                  <div class="chart-card-body">
                    <div id="chart-timeline"></div>
                  </div>
                </div>
              </div>

              <!-- Status Distribution Donut -->
              <div class="col-12 col-lg-4">
                <div class="chart-card bg-body shadow-sm">
                  <div class="chart-card-header">
                    <div>
                      <span class="fw-semibold">Κατανομή Αιτήσεων</span>
                      <small class="text-secondary d-block">Ανά κατάσταση</small>
                    </div>
                  </div>
                  <div class="chart-card-body">
                    <div id="chart-donut"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row g-4 mb-4">
              <!-- By Department -->
              <div class="col-12 col-lg-6">
                <div class="chart-card bg-body shadow-sm">
                  <div class="chart-card-header">
                    <span class="fw-semibold">Αιτήσεις ανά Τμήμα</span>
                  </div>
                  <div class="chart-card-body">
                    <div id="chart-by-dept"></div>
                  </div>
                </div>
              </div>

              <!-- By Course -->
              <div class="col-12 col-lg-6">
                <div class="chart-card bg-body shadow-sm">
                  <div class="chart-card-header">
                    <span class="fw-semibold">Αιτήσεις ανά Μάθημα</span>
                  </div>
                  <div class="chart-card-body">
                    <div id="chart-by-course"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Summary Table -->
            <div class="admin-table-card bg-body shadow-sm">
              <div class="admin-table-toolbar">
                <span class="fw-semibold">Συγκεντρωτικός Πίνακας Αιτήσεων</span>
                <button class="btn btn-outline-success btn-sm">
                  <i class="bi bi-file-earmark-excel me-1"></i>Εξαγωγή σε Excel
                </button>
              </div>
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Τμήμα</th>
                      <th>Σχολή</th>
                      <th class="text-center">Σύνολο</th>
                      <th class="text-center">Εγκεκριμένες</th>
                      <th class="text-center">Υπό Αξιολόγηση</th>
                      <th class="text-center">Απορριφθείσες</th>
                      <th>Ποσοστό Έγκρισης</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="fw-semibold">Τμήμα Μαθηματικών</td>
                      <td class="text-secondary small">Θετικών Επιστημών</td>
                      <td class="text-center">15</td>
                      <td class="text-center text-success fw-semibold">9</td>
                      <td class="text-center text-warning fw-semibold">4</td>
                      <td class="text-center text-danger fw-semibold">2</td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="progress flex-grow-1" style="height:6px;">
                            <div class="progress-bar bg-success" style="width:60%"></div>
                          </div>
                          <small class="fw-semibold">60%</small>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Τμήμα Φυσικής</td>
                      <td class="text-secondary small">Θετικών Επιστημών</td>
                      <td class="text-center">12</td>
                      <td class="text-center text-success fw-semibold">7</td>
                      <td class="text-center text-warning fw-semibold">4</td>
                      <td class="text-center text-danger fw-semibold">1</td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="progress flex-grow-1" style="height:6px;">
                            <div class="progress-bar bg-success" style="width:58%"></div>
                          </div>
                          <small class="fw-semibold">58%</small>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">Τμήμα Πληροφορικής</td>
                      <td class="text-secondary small">Σχολή Πληροφορικής</td>
                      <td class="text-center">20</td>
                      <td class="text-center text-success fw-semibold">7</td>
                      <td class="text-center text-warning fw-semibold">10</td>
                      <td class="text-center text-danger fw-semibold">3</td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="progress flex-grow-1" style="height:6px;">
                            <div class="progress-bar bg-success" style="width:35%"></div>
                          </div>
                          <small class="fw-semibold">35%</small>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="table-light fw-bold">
                    <tr>
                      <td>Σύνολο</td>
                      <td></td>
                      <td class="text-center">47</td>
                      <td class="text-center text-success">23</td>
                      <td class="text-center text-warning">18</td>
                      <td class="text-center text-danger">6</td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="progress flex-grow-1" style="height:6px;">
                            <div class="progress-bar bg-success" style="width:49%"></div>
                          </div>
                          <small>49%</small>
                        </div>
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

          </div>
        </div>
      </main>

      <!-- ===== FOOTER ===== -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">BigBottleBOYS &copy; 2026</div>
        <strong>Copyright &copy; 2026 <a href="#" class="text-decoration-none">TheBigBottleBoys</a>.</strong> All rights reserved.
      </footer>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/js/adminlte.js" defer></script>
    <script src="../../assets/js/changes.js" defer></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const sw = document.querySelector('.sidebar-wrapper');
        if (sw && OverlayScrollbarsGlobal?.OverlayScrollbars) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sw, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
          });
        }

        renderCharts();
      });

      var timelineChart, donutChart, deptChart, courseChart;

      function renderCharts() {
        // ---- Timeline / Area Chart ----
        timelineChart = new ApexCharts(document.querySelector('#chart-timeline'), {
          series: [
            { name: 'Εγκεκριμένες', data: [3, 5, 6, 4, 3, 2] },
            { name: 'Υπό Αξιολόγηση', data: [2, 3, 4, 5, 3, 1] },
            { name: 'Απορριφθείσες', data: [0, 1, 2, 1, 1, 1] }
          ],
          chart: { type: 'area', height: 260, toolbar: { show: false } },
          colors: ['#22c55e', '#f59e0b', '#ef4444'],
          stroke: { curve: 'smooth', width: 2 },
          fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
          dataLabels: { enabled: false },
          legend: { position: 'bottom', offsetY: 5 },
          xaxis: {
            categories: ['Φεβ', 'Μάρ', 'Απρ', 'Μάι', 'Ιούν', 'Ιούλ'],
            axisBorder: { show: false }, axisTicks: { show: false }
          },
          grid: { strokeDashArray: 4, borderColor: 'rgba(0,0,0,0.06)' },
          tooltip: { shared: true, intersect: false }
        });
        timelineChart.render();

        // ---- Donut Chart ----
        donutChart = new ApexCharts(document.querySelector('#chart-donut'), {
          series: [23, 18, 6],
          labels: ['Εγκεκριμένες', 'Υπό Αξιολόγηση', 'Απορριφθείσες'],
          chart: { type: 'donut', height: 260 },
          colors: ['#22c55e', '#f59e0b', '#ef4444'],
          legend: { position: 'bottom' },
          dataLabels: { formatter: function (val) { return Math.round(val) + '%'; } },
          plotOptions: { pie: { donut: { size: '70%' } } },
          tooltip: { y: { formatter: function (v) { return v + ' αιτήσεις'; } } }
        });
        donutChart.render();

        // ---- By Department ----
        deptChart = new ApexCharts(document.querySelector('#chart-by-dept'), {
          series: [{ name: 'Αιτήσεις', data: [15, 12, 20, 8, 6, 4] }],
          chart: { type: 'bar', height: 260, toolbar: { show: false } },
          colors: ['#6d28d9'],
          plotOptions: { bar: { borderRadius: 6, horizontal: true } },
          dataLabels: { enabled: false },
          xaxis: {
            categories: ['Μαθηματικών', 'Φυσικής', 'Πληροφορικής', 'Χημείας', 'Βιολογίας', 'Ιστορίας'],
            axisBorder: { show: false }
          },
          grid: { strokeDashArray: 4, borderColor: 'rgba(0,0,0,0.06)' },
          tooltip: { y: { formatter: function (v) { return v + ' αιτήσεις'; } } }
        });
        deptChart.render();

        // ---- By Course ----
        courseChart = new ApexCharts(document.querySelector('#chart-by-course'), {
          series: [{ name: 'Αιτήσεις', data: [8, 7, 6, 5, 5, 4, 4, 3] }],
          chart: { type: 'bar', height: 260, toolbar: { show: false } },
          colors: ['#0d6efd'],
          plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
          dataLabels: { enabled: false },
          xaxis: {
            categories: ['Ανάλυση Ι', 'Μηχανική', 'Προγ/σμός', 'Στατιστική', 'Ηλεκτρο.', 'Αλγόριθ.', 'Γλωσσολ.', 'Ιστορία'],
            axisBorder: { show: false }, axisTicks: { show: false }
          },
          grid: { strokeDashArray: 4, borderColor: 'rgba(0,0,0,0.06)' },
          tooltip: { y: { formatter: function (v) { return v + ' αιτήσεις'; } } }
        });
        courseChart.render();
      }

      function refreshCharts() {
        // In a real app, this would fetch data per period from the server.
        // For now, just re-render with slight variation as demonstration.
        if (timelineChart) {
          var rnd = function (base) { return base + Math.floor(Math.random() * 4 - 2); };
          timelineChart.updateSeries([
            { name: 'Εγκεκριμένες', data: [3,5,6,4,3,2].map(rnd) },
            { name: 'Υπό Αξιολόγηση', data: [2,3,4,5,3,1].map(rnd) },
            { name: 'Απορριφθείσες', data: [0,1,2,1,1,1].map(rnd) }
          ]);
        }
      }
    </script>
  </body>
</html>

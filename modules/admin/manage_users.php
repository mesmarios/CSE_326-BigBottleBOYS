<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin | Manage Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../assets/css/adminlte.css" />
    <link rel="stylesheet" href="../../assets/css/admin-pages.css" />
    <link rel="stylesheet" href="../../assets/css/admin-ui.css" />
  </head>
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">

      <!-- ===== NAVBAR ===== -->
      <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="toggleSidebar(event)" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="index.php" class="nav-link">
                <i class="bi bi-house me-1"></i>Dashboard
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <span class="nav-link text-secondary">
                <i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>Manage Users
              </span>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen" aria-label="Fullscreen">
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
              <li class="nav-item">
                <a href="index.php" class="nav-link"><i class="nav-icon bi bi-speedometer2"></i><p>Dashboard</p></a>
              </li>
              <li class="nav-header">ΔΙΑΧΕΙΡΙΣΗ</li>
              <li class="nav-item">
                <a href="manage_users.php" class="nav-link active"><i class="nav-icon bi bi-people"></i><p>Manage Users</p></a>
              </li>
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
              <li class="nav-item"><a href="report.php" class="nav-link"><i class="nav-icon bi bi-bar-chart"></i><p>Reports</p></a></li>
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
                  <span class="admin-page-title-icon" style="background:#dbeafe;color:#1d4ed8;">
                    <i class="bi bi-people-fill"></i>
                  </span>
                  Manage Users
                </h4>
              </div>
              <div class="col-auto">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                  <li class="breadcrumb-item active">Manage Users</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <!-- Stats Row -->
            <div class="row g-3 mb-4">
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap" style="background:#dbeafe;color:#1d4ed8;"><i class="bi bi-people-fill"></i></div>
                    <div><div class="stat-value">124</div><div class="stat-label">Σύνολο Χρηστών</div></div>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap" style="background:#fee2e2;color:#b91c1c;"><i class="bi bi-shield-fill"></i></div>
                    <div><div class="stat-value">3</div><div class="stat-label">Διαχειριστές</div></div>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap" style="background:#fef3c7;color:#b45309;"><i class="bi bi-person-badge-fill"></i></div>
                    <div><div class="stat-value">18</div><div class="stat-label">Αξιολογητές</div></div>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="stat-card bg-body shadow-sm">
                  <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap" style="background:#dcfce7;color:#15803d;"><i class="bi bi-person-fill"></i></div>
                    <div><div class="stat-value">103</div><div class="stat-label">Αιτούντες</div></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Users Table -->
            <div class="admin-table-card bg-body shadow-sm">
              <div class="admin-table-toolbar">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <div class="admin-table-search">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control form-control-sm" placeholder="Αναζήτηση χρήστη..." id="userSearch" />
                  </div>
                  <select class="form-select form-select-sm" style="width:auto;" id="roleFilter">
                    <option value="">Όλοι οι ρόλοι</option>
                    <option value="admin">Admin</option>
                    <option value="evaluator">Αξιολογητής</option>
                    <option value="applicant">Αιτών</option>
                  </select>
                </div>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openAddUserModal()">
                  <i class="bi bi-plus-lg me-1"></i>Προσθήκη Χρήστη
                </button>
              </div>

              <div class="table-responsive">
                <table class="table table-hover mb-0" id="usersTable">
                  <thead class="table-light">
                    <tr>
                      <th style="width:46px;"></th>
                      <th>Ονοματεπώνυμο</th>
                      <th>Email</th>
                      <th>Ρόλος</th>
                      <th>Κατάσταση</th>
                      <th>Ημ/νία Εγγραφής</th>
                      <th class="text-end">Ενέργειες</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><div class="table-avatar-placeholder" style="background:#dbeafe;color:#1d4ed8;">ΑΓ</div></td>
                      <td class="fw-semibold">Ανδρέας Γεωργίου</td>
                      <td class="text-secondary">a.georgiou@uni.gr</td>
                      <td><span class="badge badge-role-admin rounded-pill px-3 py-1">Admin</span></td>
                      <td><span class="badge bg-success rounded-pill px-3 py-1">Ενεργός</span></td>
                      <td class="text-secondary small">01/01/2026</td>
                      <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditUserModal(1)" title="Επεξεργασία"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteUser(1,'Ανδρέας Γεωργίου')" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                      </td>
                    </tr>
                    <tr>
                      <td><div class="table-avatar-placeholder" style="background:#dcfce7;color:#15803d;">ΜΠ</div></td>
                      <td class="fw-semibold">Μαρία Παπαδοπούλου</td>
                      <td class="text-secondary">m.papadopoulou@uni.gr</td>
                      <td><span class="badge badge-role-evaluator rounded-pill px-3 py-1">Αξιολογητής</span></td>
                      <td><span class="badge bg-success rounded-pill px-3 py-1">Ενεργή</span></td>
                      <td class="text-secondary small">15/02/2026</td>
                      <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditUserModal(2)" title="Επεξεργασία"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteUser(2,'Μαρία Παπαδοπούλου')" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                      </td>
                    </tr>
                    <tr>
                      <td><div class="table-avatar-placeholder" style="background:#fef3c7;color:#b45309;">ΝΚ</div></td>
                      <td class="fw-semibold">Νίκος Κωνσταντίνου</td>
                      <td class="text-secondary">n.konstantinou@email.gr</td>
                      <td><span class="badge badge-role-applicant rounded-pill px-3 py-1">Αιτών</span></td>
                      <td><span class="badge bg-success rounded-pill px-3 py-1">Ενεργός</span></td>
                      <td class="text-secondary small">20/02/2026</td>
                      <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditUserModal(3)" title="Επεξεργασία"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteUser(3,'Νίκος Κωνσταντίνου')" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                      </td>
                    </tr>
                    <tr>
                      <td><div class="table-avatar-placeholder" style="background:#ede9fe;color:#6d28d9;">ΕΔ</div></td>
                      <td class="fw-semibold">Ελένη Δημητρίου</td>
                      <td class="text-secondary">e.dimitriou@email.gr</td>
                      <td><span class="badge badge-role-applicant rounded-pill px-3 py-1">Αιτούσα</span></td>
                      <td><span class="badge bg-secondary rounded-pill px-3 py-1">Ανενεργή</span></td>
                      <td class="text-secondary small">05/01/2026</td>
                      <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditUserModal(4)" title="Επεξεργασία"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteUser(4,'Ελένη Δημητρίου')" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                      </td>
                    </tr>
                    <tr>
                      <td><div class="table-avatar-placeholder" style="background:#fee2e2;color:#b91c1c;">ΓΑ</div></td>
                      <td class="fw-semibold">Γιώργος Αντωνίου</td>
                      <td class="text-secondary">g.antoniou@uni.gr</td>
                      <td><span class="badge badge-role-evaluator rounded-pill px-3 py-1">Αξιολογητής</span></td>
                      <td><span class="badge bg-success rounded-pill px-3 py-1">Ενεργός</span></td>
                      <td class="text-secondary small">10/01/2026</td>
                      <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditUserModal(5)" title="Επεξεργασία"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteUser(5,'Γιώργος Αντωνίου')" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top">
                <small class="text-secondary">Εμφάνιση 1–5 από 124 χρήστες</small>
                <nav>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                  </ul>
                </nav>
              </div>
            </div>
            <!-- end table card -->

          </div>
        </div>
      </main>

      <!-- ===== FOOTER ===== -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">BigBottleBOYS &copy; 2026</div>
        <strong>Copyright &copy; 2026 <a href="#" class="text-decoration-none">TheBigBottleBoys</a>.</strong> All rights reserved.
      </footer>

    </div>
    <!-- end app-wrapper -->

    <!-- ===== ADD / EDIT USER MODAL ===== -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="userModalLabel">Προσθήκη Χρήστη</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="userForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Όνομα <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="userFirstName" placeholder="π.χ. Ανδρέας" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Επώνυμο <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="userLastName" placeholder="π.χ. Γεωργίου" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="userEmail" placeholder="email@example.gr" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Τηλέφωνο</label>
                  <input type="tel" class="form-control" id="userPhone" placeholder="π.χ. 2101234567" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Ρόλος <span class="text-danger">*</span></label>
                  <select class="form-select" id="userRole" required>
                    <option value="">Επιλέξτε ρόλο...</option>
                    <option value="admin">Admin</option>
                    <option value="evaluator">Αξιολογητής</option>
                    <option value="applicant">Αιτών</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Κατάσταση</label>
                  <select class="form-select" id="userStatus">
                    <option value="active">Ενεργός</option>
                    <option value="inactive">Ανενεργός</option>
                  </select>
                </div>
                <div class="col-md-6" id="passwordField">
                  <label class="form-label fw-semibold">Κωδικός <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="userPassword" placeholder="Τουλάχιστον 8 χαρακτήρες" />
                </div>
                <div class="col-md-6" id="confirmPasswordField">
                  <label class="form-label fw-semibold">Επιβεβαίωση Κωδικού</label>
                  <input type="password" class="form-control" id="userPasswordConfirm" placeholder="Επαναλάβετε τον κωδικό" />
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
            <button type="button" class="btn btn-primary" id="saveUserBtn">
              <i class="bi bi-check-lg me-1"></i>Αποθήκευση
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== DELETE CONFIRM MODAL ===== -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Διαγραφή</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            Είστε βέβαιοι ότι θέλετε να διαγράψετε τον χρήστη <strong id="deleteUserName"></strong>;
            <br><small class="text-secondary">Η ενέργεια αυτή δεν μπορεί να αναιρεθεί.</small>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Ακύρωση</button>
            <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">
              <i class="bi bi-trash me-1"></i>Διαγραφή
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/js/adminlte.js" defer></script>
    <script src="../../assets/js/changes.js" defer></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // OverlayScrollbars
        const sw = document.querySelector('.sidebar-wrapper');
        if (sw && OverlayScrollbarsGlobal?.OverlayScrollbars) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sw, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
          });
        }

        // Live search
        document.getElementById('userSearch').addEventListener('input', function () {
          const q = this.value.toLowerCase();
          document.querySelectorAll('#usersTable tbody tr').forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
          });
        });

        // Role filter
        document.getElementById('roleFilter').addEventListener('change', function () {
          const val = this.value.toLowerCase();
          document.querySelectorAll('#usersTable tbody tr').forEach(function (row) {
            row.style.display = (!val || row.textContent.toLowerCase().includes(val)) ? '' : 'none';
          });
        });
      });

      function openAddUserModal() {
        document.getElementById('userModalLabel').textContent = 'Προσθήκη Χρήστη';
        document.getElementById('userForm').reset();
        document.getElementById('passwordField').style.display = '';
        document.getElementById('confirmPasswordField').style.display = '';
      }

      function openEditUserModal(id) {
        document.getElementById('userModalLabel').textContent = 'Επεξεργασία Χρήστη #' + id;
        document.getElementById('passwordField').style.display = 'none';
        document.getElementById('confirmPasswordField').style.display = 'none';
        var modal = new bootstrap.Modal(document.getElementById('userModal'));
        modal.show();
      }

      function confirmDeleteUser(id, name) {
        document.getElementById('deleteUserName').textContent = name;
        var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
      }

      document.getElementById('saveUserBtn')?.addEventListener('click', function () {
        // Placeholder: show success toast / submit form
        var modal = bootstrap.Modal.getInstance(document.getElementById('userModal'));
        if (modal) modal.hide();
        // TODO: connect to backend
      });
    </script>
  </body>
</html>

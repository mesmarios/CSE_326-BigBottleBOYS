<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin | Manage Recruitment</title>
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
              <a class="nav-link" href="#" onclick="toggleSidebar(event)"><i class="bi bi-list"></i></a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="index.php" class="nav-link"><i class="bi bi-house me-1"></i>Dashboard</a>
            </li>
            <li class="nav-item d-none d-md-block">
              <span class="nav-link text-secondary"><i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>Manage Recruitment</span>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
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
              <li class="nav-item menu-open">
                <a href="manage_recruitment.php" class="nav-link active">
                  <i class="nav-icon bi bi-clipboard-check"></i>
                  <p>Manage Recruitment<i class="nav-arrow bi bi-chevron-right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item"><a href="#applications" class="nav-link" onclick="switchTab('applications')"><i class="nav-icon bi bi-circle"></i><p>Αιτήσεις</p></a></li>
                  <li class="nav-item"><a href="#schools" class="nav-link" onclick="switchTab('schools')"><i class="nav-icon bi bi-circle"></i><p>Σχολές</p></a></li>
                  <li class="nav-item"><a href="#departments" class="nav-link" onclick="switchTab('departments')"><i class="nav-icon bi bi-circle"></i><p>Τμήματα</p></a></li>
                  <li class="nav-item"><a href="#courses" class="nav-link" onclick="switchTab('courses')"><i class="nav-icon bi bi-circle"></i><p>Μαθήματα</p></a></li>
                  <li class="nav-item"><a href="#period" class="nav-link" onclick="switchTab('period')"><i class="nav-icon bi bi-circle"></i><p>Περίοδος Αιτήσεων</p></a></li>
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
                  <span class="admin-page-title-icon" style="background:#dcfce7;color:#15803d;">
                    <i class="bi bi-clipboard-check-fill"></i>
                  </span>
                  Manage Recruitment
                </h4>
              </div>
              <div class="col-auto">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                  <li class="breadcrumb-item active">Manage Recruitment</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <!-- Tabs Navigation -->
            <ul class="nav admin-tabs" id="recruitTabs" role="tablist">
              <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#applications" id="tab-applications">
                  <i class="bi bi-file-earmark-text"></i>Αιτήσεις
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#schools" id="tab-schools">
                  <i class="bi bi-building"></i>Σχολές
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#departments" id="tab-departments">
                  <i class="bi bi-diagram-3"></i>Τμήματα
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#courses" id="tab-courses">
                  <i class="bi bi-book"></i>Μαθήματα
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#period" id="tab-period">
                  <i class="bi bi-calendar-range"></i>Περίοδος Αιτήσεων
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#evaluators" id="tab-evaluators">
                  <i class="bi bi-person-check"></i>Αξιολογητές
                </button>
              </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">

              <!-- ===== TAB: APPLICATIONS ===== -->
              <div class="tab-pane fade show active" id="applications" role="tabpanel">
                <div class="admin-table-card bg-body shadow-sm">
                  <div class="admin-table-toolbar">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <div class="admin-table-search">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control form-control-sm" placeholder="Αναζήτηση αίτησης..." />
                      </div>
                      <select class="form-select form-select-sm" style="width:auto;">
                        <option value="">Όλες οι καταστάσεις</option>
                        <option>Ανοιχτή</option>
                        <option>Υπό Αξιολόγηση</option>
                        <option>Κλειστή</option>
                      </select>
                    </div>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#appModal">
                      <i class="bi bi-plus-lg me-1"></i>Νέα Αίτηση
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>#</th>
                          <th>Τίτλος Αίτησης</th>
                          <th>Σχολή</th>
                          <th>Τμήμα</th>
                          <th>Αξιολογητής</th>
                          <th>Κατάσταση</th>
                          <th class="text-end">Ενέργειες</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="text-secondary small">001</td>
                          <td class="fw-semibold">Καθηγητής Μαθηματικών Α' Τάξης</td>
                          <td>Σχολή Θετικών Επιστημών</td>
                          <td>Τμήμα Μαθηματικών</td>
                          <td>Μ. Παπαδοπούλου</td>
                          <td><span class="badge bg-success rounded-pill px-3">Ανοιχτή</span></td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" title="Επεξεργασία"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-secondary me-1" title="Ανάθεση"><i class="bi bi-person-plus"></i></button>
                            <button class="btn btn-sm btn-outline-danger" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                        <tr>
                          <td class="text-secondary small">002</td>
                          <td class="fw-semibold">Εκπαιδευτικός Φυσικής Β' Γυμνασίου</td>
                          <td>Σχολή Φυσικής</td>
                          <td>Τμήμα Φυσικής</td>
                          <td>Γ. Αντωνίου</td>
                          <td><span class="badge bg-warning text-dark rounded-pill px-3">Υπό Αξιολόγηση</span></td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" title="Επεξεργασία"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-secondary me-1" title="Ανάθεση"><i class="bi bi-person-plus"></i></button>
                            <button class="btn btn-sm btn-outline-danger" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                        <tr>
                          <td class="text-secondary small">003</td>
                          <td class="fw-semibold">Καθηγητής Πληροφορικής Γ' Λυκείου</td>
                          <td>Σχολή Πληροφορικής</td>
                          <td>Τμήμα Πληροφορικής</td>
                          <td>—</td>
                          <td><span class="badge bg-secondary rounded-pill px-3">Κλειστή</span></td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" title="Επεξεργασία"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-secondary me-1" title="Ανάθεση"><i class="bi bi-person-plus"></i></button>
                            <button class="btn btn-sm btn-outline-danger" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: SCHOOLS ===== -->
              <div class="tab-pane fade" id="schools" role="tabpanel">
                <div class="admin-table-card bg-body shadow-sm">
                  <div class="admin-table-toolbar">
                    <div class="admin-table-search">
                      <i class="bi bi-search"></i>
                      <input type="text" class="form-control form-control-sm" placeholder="Αναζήτηση σχολής..." />
                    </div>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#schoolModal">
                      <i class="bi bi-plus-lg me-1"></i>Νέα Σχολή
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>#</th><th>Όνομα Σχολής</th><th>Κωδικός</th><th>Τμήματα</th><th class="text-end">Ενέργειες</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>1</td><td class="fw-semibold">Σχολή Θετικών Επιστημών</td><td><code>ΘΕ</code></td><td>3</td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                        <tr>
                          <td>2</td><td class="fw-semibold">Σχολή Πληροφορικής</td><td><code>ΠΛ</code></td><td>2</td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                        <tr>
                          <td>3</td><td class="fw-semibold">Σχολή Ανθρωπιστικών Σπουδών</td><td><code>ΑΝΘ</code></td><td>4</td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: DEPARTMENTS ===== -->
              <div class="tab-pane fade" id="departments" role="tabpanel">
                <div class="admin-table-card bg-body shadow-sm">
                  <div class="admin-table-toolbar">
                    <div class="admin-table-search">
                      <i class="bi bi-search"></i>
                      <input type="text" class="form-control form-control-sm" placeholder="Αναζήτηση τμήματος..." />
                    </div>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#deptModal">
                      <i class="bi bi-plus-lg me-1"></i>Νέο Τμήμα
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr><th>#</th><th>Τμήμα</th><th>Σχολή</th><th>Μαθήματα</th><th class="text-end">Ενέργειες</th></tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>1</td><td class="fw-semibold">Τμήμα Μαθηματικών</td><td>Θετικών Επιστημών</td><td>8</td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                        <tr>
                          <td>2</td><td class="fw-semibold">Τμήμα Φυσικής</td><td>Θετικών Επιστημών</td><td>6</td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                        <tr>
                          <td>3</td><td class="fw-semibold">Τμήμα Πληροφορικής</td><td>Σχολή Πληροφορικής</td><td>10</td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: COURSES ===== -->
              <div class="tab-pane fade" id="courses" role="tabpanel">
                <div class="admin-table-card bg-body shadow-sm">
                  <div class="admin-table-toolbar">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <div class="admin-table-search">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control form-control-sm" placeholder="Αναζήτηση μαθήματος..." />
                      </div>
                      <select class="form-select form-select-sm" style="width:auto;">
                        <option value="">Όλα τα τμήματα</option>
                        <option>Μαθηματικών</option>
                        <option>Φυσικής</option>
                        <option>Πληροφορικής</option>
                      </select>
                    </div>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#courseModal">
                      <i class="bi bi-plus-lg me-1"></i>Νέο Μάθημα
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr><th>Κωδικός</th><th>Μάθημα</th><th>Τμήμα</th><th>Εξάμηνο</th><th>ECTS</th><th class="text-end">Ενέργειες</th></tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td><code>MAT101</code></td><td class="fw-semibold">Ανάλυση Ι</td><td>Μαθηματικών</td><td>1ο</td><td>6</td>
                          <td class="text-end"><button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        <tr>
                          <td><code>PHY101</code></td><td class="fw-semibold">Μηχανική</td><td>Φυσικής</td><td>1ο</td><td>7</td>
                          <td class="text-end"><button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        <tr>
                          <td><code>CS101</code></td><td class="fw-semibold">Εισαγωγή στον Προγραμματισμό</td><td>Πληροφορικής</td><td>1ο</td><td>6</td>
                          <td class="text-end"><button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: PERIOD ===== -->
              <div class="tab-pane fade" id="period" role="tabpanel">
                <div class="row g-4">
                  <div class="col-12 col-lg-7">
                    <div class="config-card bg-body shadow-sm">
                      <div class="config-card-header">
                        <i class="bi bi-calendar-range text-success"></i>
                        Τρέχουσα Περίοδος Αιτήσεων
                      </div>
                      <div class="config-card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                          <span class="period-status-badge period-status-open">
                            <i class="bi bi-circle-fill" style="font-size:.5rem;"></i> Ανοιχτή
                          </span>
                          <small class="text-secondary">Η περίοδος αιτήσεων είναι ενεργή</small>
                        </div>
                        <form>
                          <div class="row g-3">
                            <div class="col-md-6">
                              <label class="form-label fw-semibold">Έναρξη Περιόδου</label>
                              <input type="date" class="form-control" value="2026-02-01" />
                            </div>
                            <div class="col-md-6">
                              <label class="form-label fw-semibold">Λήξη Περιόδου</label>
                              <input type="date" class="form-control" value="2026-04-30" />
                            </div>
                            <div class="col-12">
                              <label class="form-label fw-semibold">Τίτλος Περιόδου</label>
                              <input type="text" class="form-control" value="Εαρινό Εξάμηνο 2025–2026" />
                            </div>
                            <div class="col-12">
                              <label class="form-label fw-semibold">Περιγραφή</label>
                              <textarea class="form-control" rows="3" placeholder="Προαιρετική περιγραφή της περιόδου αιτήσεων...">Περίοδος υποβολής αιτήσεων για το εαρινό εξάμηνο του ακαδημαϊκού έτους 2025-2026.</textarea>
                            </div>
                            <div class="col-md-6">
                              <label class="form-label fw-semibold">Κατάσταση</label>
                              <select class="form-select">
                                <option selected>Ανοιχτή</option>
                                <option>Κλειστή</option>
                                <option>Προσεχώς</option>
                              </select>
                            </div>
                            <div class="col-12 pt-1">
                              <button type="button" class="btn btn-primary">
                                <i class="bi bi-floppy me-1"></i>Αποθήκευση Αλλαγών
                              </button>
                              <button type="button" class="btn btn-danger ms-2">
                                <i class="bi bi-x-circle me-1"></i>Κλείσιμο Περιόδου
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-lg-5">
                    <div class="config-card bg-body shadow-sm">
                      <div class="config-card-header"><i class="bi bi-clock-history text-warning"></i>Ιστορικό Περιόδων</div>
                      <div class="config-card-body p-0">
                        <ul class="list-group list-group-flush">
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                              <div class="fw-semibold small">Εαρινό 2025–2026</div>
                              <div class="text-secondary" style="font-size:.8rem;">01/02/2026 – 30/04/2026</div>
                            </div>
                            <span class="period-status-badge period-status-open">Ανοιχτή</span>
                          </li>
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                              <div class="fw-semibold small">Χειμερινό 2025–2026</div>
                              <div class="text-secondary" style="font-size:.8rem;">01/09/2025 – 30/11/2025</div>
                            </div>
                            <span class="period-status-badge period-status-closed">Κλειστή</span>
                          </li>
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                              <div class="fw-semibold small">Εαρινό 2024–2025</div>
                              <div class="text-secondary" style="font-size:.8rem;">01/02/2025 – 30/04/2025</div>
                            </div>
                            <span class="period-status-badge period-status-closed">Κλειστή</span>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ===== TAB: EVALUATORS ===== -->
              <div class="tab-pane fade" id="evaluators" role="tabpanel">
                <div class="admin-table-card bg-body shadow-sm">
                  <div class="admin-table-toolbar">
                    <h6 class="mb-0 fw-semibold">Ανάθεση Αξιολογητών σε Αιτήσεις</h6>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#evalModal">
                      <i class="bi bi-person-plus me-1"></i>Νέα Ανάθεση
                    </button>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr><th>Αίτηση</th><th>Αξιολογητής</th><th>Ημ/νία Ανάθεσης</th><th>Κατάσταση Αξιολόγησης</th><th class="text-end">Ενέργειες</th></tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Καθηγητής Μαθηματικών Α'</td>
                          <td><div class="d-flex align-items-center gap-2"><div class="table-avatar-placeholder" style="background:#dbeafe;color:#1d4ed8;">ΜΠ</div>Μαρία Παπαδοπούλου</div></td>
                          <td class="text-secondary small">05/02/2026</td>
                          <td><span class="badge bg-warning text-dark rounded-pill px-3">Σε εξέλιξη</span></td>
                          <td class="text-end"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-person-dash"></i></button></td>
                        </tr>
                        <tr>
                          <td>Εκπαιδευτικός Φυσικής Β'</td>
                          <td><div class="d-flex align-items-center gap-2"><div class="table-avatar-placeholder" style="background:#fee2e2;color:#b91c1c;">ΓΑ</div>Γιώργος Αντωνίου</div></td>
                          <td class="text-secondary small">10/02/2026</td>
                          <td><span class="badge bg-success rounded-pill px-3">Ολοκληρώθηκε</span></td>
                          <td class="text-end"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-person-dash"></i></button></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

            </div>
            <!-- end tab-content -->

          </div>
        </div>
      </main>

      <!-- ===== FOOTER ===== -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">BigBottleBOYS &copy; 2026</div>
        <strong>Copyright &copy; 2026 <a href="#" class="text-decoration-none">TheBigBottleBoys</a>.</strong> All rights reserved.
      </footer>

    </div>

    <!-- ===== MODALS ===== -->
    <!-- Application Modal -->
    <div class="modal fade" id="appModal" tabindex="-1" aria-labelledby="appModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="appModalLabel">Νέα Αίτηση</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label fw-semibold">Τίτλος Αίτησης <span class="text-danger">*</span></label>
                <input type="text" class="form-control" placeholder="π.χ. Καθηγητής Μαθηματικών Α' Τάξης" />
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Σχολή</label>
                <select class="form-select">
                  <option value="">Επιλέξτε σχολή...</option>
                  <option>Σχολή Θετικών Επιστημών</option>
                  <option>Σχολή Πληροφορικής</option>
                  <option>Σχολή Ανθρωπιστικών Σπουδών</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Τμήμα</label>
                <select class="form-select">
                  <option value="">Επιλέξτε τμήμα...</option>
                  <option>Τμήμα Μαθηματικών</option>
                  <option>Τμήμα Φυσικής</option>
                  <option>Τμήμα Πληροφορικής</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Μάθημα</label>
                <select class="form-select">
                  <option value="">Επιλέξτε μάθημα...</option>
                  <option>Ανάλυση Ι</option>
                  <option>Μηχανική</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Αξιολογητής</label>
                <select class="form-select">
                  <option value="">Ανάθεση αξιολογητή...</option>
                  <option>Μαρία Παπαδοπούλου</option>
                  <option>Γιώργος Αντωνίου</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Περιγραφή</label>
                <textarea class="form-control" rows="3" placeholder="Περιγραφή της αίτησης..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
            <button type="button" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Generic small modal for School/Dept/Course -->
    <div class="modal fade" id="schoolModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Νέα Σχολή</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label fw-semibold">Όνομα Σχολής</label><input type="text" class="form-control" placeholder="π.χ. Σχολή Θετικών Επιστημών" /></div>
          <div class="mb-3"><label class="form-label fw-semibold">Κωδικός</label><input type="text" class="form-control" placeholder="π.χ. ΘΕ" maxlength="10" /></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button><button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button></div>
      </div></div>
    </div>

    <div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Νέο Τμήμα</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label fw-semibold">Όνομα Τμήματος</label><input type="text" class="form-control" placeholder="π.χ. Τμήμα Μαθηματικών" /></div>
          <div class="mb-3"><label class="form-label fw-semibold">Σχολή</label>
            <select class="form-select"><option value="">Επιλέξτε σχολή...</option><option>Σχολή Θετικών Επιστημών</option><option>Σχολή Πληροφορικής</option></select>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button><button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button></div>
      </div></div>
    </div>

    <div class="modal fade" id="courseModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Νέο Μάθημα</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Κωδικός</label><input type="text" class="form-control" placeholder="π.χ. MAT101" /></div>
            <div class="col-md-8"><label class="form-label fw-semibold">Τίτλος Μαθήματος</label><input type="text" class="form-control" placeholder="π.χ. Ανάλυση Ι" /></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Τμήμα</label><select class="form-select"><option value="">Επιλέξτε...</option><option>Μαθηματικών</option><option>Φυσικής</option><option>Πληροφορικής</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Εξάμηνο</label><input type="number" class="form-control" min="1" max="10" placeholder="1" /></div>
            <div class="col-md-3"><label class="form-label fw-semibold">ECTS</label><input type="number" class="form-control" min="1" max="12" placeholder="6" /></div>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button><button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Αποθήκευση</button></div>
      </div></div>
    </div>

    <div class="modal fade" id="evalModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Ανάθεση Αξιολογητή</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label fw-semibold">Αίτηση</label><select class="form-select"><option value="">Επιλέξτε αίτηση...</option><option>Καθηγητής Μαθηματικών Α'</option><option>Εκπαιδευτικός Φυσικής Β'</option></select></div>
          <div class="mb-3"><label class="form-label fw-semibold">Αξιολογητής</label><select class="form-select"><option value="">Επιλέξτε αξιολογητή...</option><option>Μαρία Παπαδοπούλου</option><option>Γιώργος Αντωνίου</option></select></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button><button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Ανάθεση</button></div>
      </div></div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
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

        // Handle anchor-based tab switching from sidebar links
        const hash = window.location.hash;
        if (hash) {
          const tabBtn = document.querySelector('[data-bs-target="' + hash + '"]');
          if (tabBtn) new bootstrap.Tab(tabBtn).show();
        }
      });

      function switchTab(tabId) {
        const tabBtn = document.querySelector('[data-bs-target="#' + tabId + '"]');
        if (tabBtn) {
          new bootstrap.Tab(tabBtn).show();
          window.location.hash = '#' + tabId;
        }
      }
    </script>
  </body>
</html>

<?php
require_once __DIR__ . '/../../includes/admin-guard.php';
$navFullName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?: 'Administrator';
?>
<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin | Configure System</title>
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
              <span class="nav-link text-secondary"><i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>Configure System</span>
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
                <img src="../../assets/images/avatar.png" class="user-image rounded-circle shadow" alt="<?= htmlspecialchars($navFullName, ENT_QUOTES, 'UTF-8') ?>" />
                <span class="d-none d-md-inline"><?= htmlspecialchars($navFullName, ENT_QUOTES, 'UTF-8') ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="../../assets/images/AdminLTELogo.png" class="rounded-circle shadow" alt="<?= htmlspecialchars($navFullName, ENT_QUOTES, 'UTF-8') ?>" />
                  <p><?= htmlspecialchars($navFullName, ENT_QUOTES, 'UTF-8') ?><small>Διαχειριστής Συστήματος</small></p>
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
              <li class="nav-item"><a href="configure_system.php" class="nav-link active"><i class="nav-icon bi bi-gear"></i><p>Configure System</p></a></li>
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
                  <span class="admin-page-title-icon" style="background:#fef3c7;color:#b45309;">
                    <i class="bi bi-gear-fill"></i>
                  </span>
                  Configure System
                </h4>
              </div>
              <div class="col-auto">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                  <li class="breadcrumb-item active">Configure System</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <!-- Save notification (hidden by default) -->
            <div class="alert alert-success alert-dismissible fade d-none mb-3" id="saveAlert" role="alert">
              <i class="bi bi-check-circle me-2"></i>Οι ρυθμίσεις αποθηκεύτηκαν επιτυχώς.
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <div class="row g-4">
              <div class="col-12 col-xl-8">

                <!-- General Settings -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-sliders text-primary"></i>
                    Γενικές Ρυθμίσεις
                  </div>
                  <div class="config-card-body">
                    <form>
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Όνομα Εφαρμογής <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" value="CareerTrack" />
                          <div class="form-text">Το όνομα που εμφανίζεται στην κεφαλίδα και τον τίτλο.</div>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Υπότιτλος / Slogan</label>
                          <input type="text" class="form-control" value="Σύστημα Διαχείρισης Αιτήσεων" />
                        </div>
                        <div class="col-12">
                          <label class="form-label fw-semibold">Περιγραφή Εφαρμογής</label>
                          <textarea class="form-control" rows="2">Σύστημα διαχείρισης αιτήσεων εκπαιδευτικού προσωπικού για ακαδημαϊκά ιδρύματα.</textarea>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Email Διαχειριστή <span class="text-danger">*</span></label>
                          <input type="email" class="form-control" value="admin@university.gr" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Τηλέφωνο Υποστήριξης</label>
                          <input type="tel" class="form-control" value="+30 210 1234567" />
                        </div>
                        <div class="col-12">
                          <button type="button" class="btn btn-primary" onclick="showSaveAlert()">
                            <i class="bi bi-floppy me-1"></i>Αποθήκευση
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Branding / Theme -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-palette text-purple" style="color:#6d28d9;"></i>
                    Branding & Θέμα
                  </div>
                  <div class="config-card-body">
                    <form>
                      <div class="row g-3">
                        <!-- Logo upload -->
                        <div class="col-12">
                          <label class="form-label fw-semibold">Λογότυπο</label>
                          <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="border rounded p-2" style="background:#f8fafc;">
                              <img src="../../assets/images/AdminLTELogo.png" alt="Current Logo" style="height:48px;object-fit:contain;" id="logoPreview" />
                            </div>
                            <div>
                              <label class="btn btn-outline-secondary btn-sm mb-1">
                                <i class="bi bi-upload me-1"></i>Αλλαγή Λογοτύπου
                                <input type="file" accept="image/*" class="d-none" onchange="previewLogo(this)" />
                              </label>
                              <div class="form-text">Συνιστώμενο μέγεθος: 200×50px. PNG ή SVG.</div>
                            </div>
                          </div>
                        </div>
                        <!-- Favicon -->
                        <div class="col-12">
                          <label class="form-label fw-semibold">Favicon</label>
                          <div class="d-flex align-items-center gap-3">
                            <div class="border rounded p-2" style="background:#f8fafc;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                              <i class="bi bi-globe text-secondary"></i>
                            </div>
                            <label class="btn btn-outline-secondary btn-sm">
                              <i class="bi bi-upload me-1"></i>Αλλαγή Favicon
                              <input type="file" accept="image/*,.ico" class="d-none" />
                            </label>
                          </div>
                        </div>
                        <!-- Colors -->
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Κύριο Χρώμα</label>
                          <div class="input-group">
                            <input type="color" class="form-control form-control-color" value="#0d6efd" style="max-width:60px;" />
                            <input type="text" class="form-control" value="#0d6efd" />
                          </div>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Δευτερεύον Χρώμα</label>
                          <div class="input-group">
                            <input type="color" class="form-control form-control-color" value="#6c757d" style="max-width:60px;" />
                            <input type="text" class="form-control" value="#6c757d" />
                          </div>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Χρώμα Sidebar</label>
                          <div class="input-group">
                            <input type="color" class="form-control form-control-color" value="#1f1f1f" style="max-width:60px;" />
                            <input type="text" class="form-control" value="#1f1f1f" />
                          </div>
                        </div>
                        <!-- Texts -->
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Λεκτικό "Υποβολή Αίτησης"</label>
                          <input type="text" class="form-control" value="Υποβολή Αίτησης" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Λεκτικό "Αξιολόγηση"</label>
                          <input type="text" class="form-control" value="Αξιολόγηση Αίτησης" />
                        </div>
                        <div class="col-12">
                          <button type="button" class="btn btn-primary" onclick="showSaveAlert()">
                            <i class="bi bi-floppy me-1"></i>Αποθήκευση
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Moodle Integration -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-plug text-success"></i>
                    Ενσωμάτωση Moodle
                  </div>
                  <div class="config-card-body">
                    <form>
                      <div class="row g-3">
                        <div class="col-12">
                          <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" id="moodleEnabled" checked />
                            <label class="form-check-label fw-semibold" for="moodleEnabled">
                              Ενεργοποίηση σύνδεσης με Moodle
                            </label>
                          </div>
                          <small class="text-secondary">Επιτρέπει τον συγχρονισμό χρηστών και μαθημάτων από το Moodle.</small>
                        </div>
                        <div class="col-12">
                          <label class="form-label fw-semibold">URL Moodle <span class="text-danger">*</span></label>
                          <input type="url" class="form-control" value="https://moodle.university.gr" placeholder="https://moodle.youruniversity.gr" />
                        </div>
                        <div class="col-12">
                          <label class="form-label fw-semibold">API Token <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="moodleToken" value="abc123xyz789token" />
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleMoodleToken()">
                              <i class="bi bi-eye" id="moodleTokenIcon"></i>
                            </button>
                            <button class="btn btn-outline-primary" type="button" title="Δοκιμή σύνδεσης" onclick="testMoodleConn()">
                              <i class="bi bi-wifi me-1"></i>Δοκιμή
                            </button>
                          </div>
                          <div class="form-text">Μπορείτε να βρείτε το token στις ρυθμίσεις χρήστη του Moodle.</div>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">ID Κατηγορίας Μαθημάτων</label>
                          <input type="number" class="form-control" value="12" placeholder="π.χ. 12" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Συχνότητα Συγχρονισμού</label>
                          <select class="form-select">
                            <option>Κάθε ώρα</option>
                            <option selected>Κάθε 6 ώρες</option>
                            <option>Καθημερινά</option>
                            <option>Χειροκίνητα</option>
                          </select>
                        </div>
                        <!-- Connection status indicator -->
                        <div class="col-12" id="moodleConnStatus" style="display:none;">
                          <div class="alert alert-success mb-0 py-2">
                            <i class="bi bi-check-circle me-2"></i>Επιτυχής σύνδεση με το Moodle!
                          </div>
                        </div>
                        <div class="col-12">
                          <button type="button" class="btn btn-primary" onclick="showSaveAlert()">
                            <i class="bi bi-floppy me-1"></i>Αποθήκευση
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Email Settings -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-envelope text-danger"></i>
                    Ρυθμίσεις Email (SMTP)
                  </div>
                  <div class="config-card-body">
                    <form>
                      <div class="row g-3">
                        <div class="col-12">
                          <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" id="smtpEnabled" checked />
                            <label class="form-check-label fw-semibold" for="smtpEnabled">
                              Αποστολή email ειδοποιήσεων
                            </label>
                          </div>
                        </div>
                        <div class="col-md-8">
                          <label class="form-label fw-semibold">SMTP Server</label>
                          <input type="text" class="form-control" value="smtp.university.gr" placeholder="smtp.example.gr" />
                        </div>
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Port</label>
                          <input type="number" class="form-control" value="587" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">SMTP Username</label>
                          <input type="text" class="form-control" value="noreply@university.gr" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">SMTP Password</label>
                          <input type="password" class="form-control" value="••••••••" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Email Αποστολέα</label>
                          <input type="email" class="form-control" value="noreply@university.gr" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Ονομ. Αποστολέα</label>
                          <input type="text" class="form-control" value="CareerTrack System" />
                        </div>
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Κρυπτογράφηση</label>
                          <select class="form-select">
                            <option>Χωρίς</option>
                            <option>SSL</option>
                            <option selected>TLS</option>
                          </select>
                        </div>
                        <div class="col-12">
                          <button type="button" class="btn btn-primary" onclick="showSaveAlert()">
                            <i class="bi bi-floppy me-1"></i>Αποθήκευση
                          </button>
                          <button type="button" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-send me-1"></i>Αποστολή Δοκιμαστικού Email
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

              </div>

              <!-- Right column: System Info + Quick Actions -->
              <div class="col-12 col-xl-4">

                <!-- System Info -->
                <div class="config-card bg-body shadow-sm mb-4">
                  <div class="config-card-header">
                    <i class="bi bi-info-circle text-info"></i>
                    Πληροφορίες Συστήματος
                  </div>
                  <div class="config-card-body p-0">
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-secondary small">Έκδοση Εφαρμογής</span>
                        <span class="badge bg-primary rounded-pill">v1.0.0</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-secondary small">PHP Version</span>
                        <span class="fw-semibold small">8.2.x</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-secondary small">Database</span>
                        <span class="fw-semibold small">MySQL 8.0</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-secondary small">Τελευταία Ενημέρωση</span>
                        <span class="fw-semibold small">27/02/2026</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-secondary small">Moodle</span>
                        <span class="badge bg-success rounded-pill">Συνδεδεμένο</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-secondary small">SMTP</span>
                        <span class="badge bg-success rounded-pill">Ενεργό</span>
                      </li>
                    </ul>
                  </div>
                </div>

                <!-- Maintenance -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-wrench text-warning"></i>
                    Συντήρηση Συστήματος
                  </div>
                  <div class="config-card-body">
                    <div class="d-grid gap-2">
                      <button type="button" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-arrow-clockwise me-2 text-primary"></i>Εκκαθάριση Cache
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-database me-2 text-success"></i>Δημιουργία Αντιγράφου DB
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-file-earmark-text me-2 text-info"></i>Λήψη Αρχείων Καταγραφής
                      </button>
                      <hr class="my-1" />
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="maintenanceMode" />
                        <label class="form-check-label text-danger fw-semibold" for="maintenanceMode">
                          Λειτουργία Συντήρησης
                        </label>
                      </div>
                      <small class="text-secondary">Ενεργοποιεί σελίδα συντήρησης για όλους τους χρήστες εκτός του admin.</small>
                    </div>
                  </div>
                </div>

              </div>
            </div>
            <!-- end row -->

          </div>
        </div>
      </main>

      <?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

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
      });

      function showSaveAlert() {
        var el = document.getElementById('saveAlert');
        el.classList.remove('d-none');
        el.classList.add('show');
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        setTimeout(function () { el.classList.remove('show'); setTimeout(function () { el.classList.add('d-none'); }, 200); }, 3500);
      }

      function toggleMoodleToken() {
        var inp = document.getElementById('moodleToken');
        var icon = document.getElementById('moodleTokenIcon');
        if (inp.type === 'password') {
          inp.type = 'text';
          icon.className = 'bi bi-eye-slash';
        } else {
          inp.type = 'password';
          icon.className = 'bi bi-eye';
        }
      }

      function testMoodleConn() {
        var status = document.getElementById('moodleConnStatus');
        status.style.display = 'block';
        setTimeout(function () { status.style.display = 'none'; }, 4000);
      }

      function previewLogo(input) {
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
            document.getElementById('logoPreview').src = e.target.result;
          };
          reader.readAsDataURL(input.files[0]);
        }
      }
    </script>
  </body>
</html>

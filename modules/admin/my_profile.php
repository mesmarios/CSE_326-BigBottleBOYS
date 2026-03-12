<!doctype html>
<html lang="el">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin | My Profile</title>
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
              <span class="nav-link text-secondary"><i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>My Profile</span>
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
                <img src="../../assets/images/avatar.png" class="user-image rounded-circle shadow" alt="Admin" id="navAvatar" />
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
              <li class="nav-item"><a href="my_profile.php" class="nav-link active"><i class="nav-icon bi bi-person-circle"></i><p>My Profile</p></a></li>
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
                    <i class="bi bi-person-fill"></i>
                  </span>
                  My Profile
                </h4>
              </div>
              <div class="col-auto">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                  <li class="breadcrumb-item active">My Profile</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">

            <!-- Success alert -->
            <div class="alert alert-success alert-dismissible fade d-none mb-3" id="profileAlert" role="alert">
              <i class="bi bi-check-circle me-2"></i>Οι αλλαγές αποθηκεύτηκαν επιτυχώς.
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <div class="row g-4">

              <!-- Left: Avatar + Role info card -->
              <div class="col-12 col-lg-4 col-xl-3">
                <div class="config-card bg-body shadow-sm text-center">
                  <div class="config-card-body py-4">
                    <!-- Avatar Upload -->
                    <div class="profile-avatar-upload mx-auto">
                      <img src="../../assets/images/avatar.png" alt="Avatar" id="profileAvatarImg" />
                      <label class="avatar-edit-btn" title="Αλλαγή φωτογραφίας">
                        <i class="bi bi-camera-fill"></i>
                        <input type="file" accept="image/*" class="d-none" onchange="previewAvatar(this)" />
                      </label>
                    </div>

                    <h5 class="fw-bold mb-0" id="profileDisplayName">Administrator</h5>
                    <p class="text-secondary small mb-3">Διαχειριστής Συστήματος</p>

                    <span class="badge badge-role-admin rounded-pill px-3 py-2 mb-3">
                      <i class="bi bi-shield-fill me-1"></i>Admin
                    </span>

                    <hr />

                    <!-- Quick stats -->
                    <div class="row text-center g-0">
                      <div class="col-4 border-end">
                        <div class="fw-bold">124</div>
                        <div class="text-secondary" style="font-size:.75rem;">Χρήστες</div>
                      </div>
                      <div class="col-4 border-end">
                        <div class="fw-bold">47</div>
                        <div class="text-secondary" style="font-size:.75rem;">Αιτήσεις</div>
                      </div>
                      <div class="col-4">
                        <div class="fw-bold">12</div>
                        <div class="text-secondary" style="font-size:.75rem;">Τμήματα</div>
                      </div>
                    </div>

                    <hr />

                    <div class="text-start">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-envelope text-secondary" style="width:18px;"></i>
                        <span class="small" id="profileEmailDisplay">admin@university.gr</span>
                      </div>
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-telephone text-secondary" style="width:18px;"></i>
                        <span class="small">+30 210 1234567</span>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-check text-secondary" style="width:18px;"></i>
                        <span class="small">Μέλος από 01/01/2026</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right: Edit forms -->
              <div class="col-12 col-lg-8 col-xl-9">

                <!-- Personal Info -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-person-lines-fill text-primary"></i>
                    Στοιχεία Λογαριασμού
                  </div>
                  <div class="config-card-body">
                    <form id="profileForm">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Όνομα <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="firstName" value="Admin" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Επώνυμο <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" id="lastName" value="istrator" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                          <input type="email" class="form-control" id="profileEmail" value="admin@university.gr" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Τηλέφωνο</label>
                          <input type="tel" class="form-control" value="+30 210 1234567" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Ρόλος</label>
                          <input type="text" class="form-control" value="Διαχειριστής Συστήματος" disabled />
                          <div class="form-text">Ο ρόλος δεν μπορεί να αλλαχθεί από εδώ.</div>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Γλώσσα Διεπαφής</label>
                          <select class="form-select">
                            <option selected>Ελληνικά</option>
                            <option>English</option>
                          </select>
                        </div>
                        <div class="col-12">
                          <button type="button" class="btn btn-primary" onclick="saveProfile()">
                            <i class="bi bi-floppy me-1"></i>Αποθήκευση Στοιχείων
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Change Password -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-lock-fill text-warning"></i>
                    Αλλαγή Κωδικού Πρόσβασης
                  </div>
                  <div class="config-card-body">
                    <form id="passwordForm" novalidate>
                      <div class="row g-3">
                        <div class="col-md-6 col-xl-4">
                          <label class="form-label fw-semibold">Τρέχων Κωδικός <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="currentPassword" placeholder="Τρέχων κωδικός" />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('currentPassword',this)">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                          <label class="form-label fw-semibold">Νέος Κωδικός <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" placeholder="Τουλάχιστον 8 χαρακτήρες" oninput="checkPwdStrength(this.value)" />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('newPassword',this)">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                          <!-- Password strength -->
                          <div class="mt-1" id="pwdStrengthWrap" style="display:none;">
                            <div class="progress" style="height:4px;">
                              <div class="progress-bar" id="pwdStrengthBar" style="width:0%"></div>
                            </div>
                            <small id="pwdStrengthText" class="text-secondary"></small>
                          </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                          <label class="form-label fw-semibold">Επιβεβαίωση Νέου Κωδικού <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" placeholder="Επαναλάβετε νέο κωδικό" />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('confirmPassword',this)">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                        </div>

                        <!-- Password requirements -->
                        <div class="col-12">
                          <div class="border rounded p-3 bg-body-tertiary">
                            <p class="small fw-semibold mb-2 text-secondary">Απαιτήσεις κωδικού:</p>
                            <ul class="list-unstyled mb-0 small text-secondary" id="pwdReqs">
                              <li id="req-length"><i class="bi bi-circle me-2"></i>Τουλάχιστον 8 χαρακτήρες</li>
                              <li id="req-upper"><i class="bi bi-circle me-2"></i>Ένα κεφαλαίο γράμμα</li>
                              <li id="req-lower"><i class="bi bi-circle me-2"></i>Ένα πεζό γράμμα</li>
                              <li id="req-number"><i class="bi bi-circle me-2"></i>Έναν αριθμό</li>
                              <li id="req-special"><i class="bi bi-circle me-2"></i>Έναν ειδικό χαρακτήρα (!@#$%)</li>
                            </ul>
                          </div>
                        </div>

                        <div class="col-12">
                          <button type="button" class="btn btn-warning" onclick="changePassword()">
                            <i class="bi bi-lock me-1"></i>Αλλαγή Κωδικού
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Two-Factor Auth -->
                <div class="config-card bg-body shadow-sm">
                  <div class="config-card-header">
                    <i class="bi bi-shield-check text-success"></i>
                    Ασφάλεια Λογαριασμού
                  </div>
                  <div class="config-card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                      <div>
                        <div class="fw-semibold">Έλεγχος Ταυτότητας Δύο Παραγόντων (2FA)</div>
                        <small class="text-secondary">Προσθέστε ένα επιπλέον επίπεδο ασφάλειας στον λογαριασμό σας.</small>
                      </div>
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="twoFactor" style="width:2.5em;height:1.4em;" />
                        <label class="form-check-label ms-1 fw-semibold" for="twoFactor">Ανενεργό</label>
                      </div>
                    </div>
                    <hr />
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                      <div>
                        <div class="fw-semibold">Ειδοποιήσεις Σύνδεσης</div>
                        <small class="text-secondary">Λαμβάνετε email κάθε φορά που γίνεται σύνδεση στον λογαριασμό σας.</small>
                      </div>
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="loginNotif" checked style="width:2.5em;height:1.4em;" />
                        <label class="form-check-label ms-1 fw-semibold" for="loginNotif">Ενεργό</label>
                      </div>
                    </div>
                    <hr />
                    <!-- Activity log -->
                    <div class="fw-semibold mb-2">Πρόσφατη Δραστηριότητα</div>
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item px-0 py-2">
                        <div class="d-flex align-items-center gap-3">
                          <div class="stat-icon-wrap" style="background:#dcfce7;color:#15803d;width:36px;height:36px;border-radius:10px;font-size:1rem;">
                            <i class="bi bi-box-arrow-in-right"></i>
                          </div>
                          <div>
                            <div class="small fw-semibold">Σύνδεση επιτυχής</div>
                            <div class="text-secondary" style="font-size:.78rem;">27/02/2026 09:14 · Chrome · Windows 11</div>
                          </div>
                          <span class="badge bg-success ms-auto">Επιτυχία</span>
                        </div>
                      </li>
                      <li class="list-group-item px-0 py-2">
                        <div class="d-flex align-items-center gap-3">
                          <div class="stat-icon-wrap" style="background:#dcfce7;color:#15803d;width:36px;height:36px;border-radius:10px;font-size:1rem;">
                            <i class="bi bi-box-arrow-in-right"></i>
                          </div>
                          <div>
                            <div class="small fw-semibold">Σύνδεση επιτυχής</div>
                            <div class="text-secondary" style="font-size:.78rem;">26/02/2026 14:32 · Chrome · Windows 11</div>
                          </div>
                          <span class="badge bg-success ms-auto">Επιτυχία</span>
                        </div>
                      </li>
                      <li class="list-group-item px-0 py-2">
                        <div class="d-flex align-items-center gap-3">
                          <div class="stat-icon-wrap" style="background:#fee2e2;color:#b91c1c;width:36px;height:36px;border-radius:10px;font-size:1rem;">
                            <i class="bi bi-x-circle"></i>
                          </div>
                          <div>
                            <div class="small fw-semibold">Αποτυχημένη σύνδεση</div>
                            <div class="text-secondary" style="font-size:.78rem;">25/02/2026 11:05 · Firefox · Unknown</div>
                          </div>
                          <span class="badge bg-danger ms-auto">Αποτυχία</span>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>

              </div>
            </div>
            <!-- end row -->

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

        document.getElementById('twoFactor').addEventListener('change', function () {
          this.nextElementSibling.textContent = this.checked ? 'Ενεργό' : 'Ανενεργό';
        });
      });

      function previewAvatar(input) {
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
            document.getElementById('profileAvatarImg').src = e.target.result;
            document.getElementById('navAvatar').src = e.target.result;
          };
          reader.readAsDataURL(input.files[0]);
        }
      }

      function saveProfile() {
        var firstName = document.getElementById('firstName').value.trim();
        var lastName  = document.getElementById('lastName').value.trim();
        var email     = document.getElementById('profileEmail').value.trim();
        if (!firstName || !lastName || !email) {
          alert('Παρακαλώ συμπληρώστε όλα τα υποχρεωτικά πεδία.');
          return;
        }
        document.getElementById('profileDisplayName').textContent = firstName + ' ' + lastName;
        document.getElementById('profileEmailDisplay').textContent = email;
        showAlert('profileAlert');
      }

      function changePassword() {
        var curr = document.getElementById('currentPassword').value;
        var nw   = document.getElementById('newPassword').value;
        var conf = document.getElementById('confirmPassword').value;
        if (!curr || !nw || !conf) { alert('Συμπληρώστε όλα τα πεδία κωδικού.'); return; }
        if (nw !== conf) { alert('Ο νέος κωδικός και η επιβεβαίωση δεν ταιριάζουν.'); return; }
        if (nw.length < 8) { alert('Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.'); return; }
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
        document.getElementById('pwdStrengthWrap').style.display = 'none';
        showAlert('profileAlert');
      }

      function togglePwd(fieldId, btn) {
        var inp = document.getElementById(fieldId);
        var icon = btn.querySelector('i');
        if (inp.type === 'password') {
          inp.type = 'text';
          icon.className = 'bi bi-eye-slash';
        } else {
          inp.type = 'password';
          icon.className = 'bi bi-eye';
        }
      }

      function checkPwdStrength(val) {
        var wrap = document.getElementById('pwdStrengthWrap');
        var bar  = document.getElementById('pwdStrengthBar');
        var txt  = document.getElementById('pwdStrengthText');
        if (!val) { wrap.style.display = 'none'; return; }
        wrap.style.display = 'block';

        var score = 0;
        var setReq = function (id, ok) {
          var el = document.getElementById(id);
          el.querySelector('i').className = ok ? 'bi bi-check-circle-fill me-2 text-success' : 'bi bi-circle me-2';
          el.className = ok ? 'text-success' : '';
          if (ok) score++;
        };

        setReq('req-length',  val.length >= 8);
        setReq('req-upper',   /[A-Z]/.test(val));
        setReq('req-lower',   /[a-z]/.test(val));
        setReq('req-number',  /[0-9]/.test(val));
        setReq('req-special', /[!@#$%^&*()_+\-=]/.test(val));

        var widths  = ['20%', '40%', '60%', '80%', '100%'];
        var colors  = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'];
        var labels  = ['Πολύ αδύναμος', 'Αδύναμος', 'Μέτριος', 'Ισχυρός', 'Πολύ ισχυρός'];

        bar.style.width = widths[score - 1] || '0%';
        bar.style.backgroundColor = colors[score - 1] || '#ef4444';
        txt.textContent = labels[score - 1] || '';
        txt.style.color = colors[score - 1] || '';
      }

      function showAlert(id) {
        var el = document.getElementById(id);
        el.classList.remove('d-none');
        el.classList.add('show');
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        setTimeout(function () {
          el.classList.remove('show');
          setTimeout(function () { el.classList.add('d-none'); }, 200);
        }, 3500);
      }
    </script>
  </body>
</html>

<?php include('../../includes/layout.php'); include('../../includes/header.php'); include('../../includes/nav.php'); ?>
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
                Candidate
              </div>
              <h2 class="mb-1">Welcome back, <span id="dashWelcomeName">Candidate</span>!</h2>
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

            <!-- ── Notifications + Open Calls ─────────────────────── -->
            <div class="row g-3 mb-4">

              <!-- Notifications -->
              <div class="col-lg-5">
                <div class="card info-card h-100">
                  <div class="card-header d-flex align-items-center justify-content-between">
                    <h6><i class="bi bi-bell-fill me-2 text-primary"></i>Notifications</h6>
                    <a href="#" id="viewAllNotifBtn" class="text-primary" style="font-size:.78rem; font-weight:600; text-decoration:none;" data-bs-toggle="modal" data-bs-target="#allNotifsModal">
                      View all <i class="bi bi-arrow-right"></i>
                    </a>
                  </div>
                  <div class="card-body p-3">
                    <div id="notifList">
                      <!-- populated by JS -->
                    </div>
                    <p id="notifEmpty" class="text-center text-muted py-3 mb-0 d-none" style="font-size:.84rem;">
                      No new notifications.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Open Calls -->
              <div class="col-lg-7">
                <div class="card info-card h-100">
                  <div class="card-header d-flex align-items-center justify-content-between">
                    <h6><i class="bi bi-megaphone-fill me-2 text-success"></i>Open Application Calls</h6>
                    <a href="./myapplication.php" class="text-primary"
                       style="font-size:.78rem; font-weight:600; text-decoration:none;">
                      View all <i class="bi bi-arrow-right"></i>
                    </a>
                  </div>
                  <div class="card-body p-3">
                    <div id="openCallsList">
                      <!-- populated by JS -->
                    </div>
                    <p id="openCallsEmpty" class="text-center text-muted py-3 mb-0 d-none" style="font-size:.84rem;">
                      No open calls at the moment.
                    </p>
                  </div>
                </div>
              </div>

            </div><!-- /row notifications + open calls -->

          </div><!-- /container-fluid -->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

      <!-- ── All Notifications Modal ──────────────────────────── -->
      <div class="modal fade" id="allNotifsModal" tabindex="-1" aria-labelledby="allNotifsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content" style="border-radius:.85rem; border:none; min-height:520px;">
            <div class="modal-header px-4 py-3" style="border-bottom:1px solid #e9ecef;">
              <h5 class="modal-title fw-bold" id="allNotifsModalLabel">
                <i class="bi bi-bell-fill me-2 text-warning"></i>All Notifications
                <span id="allNotifsCount" class="badge bg-warning text-dark ms-2" style="font-size:.78rem;"></span>
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3" id="allNotifsBody" style="overflow-y:auto; max-height:65vh;">
              <!-- populated by JS -->
            </div>
            <div class="modal-footer px-4" style="border-top:1px solid #e9ecef;">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

<script src="../../recruitment/assets/js/index.js"></script>

<?php include('../../includes/footer.php'); ?>

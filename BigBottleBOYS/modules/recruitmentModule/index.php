<?php include('../../includes/layout.php'); include('../../includes/header.php'); include('../../includes/nav.php'); ?>
<style>
/* ── Recruitment Module – Candidate Dashboard ─────────────────────── */

/* Welcome banner */
.dash-welcome {
  background: linear-gradient(135deg, #1a6fc4 0%, #0d47a1 100%);
  border-radius: .85rem;
  padding: 2rem 2rem 1.75rem;
  color: #fff;
  position: relative;
  overflow: hidden;
}
.dash-welcome::before {
  content: '';
  position: absolute;
  top: -70px; right: -70px;
  width: 260px; height: 260px;
  background: rgba(255,255,255,.06);
  border-radius: 50%;
  pointer-events: none;
}
.dash-welcome::after {
  content: '';
  position: absolute;
  bottom: -50px; right: 120px;
  width: 160px; height: 160px;
  background: rgba(255,255,255,.04);
  border-radius: 50%;
  pointer-events: none;
}
.dash-welcome .welcome-role-badge {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  background: rgba(255,255,255,.18);
  border: 1px solid rgba(255,255,255,.3);
  border-radius: 2rem;
  padding: .2rem .75rem;
  font-size: .75rem;
  font-weight: 600;
  letter-spacing: .04em;
  text-transform: uppercase;
  margin-bottom: .65rem;
}
.dash-welcome h2 { font-size: 1.6rem; font-weight: 700; margin-bottom: .3rem; }
.dash-welcome p  { font-size: .88rem; opacity: .82; margin-bottom: 0; max-width: 520px; }

/* Section heading */
.section-heading {
  font-size: .95rem;
  font-weight: 700;
  color: #1a1a2e;
  letter-spacing: .01em;
  border-left: 4px solid #0d6efd;
  padding-left: .6rem;
  margin-bottom: 0;
}

/* Summary stat cards */
.stat-card {
  border: none;
  border-radius: .85rem;
  box-shadow: 0 2px 12px rgba(0,0,0,.07);
  transition: box-shadow .2s ease, transform .2s ease;
  overflow: hidden;
}
.stat-card:hover {
  box-shadow: 0 6px 24px rgba(0,0,0,.12);
  transform: translateY(-2px);
}
.stat-card .stat-icon {
  width: 52px; height: 52px;
  border-radius: .65rem;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem;
  flex-shrink: 0;
}
.stat-card .stat-value {
  font-size: 2rem;
  font-weight: 800;
  line-height: 1;
  color: #1a1a2e;
}
.stat-card .stat-label {
  font-size: .78rem;
  color: #6c757d;
  font-weight: 600;
  letter-spacing: .03em;
  text-transform: uppercase;
  margin-top: .2rem;
}
.stat-card .stat-footer {
  font-size: .75rem;
  color: #6c757d;
  padding: .55rem 1.25rem;
  background: #f8f9fa;
  border-top: 1px solid #f0f0f0;
}

/* Quick action buttons */
.quick-action-card {
  border: none;
  border-radius: .85rem;
  box-shadow: 0 2px 12px rgba(0,0,0,.07);
}
.quick-action-btn {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border: 1.5px solid #e9ecef;
  border-radius: .75rem;
  background: #fff;
  text-decoration: none;
  color: #1a1a2e;
  transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
  width: 100%;
}
.quick-action-btn:hover {
  border-color: #0d6efd;
  box-shadow: 0 4px 16px rgba(13,110,253,.12);
  background: #f0f5ff;
  color: #0d6efd;
}
.quick-action-btn .qa-icon {
  width: 42px; height: 42px;
  border-radius: .55rem;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.15rem;
  flex-shrink: 0;
}
.quick-action-btn .qa-title {
  font-size: .88rem;
  font-weight: 700;
  line-height: 1.1;
  margin-bottom: .1rem;
}
.quick-action-btn .qa-sub {
  font-size: .74rem;
  color: #6c757d;
  line-height: 1;
}

/* General info cards */
.info-card {
  border: none;
  border-radius: .85rem;
  box-shadow: 0 2px 12px rgba(0,0,0,.07);
}
.info-card .card-header {
  background: #fff;
  border-bottom: 1px solid #e9ecef;
  border-radius: .85rem .85rem 0 0 !important;
  padding: .85rem 1.25rem;
}
.info-card .card-header h6 {
  font-size: .88rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
}

/* Activity list */
.activity-item {
  display: flex;
  align-items: flex-start;
  gap: .85rem;
  padding: .7rem 0;
  border-bottom: 1px solid #f0f0f0;
}
.activity-item:last-child { border-bottom: none; }
.activity-dot {
  width: 34px; height: 34px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .85rem;
  flex-shrink: 0;
  margin-top: .1rem;
}
.activity-text {
  font-size: .84rem;
  font-weight: 600;
  color: #1a1a2e;
  line-height: 1.25;
}
.activity-time {
  font-size: .73rem;
  color: #adb5bd;
  margin-top: .15rem;
}

/* Notification list */
.notif-item {
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  padding: .65rem 0;
  border-bottom: 1px solid #f0f0f0;
}
.notif-item:last-child { border-bottom: none; }
.notif-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: #0d6efd;
  flex-shrink: 0;
  margin-top: .42rem;
}
.notif-dot.read { background: #dee2e6; }
.notif-text {
  font-size: .83rem;
  color: #1a1a2e;
  line-height: 1.3;
}
.notif-time {
  font-size: .72rem;
  color: #adb5bd;
  margin-top: .1rem;
}

/* Open calls */
.open-call-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: .75rem 0;
  border-bottom: 1px solid #f0f0f0;
}
.open-call-item:last-child { border-bottom: none; }
.open-call-title {
  font-size: .84rem;
  font-weight: 700;
  color: #1a1a2e;
  line-height: 1.2;
  margin-bottom: .15rem;
}
.open-call-meta {
  font-size: .74rem;
  color: #6c757d;
  line-height: 1.3;
}
.deadline-badge {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  font-size: .72rem;
  font-weight: 600;
  padding: .25rem .55rem;
  border-radius: .35rem;
}
.deadline-soon { background: #fff3cd; color: #664d03; }
.deadline-ok   { background: #d1e7dd; color: #0a3622; }

/* Responsive tweaks */
@media (max-width: 576px) {
  .dash-welcome h2 { font-size: 1.25rem; }
  .stat-card .stat-value { font-size: 1.6rem; }
  .quick-action-btn .qa-title { font-size: .82rem; }
}

/* Blurry backdrop for notifications modal */
#allNotifsModal ~ .modal-backdrop,
.modal-backdrop {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  background-color: rgba(15, 23, 42, 0.35) !important;
  opacity: 1 !important;
}
</style>

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
                    <i class="bi bi-lightning-charge-fill text-warning"></i>
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
                      <div class="qa-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-bar-chart-steps"></i>
                      </div>
                      <div>
                        <div class="qa-title">View Application Status</div>
                        <div class="qa-sub">Track progress of your submissions</div>
                      </div>
                      <i class="bi bi-chevron-right ms-auto text-muted" style="font-size:.75rem;"></i>
                    </a>
                    <a href="./myprofile.php" class="quick-action-btn">
                      <div class="qa-icon bg-success bg-opacity-10 text-success">
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
                    <h6><i class="bi bi-bell-fill me-2 text-warning"></i>Notifications</h6>
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

<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── Helpers ──────────────────────────────────────────────── */
  function getSafe(key, fallback) {
    try { return JSON.parse(localStorage.getItem(key)) || fallback; }
    catch(e) { return fallback; }
  }

  function timeAgo(isoStr) {
    if (!isoStr) return '';
    const diff = Date.now() - new Date(isoStr).getTime();
    const m = Math.floor(diff / 60000);
    const h = Math.floor(m / 60);
    const d = Math.floor(h / 24);
    if (d > 0)  return d  + (d  === 1 ? ' day ago'    : ' days ago');
    if (h > 0)  return h  + (h  === 1 ? ' hour ago'   : ' hours ago');
    if (m > 0)  return m  + (m  === 1 ? ' minute ago' : ' minutes ago');
    return 'Just now';
  }

  function daysUntil(isoStr) {
    if (!isoStr) return null;
    return Math.ceil((new Date(isoStr).getTime() - Date.now()) / 86400000);
  }

  function formatDate(isoStr) {
    if (!isoStr) return '—';
    const d = new Date(isoStr);
    return d.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
  }

  /* ── Welcome Name ─────────────────────────────────────────── */
  const profile = getSafe('userProfileData', {});
  const firstName = profile.name || 'Candidate';
  document.getElementById('dashWelcomeName').textContent = firstName;

  // Sync navbar display name
  const navName = document.getElementById('navbarUserName');
  if (navName && profile.name) {
    navName.textContent = (profile.name + ' ' + (profile.surname || '')).trim();
  }

  /* ── Load data ────────────────────────────────────────────── */
  const submissions   = getSafe('submittedApplications', []);   // matches myapplication.php
  const draftsData    = getSafe('applicationDrafts', {});       // object: { callId: draftObj, … }
  const activityLog   = getSafe('activityLog', []);
  const notifications = getSafe('notificationsData', []);

  // Static open calls (mirrors myapplication.php source)
  const OPEN_CALLS = [
    {
      id: 'CALL-2024-001',
      title: 'Assistant Professor – Computer Science',
      department: 'Dept. of Computer Science & Engineering',
      deadline: '2026-03-20',
    },
    {
      id: 'CALL-2024-002',
      title: 'Associate Professor – Mathematics',
      department: 'Dept. of Mathematics',
      deadline: '2026-03-28',
    },
    {
      id: 'CALL-2024-003',
      title: 'Lecturer – Electrical Engineering',
      department: 'Dept. of Electrical Engineering',
      deadline: '2026-04-10',
    },
    {
      id: 'CALL-2024-004',
      title: 'Research Fellow – Biomedical Engineering',
      department: 'Dept. of Biomedical Engineering',
      deadline: '2026-04-25',
    },
  ];

  /* ── Summary Counts ───────────────────────────────────────── */
  const draftCount = Object.keys(draftsData).length;
  const total   = submissions.length + draftCount;
  const drafts  = draftCount;
  const review  = submissions.filter(s =>
    ['submitted','reviewing','evaluated'].includes((s.status||'').toLowerCase())
  ).length;

  const submittedCallIds = submissions.map(s => s.callId);
  const upcoming = OPEN_CALLS.filter(c => {
    const days = daysUntil(c.deadline);
    return days !== null && days >= 0 && days <= 7 && !submittedCallIds.includes(c.id);
  }).length;

  document.getElementById('statTotal').textContent     = total;
  document.getElementById('statDraft').textContent     = drafts;
  document.getElementById('statReview').textContent    = review;
  document.getElementById('statDeadlines').textContent = upcoming;

  // Disable "Continue a Draft" button when there are no drafts
  const continueDraftBtn = document.getElementById('continueDraftBtn');
  if (continueDraftBtn && draftCount === 0) {
    continueDraftBtn.removeAttribute('href');
    continueDraftBtn.style.opacity = '0.45';
    continueDraftBtn.style.cursor  = 'not-allowed';
    continueDraftBtn.style.pointerEvents = 'none';
    continueDraftBtn.querySelector('.qa-sub').textContent = 'No drafts saved yet';
  }

  /* ── Recent Activity ──────────────────────────────────────── */
  const actEl = document.getElementById('activityList');
  const actEmpty = document.getElementById('activityEmpty');

  // Build default activity entries from data if no log exists
  let activity = activityLog.length ? activityLog : [];

  if (!activity.length && (submissions.length || draftCount)) {
    // Add draft activities from applicationDrafts
    Object.entries(draftsData).slice(0, 3).reverse().forEach(([callId, draft]) => {
      activity.push({
        icon: 'bi-pencil-fill',
        color: 'secondary',
        text: `Draft saved – <strong>${callId}</strong>`,
        date: draft.lastModified || null,
      });
    });
    // Add submission activities
    submissions.slice().reverse().slice(0, 5).forEach(sub => {
      activity.push({
        icon: 'bi-send-fill',
        color: 'primary',
        text: `Application submitted – <strong>${sub.callId || 'Application'}</strong>`,
        date: sub.submittedDate,
      });
    });
  }

  // Always inject 3 fallback entries so the section is never empty on first load
  if (!activity.length) {
    activity = [
      { icon: 'bi-person-check-fill', color: 'success',   text: 'Profile created successfully',                   date: new Date(Date.now() - 3600000*2).toISOString() },
      { icon: 'bi-bell-fill',         color: 'warning',   text: 'Welcome to CareerTrack Recruitment Portal',      date: new Date(Date.now() - 3600000*5).toISOString() },
      { icon: 'bi-shield-lock-fill',  color: 'info',      text: 'Account verified and activated',                 date: new Date(Date.now() - 86400000).toISOString()  },
    ];
  }

  if (activity.length) {
    actEl.innerHTML = activity.slice(0, 6).map(a => `
      <div class="activity-item">
        <div class="activity-dot bg-${a.color} bg-opacity-10 text-${a.color}">
          <i class="bi ${a.icon}"></i>
        </div>
        <div class="flex-grow-1">
          <div class="activity-text">${a.text}</div>
          <div class="activity-time"><i class="bi bi-clock me-1"></i>${timeAgo(a.date)}</div>
        </div>
      </div>`).join('');
  } else {
    actEmpty.classList.remove('d-none');
  }

  /* ── Notifications ────────────────────────────────────────── */
  const notifEl    = document.getElementById('notifList');
  const notifEmpty = document.getElementById('notifEmpty');

  let notifs = notifications.length ? notifications : [
    { text: 'Your application for <strong>CS Assistant Professor</strong> has been received.', date: new Date(Date.now() - 1800000).toISOString(),    read: false },
    { text: 'Deadline reminder: <strong>Mathematics Associate Professor</strong> closes in 5 days.', date: new Date(Date.now() - 7200000).toISOString(), read: false },
    { text: 'New position opened: <strong>Biomedical Research Fellow</strong>.', date: new Date(Date.now() - 86400000*2).toISOString(),  read: true  },
    { text: 'Profile completeness is at 80% — add your publications.', date: new Date(Date.now() - 86400000*3).toISOString(),  read: true  },
  ];

  function notifItemHTML(n) {
    return `
      <div class="notif-item">
        <div class="notif-dot ${n.read ? 'read' : ''}" title="${n.read ? 'Read' : 'Unread'}"></div>
        <div class="flex-grow-1">
          <div class="notif-text">${n.text}</div>
          <div class="notif-time"><i class="bi bi-clock me-1"></i>${timeAgo(n.date)}</div>
        </div>
      </div>`;
  }

  if (notifs.length) {
    notifEl.innerHTML = notifs.slice(0, 4).map(notifItemHTML).join('');
  } else {
    notifEmpty.classList.remove('d-none');
  }

  // Populate modal with ALL notifications
  const allNotifsBody  = document.getElementById('allNotifsBody');
  const allNotifsCount = document.getElementById('allNotifsCount');
  if (allNotifsCount) allNotifsCount.textContent = notifs.length;
  if (allNotifsBody) {
    if (notifs.length) {
      allNotifsBody.innerHTML = notifs.map(notifItemHTML).join('');
    } else {
      allNotifsBody.innerHTML = '<p class="text-center text-muted py-4 mb-0" style="font-size:.84rem;">No notifications yet.</p>';
    }
  }

  /* ── Open Calls ───────────────────────────────────────────── */
  const openEl    = document.getElementById('openCallsList');
  const openEmpty = document.getElementById('openCallsEmpty');

  const visibleCalls = OPEN_CALLS.filter(c => {
    const d = daysUntil(c.deadline);
    return d !== null && d >= 0;
  });

  if (visibleCalls.length) {
    openEl.innerHTML = visibleCalls.slice(0, 4).map(c => {
      const days = daysUntil(c.deadline);
      const badgeCls  = days <= 7 ? 'deadline-soon' : 'deadline-ok';
      const badgeIcon = days <= 7 ? 'bi-exclamation-circle-fill' : 'bi-calendar-check-fill';
      const alreadyApplied = submittedCallIds.includes(c.id);
      return `
        <div class="open-call-item">
          <div class="flex-grow-1" style="min-width:0;">
            <div class="open-call-title text-truncate">${c.title}</div>
            <div class="open-call-meta">
              <i class="bi bi-building me-1"></i>${c.department}<br>
              <span class="deadline-badge ${badgeCls} mt-1">
                <i class="bi ${badgeIcon}"></i>
                Deadline: ${formatDate(c.deadline)} (${days}d left)
              </span>
            </div>
          </div>
          <div class="flex-shrink-0">
            ${alreadyApplied
              ? `<span class="badge bg-success-subtle text-success px-2 py-1" style="font-size:.74rem;font-weight:600;">
                   <i class="bi bi-check-circle me-1"></i>Applied
                 </span>`
              : `<a href="./myapplication.php" class="btn btn-sm btn-primary" style="font-size:.76rem;">
                   <i class="bi bi-send me-1"></i>Apply
                 </a>`
            }
          </div>
        </div>`;
    }).join('');
  } else {
    openEmpty.classList.remove('d-none');
  }

});
</script>

<?php include('../../includes/footer.php'); ?>

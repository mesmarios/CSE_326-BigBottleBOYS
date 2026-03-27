/* ================================================================
   index.js  –  Recruitment Module: Dashboard
   Fetches all data from the PHP API.
================================================================= */

const API_BASE = '../../api';

function timeAgo(isoStr) {
  if (!isoStr) return '';
  const diff = Date.now() - new Date(isoStr).getTime();
  const m = Math.floor(diff / 60000);
  const h = Math.floor(m / 60);
  const d = Math.floor(h / 24);
  if (d > 0) return d + (d === 1 ? ' day ago'    : ' days ago');
  if (h > 0) return h + (h === 1 ? ' hour ago'   : ' hours ago');
  if (m > 0) return m + (m === 1 ? ' minute ago' : ' minutes ago');
  return 'Just now';
}

function daysUntil(isoStr) {
  if (!isoStr) return null;
  return Math.ceil((new Date(isoStr).getTime() - Date.now()) / 86400000);
}

function formatDate(isoStr) {
  if (!isoStr) return '—';
  const d = new Date(isoStr);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

/* ── Render stats cards ───────────────────────────────────────── */
function renderStats(applications, announcements) {
  const drafts     = applications.filter(a => a.status === 'Draft').length;
  const total      = applications.length;
  const review     = applications.filter(a =>
    ['Submitted', 'Under Review'].includes(a.status)).length;

  const submittedCallIds = applications.map(a => a.callId);
  const upcoming = announcements.filter(c => {
    const days = daysUntil(c.endDate);
    return days !== null && days >= 0 && days <= 7 && !submittedCallIds.includes(c.id);
  }).length;

  document.getElementById('statTotal').textContent     = total;
  document.getElementById('statDraft').textContent     = drafts;
  document.getElementById('statReview').textContent    = review;
  document.getElementById('statDeadlines').textContent = upcoming;

  // Disable "Continue a Draft" quick-action if no drafts
  const continueDraftBtn = document.getElementById('continueDraftBtn');
  if (continueDraftBtn && drafts === 0) {
    continueDraftBtn.removeAttribute('href');
    continueDraftBtn.style.opacity      = '0.45';
    continueDraftBtn.style.cursor       = 'not-allowed';
    continueDraftBtn.style.pointerEvents = 'none';
    continueDraftBtn.querySelector('.qa-sub').textContent = 'No drafts saved yet';
  }
}

/* ── Render recent activity ───────────────────────────────────── */
function renderActivity(applications) {
  const actEl    = document.getElementById('activityList');
  const actEmpty = document.getElementById('activityEmpty');

  const activity = [];
  applications.filter(a => a.status === 'Draft').forEach(a => {
    activity.push({
      icon:  'bi-pencil-fill',
      color: 'secondary',
      text:  `Draft saved – <strong>${a.callInfo?.title || a.callId}</strong>`,
      date:  a.savedAt,
    });
  });
  applications.filter(a => a.status !== 'Draft').forEach(a => {
    activity.push({
      icon:  'bi-send-fill',
      color: 'primary',
      text:  `Application submitted – <strong>${a.callInfo?.title || a.callId}</strong>`,
      date:  a.submittedDate,
    });
  });

  if (!activity.length) {
    activity.push(
      { icon: 'bi-person-check-fill', color: 'success', text: 'Profile created successfully',              date: new Date(Date.now() - 3600000 * 2).toISOString() },
      { icon: 'bi-bell-fill',         color: 'warning', text: 'Welcome to CareerTrack Recruitment Portal', date: new Date(Date.now() - 3600000 * 5).toISOString() },
      { icon: 'bi-shield-lock-fill',  color: 'info',    text: 'Account verified and activated',            date: new Date(Date.now() - 86400000).toISOString()  }
    );
  }

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

  if (!activity.length) actEmpty.classList.remove('d-none');
}

/* ── Render notifications ─────────────────────────────────────── */
function renderNotifications(notifications) {
  const notifEl    = document.getElementById('notifList');
  const notifEmpty = document.getElementById('notifEmpty');
  const allNotifsBody  = document.getElementById('allNotifsBody');
  const allNotifsCount = document.getElementById('allNotifsCount');

  const notifItemHTML = n => `
    <div class="notif-item">
      <div class="notif-dot ${n.read ? 'read' : ''}" title="${n.read ? 'Read' : 'Unread'}"></div>
      <div class="flex-grow-1">
        <div class="notif-text">${n.text}</div>
        <div class="notif-time"><i class="bi bi-clock me-1"></i>${timeAgo(n.date)}</div>
      </div>
    </div>`;

  if (notifications.length) {
    notifEl.innerHTML = notifications.slice(0, 4).map(notifItemHTML).join('');
  } else {
    notifEmpty.classList.remove('d-none');
  }

  if (allNotifsCount) allNotifsCount.textContent = notifications.length;
  if (allNotifsBody) {
    allNotifsBody.innerHTML = notifications.length
      ? notifications.map(notifItemHTML).join('')
      : '<p class="text-center text-muted py-4 mb-0" style="font-size:.84rem;">No notifications yet.</p>';
  }
}

/* ── Render open calls ────────────────────────────────────────── */
function renderOpenCalls(announcements, applications) {
  const openEl    = document.getElementById('openCallsList');
  const openEmpty = document.getElementById('openCallsEmpty');

  const submittedCallIds = applications.map(a => a.callId);
  const visibleCalls     = announcements.filter(c => {
    const days = daysUntil(c.endDate);
    return days !== null && days >= 0;
  });

  if (!visibleCalls.length) { openEmpty.classList.remove('d-none'); return; }

  openEl.innerHTML = visibleCalls.slice(0, 4).map(c => {
    const days     = daysUntil(c.endDate);
    const badgeCls  = days <= 7 ? 'deadline-soon' : 'deadline-ok';
    const badgeIcon = days <= 7 ? 'bi-exclamation-circle-fill' : 'bi-calendar-check-fill';
    const applied   = submittedCallIds.includes(c.id);
    return `
      <div class="open-call-item">
        <div class="flex-grow-1" style="min-width:0;">
          <div class="open-call-title text-truncate">${c.title}</div>
          <div class="open-call-meta">
            <i class="bi bi-building me-1"></i>${c.department}<br>
            <span class="deadline-badge ${badgeCls} mt-1">
              <i class="bi ${badgeIcon}"></i>
              Deadline: ${formatDate(c.endDate)} (${days}d left)
            </span>
          </div>
        </div>
        <div class="flex-shrink-0">
          ${applied
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
}

/* ── Init ─────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', async function () {
  // Sync navbar user name from CareerTrack global (set by PHP)
  const navName = document.getElementById('navbarUserName');
  if (navName && window.CareerTrack?.fullName) {
    navName.textContent = window.CareerTrack.fullName;
  }
  const dashWelcomeName = document.getElementById('dashWelcomeName');
  if (dashWelcomeName && window.CareerTrack?.firstName) {
    dashWelcomeName.textContent = window.CareerTrack.firstName;
  }

  try {
    const [annResp, appResp, notifResp] = await Promise.all([
      fetch(`${API_BASE}/announcements.php`).then(r => r.json()),
      fetch(`${API_BASE}/applications.php`).then(r => r.json()),
      fetch(`${API_BASE}/notifications.php`).then(r => r.json()),
    ]);

    const announcements = annResp.success   ? annResp.announcements    : [];
    const applications  = appResp.success   ? appResp.applications     : [];
    const notifications = notifResp.success ? notifResp.notifications  : [];

    renderStats(applications, announcements);
    renderActivity(applications);
    renderNotifications(notifications);
    renderOpenCalls(announcements, applications);
  } catch (e) {
    console.error('Dashboard init failed', e);
  }
});

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
        date: draft.savedAt || draft.lastModified || null,
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

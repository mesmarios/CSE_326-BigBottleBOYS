document.addEventListener('DOMContentLoaded', async function () {
  const BOOTSTRAP = window.RECRUITMENT_INDEX_BOOTSTRAP || {};
  const APPLICATIONS_API_URL = '../../api/applications.php';

  function readLocalJson(key, fallback) {
    try {
      const value = localStorage.getItem(key);
      return value ? JSON.parse(value) : fallback;
    } catch (error) {
      return fallback;
    }
  }

  function escapeHtml(value) {
    const span = document.createElement('span');
    span.textContent = String(value ?? '');
    return span.innerHTML;
  }

  function timeAgo(isoValue) {
    if (!isoValue) return '';

    const diff = Date.now() - new Date(isoValue).getTime();
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) return `${days} ${days === 1 ? 'day' : 'days'} ago`;
    if (hours > 0) return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;
    if (minutes > 0) return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago`;
    return 'Just now';
  }

  function isDraftStatus(status) {
    return String(status || '').toLowerCase() === 'draft';
  }

  function normalizeSubmission(submission = {}) {
    const callInfo = submission.callInfo || {};

    return {
      callId: String(submission.callId ?? callInfo.id ?? ''),
      title: submission.title || callInfo.title || 'Application',
      status: String(submission.status || ''),
      submittedDate: submission.submittedDate || null,
      updatedDate: submission.updatedDate || null,
      savedAt:
        submission.savedAt ||
        submission.updatedDate ||
        submission.submittedDate ||
        (submission.data && submission.data.savedAt) ||
        null,
    };
  }

  function getProfile() {
    if (BOOTSTRAP.user && typeof BOOTSTRAP.user === 'object') {
      return BOOTSTRAP.user;
    }

    return readLocalJson('userProfileData', {});
  }

  async function loadApplicationsFromServer() {
    try {
      const response = await fetch(APPLICATIONS_API_URL, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const result = await response.json();

      if (!response.ok || !result.success || !Array.isArray(result.applications)) {
        return null;
      }

      return result.applications.map(normalizeSubmission);
    } catch (error) {
      return null;
    }
  }

  function buildDraftEntries(submissions, localDrafts) {
    const submittedIds = new Set(
      submissions
        .filter(submission => !isDraftStatus(submission.status))
        .map(submission => String(submission.callId))
    );

    const draftsById = new Map();

    submissions
      .filter(submission => isDraftStatus(submission.status))
      .forEach(submission => {
        const callId = String(submission.callId);
        if (submittedIds.has(callId)) return;

        draftsById.set(callId, {
          callId,
          title: submission.title || `Application ${callId}`,
          savedAt: submission.savedAt || submission.updatedDate || submission.submittedDate || null,
          source: 'server',
        });
      });

    Object.entries(localDrafts).forEach(([callId, draft]) => {
      const normalizedCallId = String(callId);
      if (submittedIds.has(normalizedCallId) || draftsById.has(normalizedCallId)) {
        return;
      }

      draftsById.set(normalizedCallId, {
        callId: normalizedCallId,
        title: `Application ${normalizedCallId}`,
        savedAt: draft.savedAt || draft.lastModified || null,
        source: 'local',
      });
    });

    return Array.from(draftsById.values()).sort((left, right) => {
      return new Date(right.savedAt || 0).getTime() - new Date(left.savedAt || 0).getTime();
    });
  }

  function buildActivityEntries(submissions, draftEntries) {
    const items = [];

    draftEntries.forEach(draft => {
      items.push({
        icon: 'bi-pencil-fill',
        color: 'secondary',
        text: `Draft saved - ${draft.title}`,
        date: draft.savedAt,
      });
    });

    submissions
      .filter(submission => !isDraftStatus(submission.status))
      .forEach(submission => {
        const normalizedStatus = String(submission.status || '').toLowerCase();
        let actionText = 'updated';

        if (normalizedStatus === 'submitted') actionText = 'submitted';
        if (normalizedStatus === 'under review') actionText = 'under review';
        if (normalizedStatus === 'approved') actionText = 'approved';
        if (normalizedStatus === 'rejected') actionText = 'rejected';

        items.push({
          icon: 'bi-send-fill',
          color: normalizedStatus === 'approved'
            ? 'success'
            : (normalizedStatus === 'rejected' ? 'danger' : 'primary'),
          text: `Application ${actionText} - ${submission.title || submission.callId || 'Application'}`,
          date: submission.updatedDate || submission.submittedDate || submission.savedAt,
        });
      });

    items.sort((left, right) => {
      return new Date(right.date || 0).getTime() - new Date(left.date || 0).getTime();
    });

    if (items.length) {
      return items.slice(0, 6);
    }

    return [
      {
        icon: 'bi-person-check-fill',
        color: 'success',
        text: 'Profile created successfully',
        date: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
      },
      {
        icon: 'bi-bell-fill',
        color: 'warning',
        text: 'Welcome to CareerTrack Recruitment Portal',
        date: new Date(Date.now() - 5 * 60 * 60 * 1000).toISOString(),
      },
      {
        icon: 'bi-shield-lock-fill',
        color: 'info',
        text: 'Account verified and activated',
        date: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
      }
    ];
  }

  function setContinueDraftState(latestDraft) {
    const continueDraftBtn = document.getElementById('continueDraftBtn');
    if (!continueDraftBtn) return;

    const subtitle = continueDraftBtn.querySelector('.qa-sub');

    if (latestDraft) {
      continueDraftBtn.href = `./myapplication.php?draft=${encodeURIComponent(latestDraft.callId)}#drafts`;
      continueDraftBtn.style.opacity = '';
      continueDraftBtn.style.cursor = '';
      continueDraftBtn.style.pointerEvents = '';
      continueDraftBtn.setAttribute('aria-disabled', 'false');
      if (subtitle) {
        subtitle.textContent = 'Continue your most recent saved draft';
      }
      return;
    }

    continueDraftBtn.removeAttribute('href');
    continueDraftBtn.style.opacity = '0.45';
    continueDraftBtn.style.cursor = 'not-allowed';
    continueDraftBtn.style.pointerEvents = 'none';
    continueDraftBtn.setAttribute('aria-disabled', 'true');
    if (subtitle) {
      subtitle.textContent = 'No drafts saved yet';
    }
  }

  const profile = getProfile();
  const firstName = profile.name || BOOTSTRAP.roleLabel || 'Candidate';
  document.getElementById('dashWelcomeName').textContent = firstName;

  const navName = document.getElementById('navbarUserName');
  const fullName = `${profile.name || ''} ${profile.surname || ''}`.trim();
  if (navName && fullName) {
    navName.textContent = fullName;
  }

  const bootstrapSubmissions = Array.isArray(BOOTSTRAP.submissions)
    ? BOOTSTRAP.submissions.map(normalizeSubmission)
    : [];
  const serverSubmissions = await loadApplicationsFromServer();
  const submissions = Array.isArray(serverSubmissions) ? serverSubmissions : bootstrapSubmissions;
  const draftsData = readLocalJson('applicationDrafts', {});
  const draftEntries = buildDraftEntries(submissions, draftsData);
  const localOnlyDraftCount = draftEntries.filter(entry => entry.source === 'local').length;
  const stats = BOOTSTRAP.stats && typeof BOOTSTRAP.stats === 'object' ? BOOTSTRAP.stats : {};
  const computedTotal = submissions.length + localOnlyDraftCount;
  const computedDrafts = draftEntries.length;
  const computedReview = submissions.filter(submission => {
    return String(submission.status || '').toLowerCase() === 'under review';
  }).length;

  document.getElementById('statTotal').textContent = String(
    typeof stats.total === 'number' ? Math.max(stats.total, computedTotal) : computedTotal
  );
  document.getElementById('statDraft').textContent = String(
    typeof stats.drafts === 'number' ? Math.max(stats.drafts, computedDrafts) : computedDrafts
  );
  document.getElementById('statReview').textContent = String(
    typeof stats.underReview === 'number' ? Math.max(stats.underReview, computedReview) : computedReview
  );
  document.getElementById('statDeadlines').textContent = String(
    typeof stats.upcomingDeadlines === 'number' ? stats.upcomingDeadlines : 0
  );

  setContinueDraftState(draftEntries[0] || null);

  const actEl = document.getElementById('activityList');
  const actEmpty = document.getElementById('activityEmpty');
  const activity = buildActivityEntries(submissions, draftEntries);

  if (activity.length) {
    actEmpty.classList.add('d-none');
    actEl.innerHTML = activity.map(entry => `
      <div class="activity-item">
        <div class="activity-dot bg-${entry.color} bg-opacity-10 text-${entry.color}">
          <i class="bi ${entry.icon}"></i>
        </div>
        <div class="flex-grow-1">
          <div class="activity-text">${escapeHtml(entry.text)}</div>
          <div class="activity-time"><i class="bi bi-clock me-1"></i>${escapeHtml(timeAgo(entry.date))}</div>
        </div>
      </div>`).join('');
  } else {
    actEl.innerHTML = '';
    actEmpty.classList.remove('d-none');
  }
});

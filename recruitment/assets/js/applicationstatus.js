const BOOTSTRAP = window.APPLICATIONSTATUS_BOOTSTRAP || {};

const AVAILABLE_CALLS = Array.isArray(BOOTSTRAP.calls)
  ? BOOTSTRAP.calls.map(call => ({
      ...call,
      id: String(call.id),
      courses: Array.isArray(call.courses) ? call.courses : [call.courses || '-'],
    }))
  : [];

const SERVER_USER = BOOTSTRAP.user && typeof BOOTSTRAP.user === 'object'
  ? BOOTSTRAP.user
  : {};

const SUBMISSIONS = Array.isArray(BOOTSTRAP.submissions)
  ? BOOTSTRAP.submissions.map(submission => ({
      ...submission,
      callId: String(submission.callId),
    }))
  : [];

function formatDate(isoValue) {
  if (!isoValue) return '-';

  const [year, month, day] = isoValue.split('-');
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  return `${monthNames[parseInt(month, 10) - 1]} ${parseInt(day, 10)}, ${year}`;
}

function formatDateTime(isoValue) {
  if (!isoValue) return '-';

  const date = new Date(isoValue);
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  return `${monthNames[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
}

function escHtml(value) {
  const span = document.createElement('span');
  span.textContent = String(value ?? '');
  return span.innerHTML;
}

function getUserData() {
  return SERVER_USER;
}

const STAGES = [
  { key: 'Draft', label: 'Draft', icon: 'bi-pencil-square' },
  { key: 'Submitted', label: 'Submitted', icon: 'bi-send-fill' },
  { key: 'Under Review', label: 'Under\nReview', icon: 'bi-search' },
  { key: 'Evaluation Completed', label: 'Evaluation\nCompleted', icon: 'bi-clipboard-check' },
  { key: 'Approved', label: 'Approved', icon: 'bi-patch-check-fill' },
];

const REJECTED_STAGE = { key: 'Rejected', label: 'Rejected', icon: 'bi-x-circle-fill' };

function stageIndex(status) {
  const index = STAGES.findIndex(stage => stage.key === status);
  return index === -1 ? 1 : index;
}

function statusBadge(status) {
  const map = {
    Draft: ['badge-draft', 'bi-pencil-square'],
    Submitted: ['badge-submitted', 'bi-send'],
    'Under Review': ['badge-reviewing', 'bi-hourglass-split'],
    'Evaluation Completed': ['badge-evaluated', 'bi-clipboard-check'],
    Approved: ['badge-approved', 'bi-check-circle-fill'],
    Rejected: ['badge-rejected', 'bi-x-circle-fill'],
  };

  const badge = map[status] || ['badge-draft', 'bi-circle'];
  return `<span class="badge rounded-pill ${badge[0]}"><i class="bi ${badge[1]} me-1"></i>${escHtml(status)}</span>`;
}

function buildStepper(status) {
  const rejected = status === 'Rejected';
  const current = rejected ? stageIndex('Evaluation Completed') : stageIndex(status);
  const stages = rejected ? [...STAGES.slice(0, 4), REJECTED_STAGE] : [...STAGES];

  return stages.map((stage, index) => {
    let cssClass = index < current ? 'done' : (index === current ? 'current' : '');

    if (rejected && index === 4) {
      cssClass = 'current rejected-step';
    }

    if (!rejected && status === 'Approved' && index === 4) {
      cssClass = 'current approved-step done';
    }

    const labelHtml = stage.label.split('\n').join('<br>');

    return `<div class="status-step ${cssClass}">
      <div class="step-circle"><i class="bi ${stage.icon}"></i></div>
      <div class="step-label">${labelHtml}</div>
    </div>`;
  }).join('');
}

function buildTimeline(submission) {
  const status = submission.status || 'Submitted';
  const data = submission.data || {};
  const events = [
    {
      dot: 'dot-secondary',
      icon: 'bi-file-earmark-plus',
      title: 'Application Created',
      date: data.savedAt ? formatDateTime(data.savedAt) : formatDate(submission.submittedDate),
      description: 'Application record was created.',
    },
    {
      dot: 'dot-primary',
      icon: 'bi-send-fill',
      title: 'Application Submitted',
      date: formatDate(submission.submittedDate),
      description: 'Your application was successfully submitted.',
    }
  ];

  const currentStage = stageIndex(status);

  if (currentStage >= 2 || status === 'Rejected') {
    events.push({
      dot: 'dot-warning',
      icon: 'bi-search',
      title: 'Review Started',
      date: submission.reviewedDate ? formatDateTime(submission.reviewedDate) : '-',
      description: 'Your application is currently under review by the committee.',
    });
  }

  if (currentStage >= 3 || status === 'Rejected') {
    events.push({
      dot: 'dot-purple',
      icon: 'bi-clipboard-check',
      title: 'Evaluation Completed',
      date: submission.updatedDate ? formatDateTime(submission.updatedDate) : '-',
      description: 'The evaluation committee has completed its assessment.',
    });
  }

  if (status === 'Approved') {
    events.push({
      dot: 'dot-success',
      icon: 'bi-patch-check-fill',
      title: 'Application Approved',
      date: submission.updatedDate ? formatDateTime(submission.updatedDate) : '-',
      description: 'Congratulations! Your application has been approved.',
    });
  }

  if (status === 'Rejected') {
    events.push({
      dot: 'dot-danger',
      icon: 'bi-x-circle-fill',
      title: 'Application Rejected',
      date: submission.updatedDate ? formatDateTime(submission.updatedDate) : '-',
      description: 'Unfortunately your application was not selected at this time.',
    });
  }

  return events.map(event => `
    <li class="timeline-item">
      <div class="timeline-dot ${event.dot}"><i class="bi ${event.icon}" style="font-size:.65rem;"></i></div>
      <div class="timeline-title">${escHtml(event.title)}</div>
      <div class="timeline-date"><i class="bi bi-clock me-1"></i>${escHtml(event.date)}</div>
      <div class="timeline-desc">${escHtml(event.description)}</div>
    </li>
  `).join('');
}

function openFile(encodedUrl) {
  try {
    const dataUrl = decodeURIComponent(encodedUrl);
    const [header, body] = dataUrl.split(',');
    const mimeMatch = header.match(/:(.*?);/);
    if (!mimeMatch) {
      throw new Error('Invalid file payload');
    }

    const mime = mimeMatch[1];
    const bytes = atob(body);
    const buffer = new Uint8Array(bytes.length);

    for (let index = 0; index < bytes.length; index++) {
      buffer[index] = bytes.charCodeAt(index);
    }

    window.open(URL.createObjectURL(new Blob([buffer], { type: mime })), '_blank');
  } catch (error) {
    alert('Unable to open file.');
  }
}

window.openFile = openFile;

function buildDataPanel(submission) {
  const data = submission.data || {};
  const call = AVAILABLE_CALLS.find(item => String(item.id) === String(submission.callId)) || {};
  const user = getUserData();
  const fullName = data.fullName || [user.name, user.surname].filter(Boolean).join(' ') || '-';

  function row(label, value) {
    return `<div class="data-row d-flex gap-2 flex-wrap">
      <span class="data-label">${escHtml(label)}</span>
      <span class="data-value">${escHtml(value) || '-'}</span>
    </div>`;
  }

  function fileItem(name, dataUrl, label) {
    const viewButton = dataUrl
      ? `<button class="btn btn-sm btn-outline-primary btn-view-file" onclick="openFile('${encodeURIComponent(dataUrl)}')"><i class="bi bi-eye me-1"></i>View</button>`
      : `<button class="btn btn-sm btn-outline-secondary btn-view-file" disabled title="File data not available"><i class="bi bi-eye me-1"></i>View</button>`;

    return `<div class="file-item">
      <i class="bi bi-file-earmark-fill"></i>
      <div>
        <div style="font-weight:600;font-size:.83rem;">${escHtml(label)}</div>
        <div style="font-size:.78rem;color:#6c757d;">${escHtml(name)}</div>
      </div>
      ${viewButton}
    </div>`;
  }

  let html = `
    <div class="data-section-title"><i class="bi bi-person me-1"></i>Personal and Position</div>
    ${row('Full Name', fullName)}
    ${row('Email', data.email || user.email || '')}
    ${row('Phone', data.phone || '')}
    ${row('Address', data.address || '')}
    ${row('Position', submission.title || call.title || submission.callId)}
    ${row('Department', submission.department || call.department || '')}
    ${row('School', submission.school || call.school || '')}
    ${(submission.courses || call.courses) ? row('Courses', (submission.courses || call.courses).join(', ')) : ''}
    <div class="data-section-title mt-3"><i class="bi bi-mortarboard me-1"></i>Academic and Professional</div>
    ${row('Highest Degree', data.degree || data.education || '')}
    ${row('Institution', data.institution || '')}
    ${row('Field of Specialization', data.specialization || '')}
    ${row('Years of Experience', (data.experience !== undefined && data.experience !== '') ? `${data.experience} yr(s)` : '')}
  `;

  if (data.summary) {
    html += `<div class="data-section-title mt-3"><i class="bi bi-card-text me-1"></i>Professional Summary</div>
      <p style="font-size:.87rem;color:#1a1a2e;white-space:pre-wrap;">${escHtml(data.summary)}</p>`;
  }

  html += `<div class="data-section-title mt-3"><i class="bi bi-paperclip me-1"></i>Documents</div>`;

  if (data.cvFileName) {
    html += fileItem(data.cvFileName, data.cvFileData || data.cvFilePath || null, 'Curriculum Vitae (CV)');
  } else {
    html += `<p class="text-muted" style="font-size:.85rem;">No documents recorded.</p>`;
  }

  if (data.clFileName) {
    html += fileItem(data.clFileName, data.clFileData || null, 'Cover Letter');
  }

  if (Array.isArray(data.supFileNames) && data.supFileNames.length) {
    data.supFileNames.forEach((name, index) => {
      html += fileItem(name, (data.supFilesData && data.supFilesData[index]) || null, `Supporting Document ${index + 1}`);
    });
  }

  return html;
}

function renderApplication(submission) {
  const call = AVAILABLE_CALLS.find(item => String(item.id) === String(submission.callId)) || {};
  const status = submission.status || 'Submitted';

  document.getElementById('contentTitle').textContent = submission.title || call.title || submission.callId;
  document.getElementById('contentSubtitle').textContent = [submission.department || call.department, submission.school || call.school].filter(Boolean).join(' - ');
  document.getElementById('currentBadge').innerHTML = statusBadge(status);
  document.getElementById('statusStepper').innerHTML = buildStepper(status);
  document.getElementById('sumStatus').innerHTML = statusBadge(status);
  document.getElementById('sumSubmitDate').textContent = formatDate(submission.submittedDate);
  document.getElementById('sumLastUpdate').textContent = submission.updatedDate
    ? formatDateTime(submission.updatedDate)
    : ((submission.data && submission.data.savedAt) ? formatDateTime(submission.data.savedAt) : formatDate(submission.submittedDate));
  document.getElementById('statusTimeline').innerHTML = buildTimeline(submission);
  document.getElementById('submittedDataPanel').innerHTML = buildDataPanel(submission);

  document.getElementById('placeholderState').classList.add('d-none');
  document.getElementById('emptyState').classList.add('d-none');
  document.getElementById('statusContent').classList.remove('d-none');
}

document.addEventListener('DOMContentLoaded', function () {
  const selector = document.getElementById('appSelector');
  document.getElementById('totalAppsLabel').textContent = `${SUBMISSIONS.length} submitted`;

  if (SUBMISSIONS.length === 0) {
    document.getElementById('selectorRow').classList.add('d-none');
    document.getElementById('placeholderState').classList.add('d-none');
    document.getElementById('emptyState').classList.remove('d-none');
    return;
  }

  SUBMISSIONS.forEach((submission, index) => {
    const call = AVAILABLE_CALLS.find(item => String(item.id) === String(submission.callId));
    const label = submission.title || (call ? call.title : submission.callId);
    const option = document.createElement('option');
    option.value = String(index);
    option.textContent = `${label} - ${formatDate(submission.submittedDate)}`;
    selector.appendChild(option);
  });

  if (SUBMISSIONS.length === 1) {
    selector.value = '0';
    renderApplication(SUBMISSIONS[0]);
  }

  selector.addEventListener('change', function () {
    const selectedIndex = parseInt(this.value, 10);

    if (Number.isNaN(selectedIndex)) {
      document.getElementById('statusContent').classList.add('d-none');
      document.getElementById('placeholderState').classList.remove('d-none');
      return;
    }

    renderApplication(SUBMISSIONS[selectedIndex]);
  });

  const user = getUserData();
  const navbarUserName = document.getElementById('navbarUserName');
  if (navbarUserName && user.name) {
    navbarUserName.textContent = `${user.name} ${user.surname || ''}`.trim();
  }
});

/* ================================================================
   applicationstatus.js  –  Recruitment Module: Application Status
   Fetches submitted applications from the PHP API.
================================================================= */

const API_BASE = '../../api';

const STAGES = [
  { key: 'Draft',                label: 'Draft',              icon: 'bi-pencil-square' },
  { key: 'Submitted',            label: 'Submitted',          icon: 'bi-send-fill' },
  { key: 'Under Review',         label: 'Under\nReview',      icon: 'bi-search' },
  { key: 'Evaluation Completed', label: 'Evaluation\nCompleted', icon: 'bi-clipboard-check' },
  { key: 'Approved',             label: 'Approved',           icon: 'bi-patch-check-fill' },
];
const REJECTED_STAGE = { key: 'Rejected', label: 'Rejected', icon: 'bi-x-circle-fill' };

function formatDate(iso) {
  if (!iso) return '—';
  const [y, m, d] = iso.split('-');
  const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return `${mn[parseInt(m, 10) - 1]} ${parseInt(d, 10)}, ${y}`;
}

function formatDateTime(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return `${mn[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}

function escHtml(s) {
  if (!s) return '';
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function stageIndex(status) {
  const idx = STAGES.findIndex(s => s.key === status);
  return idx === -1 ? 1 : idx;
}

function statusBadge(status) {
  const map = {
    'Draft':                ['badge-draft',     'bi-pencil-square'],
    'Submitted':            ['badge-submitted', 'bi-send'],
    'Under Review':         ['badge-reviewing', 'bi-hourglass-split'],
    'Evaluation Completed': ['badge-evaluated', 'bi-clipboard-check'],
    'Approved':             ['badge-approved',  'bi-check-circle-fill'],
    'Rejected':             ['badge-rejected',  'bi-x-circle-fill'],
  };
  const [cls, icon] = map[status] || ['badge-draft', 'bi-circle'];
  return `<span class="badge rounded-pill ${cls}"><i class="bi ${icon} me-1"></i>${status}</span>`;
}

function buildStepper(status) {
  const isRejected = status === 'Rejected';
  const current    = isRejected ? stageIndex('Evaluation Completed') : stageIndex(status);
  const stages     = isRejected ? [...STAGES.slice(0, 4), REJECTED_STAGE] : [...STAGES];

  return stages.map((stage, i) => {
    let cls = i < current ? 'done' : (i === current ? 'current' : '');
    if (isRejected && i === 4)                          cls = 'current rejected-step';
    if (!isRejected && status === 'Approved' && i === 4) cls = 'current approved-step done';
    const labelHtml = stage.label.split('\n').join('<br>');
    return `<div class="status-step ${cls}">
      <div class="step-circle"><i class="bi ${stage.icon}"></i></div>
      <div class="step-label">${labelHtml}</div>
    </div>`;
  }).join('');
}

function buildTimeline(sub) {
  const status = sub.status || 'Submitted';
  const d      = sub.data   || {};
  const events = [
    { dot: 'dot-secondary', icon: 'bi-file-earmark-plus', title: 'Application Created',
      date: d.savedAt ? formatDateTime(d.savedAt) : formatDate(sub.submittedDate),
      desc: 'Application draft was started.' },
    { dot: 'dot-primary',   icon: 'bi-send-fill', title: 'Application Submitted',
      date: formatDate(sub.submittedDate),
      desc: 'Your application was successfully submitted.' },
  ];
  const idx = stageIndex(status);
  if (idx >= 2 || status === 'Rejected')
    events.push({ dot: 'dot-warning', icon: 'bi-search', title: 'Review Started',
      date: '—', desc: 'Your application is currently under review by the committee.' });
  if (idx >= 3 || status === 'Rejected')
    events.push({ dot: 'dot-purple', icon: 'bi-clipboard-check', title: 'Evaluation Completed',
      date: '—', desc: 'The evaluation committee has completed their assessment.' });
  if (status === 'Approved')
    events.push({ dot: 'dot-success', icon: 'bi-patch-check-fill', title: 'Application Approved',
      date: '—', desc: 'Congratulations! Your application has been approved.' });
  if (status === 'Rejected')
    events.push({ dot: 'dot-danger', icon: 'bi-x-circle-fill', title: 'Application Rejected',
      date: '—', desc: 'Unfortunately your application was not selected at this time.' });

  return events.map(ev => `
    <li class="timeline-item">
      <div class="timeline-dot ${ev.dot}"><i class="bi ${ev.icon}" style="font-size:.65rem;"></i></div>
      <div class="timeline-title">${ev.title}</div>
      <div class="timeline-date"><i class="bi bi-clock me-1"></i>${ev.date}</div>
      <div class="timeline-desc">${ev.desc}</div>
    </li>`).join('');
}

function openFileUrl(url) {
  if (!url) return;
  if (url.startsWith('data:')) {
    // Legacy base64 – convert to blob URL
    try {
      const [header, b64] = url.split(',');
      const mime  = header.match(/:(.*?);/)[1];
      const bytes = atob(b64);
      const buf   = new Uint8Array(bytes.length);
      for (let i = 0; i < bytes.length; i++) buf[i] = bytes.charCodeAt(i);
      window.open(URL.createObjectURL(new Blob([buf], { type: mime })), '_blank');
    } catch (e) { alert('Unable to open file.'); }
  } else {
    window.open(url, '_blank');
  }
}

function buildDataPanel(sub) {
  const d      = sub.data     || {};
  const call   = sub.callInfo || {};
  const fullName = (window.CareerTrack?.fullName) || '';

  const row = (label, value) =>
    `<div class="data-row d-flex gap-2 flex-wrap">
       <span class="data-label">${label}</span>
       <span class="data-value">${escHtml(value) || '—'}</span>
     </div>`;

  const fileItem = (name, url, label) => {
    const viewBtn = url
      ? `<button class="btn btn-sm btn-outline-primary btn-view-file" data-url="${escHtml(url)}">
           <i class="bi bi-eye me-1"></i>View
         </button>`
      : `<button class="btn btn-sm btn-outline-secondary btn-view-file" disabled>
           <i class="bi bi-eye me-1"></i>View
         </button>`;
    return `<div class="file-item">
      <i class="bi bi-file-earmark-fill"></i>
      <div>
        <div style="font-weight:600;font-size:.83rem;">${label}</div>
        <div style="font-size:.78rem;color:#6c757d;">${escHtml(name)}</div>
      </div>
      ${viewBtn}
    </div>`;
  };

  let html = `
    <div class="data-section-title"><i class="bi bi-person me-1"></i>Personal &amp; Position</div>
    ${row('Full Name',   fullName)}
    ${row('Email',       window.CareerTrack?.email || '')}
    ${row('Phone',       d.phone || '')}
    ${row('Position',    call.title || '')}
    ${row('Department',  call.department || '')}
    ${row('School',      call.school || '')}
    ${call.courses ? row('Courses', call.courses.join(', ')) : ''}
    <div class="data-section-title mt-3"><i class="bi bi-mortarboard me-1"></i>Academic &amp; Professional</div>
    ${row('Highest Degree',          d.degree || '')}
    ${row('Institution',             d.institution || '')}
    ${row('Field of Specialization', d.specialization || '')}
    ${(d.experience !== undefined && d.experience !== '') ? row('Years of Experience', d.experience + ' yr(s)') : row('Years of Experience', '')}`;

  if (d.summary) {
    html += `<div class="data-section-title mt-3"><i class="bi bi-card-text me-1"></i>Professional Summary</div>
      <p style="font-size:.87rem;color:#1a1a2e;white-space:pre-wrap;">${escHtml(d.summary)}</p>`;
  }

  html += `<div class="data-section-title mt-3"><i class="bi bi-paperclip me-1"></i>Documents</div>`;
  if (d.cvFileName) {
    html += fileItem(d.cvFileName, d.cvFileData || null, 'Curriculum Vitae (CV)');
  } else {
    html += `<p class="text-muted" style="font-size:.85rem;">No documents recorded.</p>`;
  }
  if (d.clFileName)    html += fileItem(d.clFileName, d.clFileData || null, 'Cover Letter');
  if (d.supFileNames?.length) {
    d.supFileNames.forEach((name, i) => {
      html += fileItem(name, d.supFilesData?.[i] || null, `Supporting Document ${i + 1}`);
    });
  }
  return html;
}

function renderApplication(sub) {
  const call   = sub.callInfo || {};
  const status = sub.status   || 'Submitted';

  document.getElementById('contentTitle').textContent    = call.title || sub.callId;
  document.getElementById('contentSubtitle').textContent = [call.department, call.school].filter(Boolean).join(' · ');
  document.getElementById('currentBadge').innerHTML      = statusBadge(status);
  document.getElementById('statusStepper').innerHTML     = buildStepper(status);
  document.getElementById('sumStatus').innerHTML         = statusBadge(status);
  document.getElementById('sumSubmitDate').textContent   = formatDate(sub.submittedDate);
  document.getElementById('sumLastUpdate').textContent   = sub.data?.savedAt
    ? formatDateTime(sub.data.savedAt) : formatDate(sub.submittedDate);
  document.getElementById('statusTimeline').innerHTML    = buildTimeline(sub);
  document.getElementById('submittedDataPanel').innerHTML = buildDataPanel(sub);

  // Bind view-file buttons
  document.querySelectorAll('.btn-view-file[data-url]').forEach(btn => {
    btn.addEventListener('click', () => openFileUrl(btn.dataset.url));
  });

  document.getElementById('placeholderState').classList.add('d-none');
  document.getElementById('emptyState').classList.add('d-none');
  document.getElementById('statusContent').classList.remove('d-none');
}

document.addEventListener('DOMContentLoaded', async function () {
  const selector = document.getElementById('appSelector');

  try {
    const res  = await fetch(`${API_BASE}/applications.php`);
    const data = await res.json();
    const subs = data.success
      ? data.applications.filter(a => a.status !== 'Draft')
      : [];

    document.getElementById('totalAppsLabel').textContent = `${subs.length} submitted`;

    if (!subs.length) {
      document.getElementById('selectorRow').classList.add('d-none');
      document.getElementById('placeholderState').classList.add('d-none');
      document.getElementById('emptyState').classList.remove('d-none');
      return;
    }

    subs.forEach((sub, i) => {
      const label = sub.callInfo?.title || sub.callId;
      const opt   = document.createElement('option');
      opt.value   = i;
      opt.textContent = `${label} — ${formatDate(sub.submittedDate)}`;
      selector.appendChild(opt);
    });

    if (subs.length === 1) {
      selector.value = '0';
      renderApplication(subs[0]);
    }

    selector.addEventListener('change', function () {
      const idx = parseInt(this.value, 10);
      if (isNaN(idx)) {
        document.getElementById('statusContent').classList.add('d-none');
        document.getElementById('placeholderState').classList.remove('d-none');
      } else {
        renderApplication(subs[idx]);
      }
    });
  } catch (e) {
    console.error('Failed to load applications', e);
  }
});

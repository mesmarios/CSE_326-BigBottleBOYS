const BOOTSTRAP = window.APPLICATIONSTATUS_BOOTSTRAP || {};

const DEFAULT_CALLS = [
  { id:'CALL-2026-001', title:'Lecturer in Computer Science', department:'Department of Computer Science', school:'School of Engineering & Applied Sciences', courses:['CS101 – Introduction to Programming','CS201 – Data Structures','CS305 – Algorithms'], startDate:'2026-01-15', endDate:'2026-03-31' },
  { id:'CALL-2026-002', title:'Assistant Professor in Mathematics', department:'Department of Mathematics', school:'School of Natural Sciences', courses:['MATH101 – Calculus I','MATH201 – Linear Algebra','MATH302 – Probability & Statistics'], startDate:'2026-02-01', endDate:'2026-04-15' },
  { id:'CALL-2026-003', title:'Adjunct Instructor – Business Administration', department:'Department of Business & Management', school:'School of Economics & Business', courses:['BUS101 – Principles of Management','BUS210 – Marketing Fundamentals'], startDate:'2026-01-20', endDate:'2026-02-28' },
  { id:'CALL-2026-004', title:'Research Associate – Environmental Studies', department:'Department of Environmental Sciences', school:'School of Natural Sciences', courses:['ENV201 – Environmental Policy','ENV303 – Climate Change & Society'], startDate:'2026-03-01', endDate:'2026-05-30' },
];

const AVAILABLE_CALLS = Array.isArray(BOOTSTRAP.calls) && BOOTSTRAP.calls.length
  ? BOOTSTRAP.calls.map(call => ({
      ...call,
      id: String(call.id),
      courses: Array.isArray(call.courses) ? call.courses : [call.courses || '—'],
    }))
  : DEFAULT_CALLS;

const SERVER_USER = BOOTSTRAP.user && Object.keys(BOOTSTRAP.user).length ? BOOTSTRAP.user : null;
let serverSubmissions = Array.isArray(BOOTSTRAP.submissions)
  ? BOOTSTRAP.submissions.map(sub => ({ ...sub, callId: String(sub.callId) }))
  : null;

function formatDate(iso) {
  if (!iso) return '—';
  const [y,m,d] = iso.split('-');
  const mn=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return `${mn[parseInt(m,10)-1]} ${parseInt(d,10)}, ${y}`;
}
function formatDateTime(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  const mn=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return `${mn[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}
function getSubmissions() {
  if (Array.isArray(serverSubmissions)) return serverSubmissions;
  const s = localStorage.getItem('submittedApplications');
  return s ? JSON.parse(s) : [];
}

function getUserData() {
  if (SERVER_USER) return SERVER_USER;
  try {
    return JSON.parse(localStorage.getItem('userProfileData')) || {};
  } catch (e) {
    return {};
  }
}
function escHtml(s) {
  if (!s) return '';
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

const STAGES = [
  { key:'Draft',                label:'Draft',              icon:'bi-pencil-square' },
  { key:'Submitted',            label:'Submitted',          icon:'bi-send-fill' },
  { key:'Under Review',         label:'Under\nReview',      icon:'bi-search' },
  { key:'Evaluation Completed', label:'Evaluation\nCompleted', icon:'bi-clipboard-check' },
  { key:'Approved',             label:'Approved',           icon:'bi-patch-check-fill' },
];
const REJECTED_STAGE = { key:'Rejected', label:'Rejected', icon:'bi-x-circle-fill' };

function stageIndex(status) {
  const idx = STAGES.findIndex(s => s.key === status);
  return idx === -1 ? 1 : idx;
}

function statusBadge(status) {
  const map = {
    'Draft':['badge-draft','bi-pencil-square'],
    'Submitted':['badge-submitted','bi-send'],
    'Under Review':['badge-reviewing','bi-hourglass-split'],
    'Evaluation Completed':['badge-evaluated','bi-clipboard-check'],
    'Approved':['badge-approved','bi-check-circle-fill'],
    'Rejected':['badge-rejected','bi-x-circle-fill'],
  };
  const [cls,icon] = map[status] || ['badge-draft','bi-circle'];
  return `<span class="badge rounded-pill ${cls}"><i class="bi ${icon} me-1"></i>${status}</span>`;
}

function buildStepper(status) {
  const isRejected = status === 'Rejected';
  const current    = isRejected ? stageIndex('Evaluation Completed') : stageIndex(status);
  const stages     = isRejected ? [...STAGES.slice(0,4), REJECTED_STAGE] : [...STAGES];

  return stages.map((stage, i) => {
    let cls = i < current ? 'done' : (i === current ? 'current' : '');
    if (isRejected && i === 4)                      cls = 'current rejected-step';
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
  const d      = sub.data || {};
  const events = [
    { dot:'dot-secondary', icon:'bi-file-earmark-plus', title:'Application Created',
      date: d.savedAt ? formatDateTime(d.savedAt) : formatDate(sub.submittedDate),
      desc:'Application record was created.' },
    { dot:'dot-primary', icon:'bi-send-fill', title:'Application Submitted',
      date: formatDate(sub.submittedDate),
      desc:'Your application was successfully submitted.' },
  ];
  const idx = stageIndex(status);
  if (idx >= 2 || status === 'Rejected')
    events.push({ dot:'dot-warning', icon:'bi-search', title:'Review Started', date: sub.reviewedDate ? formatDateTime(sub.reviewedDate) : '—', desc:'Your application is currently under review by the committee.' });
  if (idx >= 3 || status === 'Rejected')
    events.push({ dot:'dot-purple', icon:'bi-clipboard-check', title:'Evaluation Completed', date: sub.updatedDate ? formatDateTime(sub.updatedDate) : '—', desc:'The evaluation committee has completed their assessment.' });
  if (status === 'Approved')
    events.push({ dot:'dot-success', icon:'bi-patch-check-fill', title:'Application Approved', date: sub.updatedDate ? formatDateTime(sub.updatedDate) : '—', desc:'Congratulations! Your application has been approved.' });
  if (status === 'Rejected')
    events.push({ dot:'dot-danger', icon:'bi-x-circle-fill', title:'Application Rejected', date: sub.updatedDate ? formatDateTime(sub.updatedDate) : '—', desc:'Unfortunately your application was not selected at this time.' });

  return events.map(ev => `
    <li class="timeline-item">
      <div class="timeline-dot ${ev.dot}"><i class="bi ${ev.icon}" style="font-size:.65rem;"></i></div>
      <div class="timeline-title">${ev.title}</div>
      <div class="timeline-date"><i class="bi bi-clock me-1"></i>${ev.date}</div>
      <div class="timeline-desc">${ev.desc}</div>
    </li>`).join('');
}

function openFile(encodedUrl) {
  try {
    const dataUrl = decodeURIComponent(encodedUrl);
    const [header, b64] = dataUrl.split(',');
    const mime  = header.match(/:(.*?);/)[1];
    const bytes = atob(b64);
    const buf   = new Uint8Array(bytes.length);
    for (let i = 0; i < bytes.length; i++) buf[i] = bytes.charCodeAt(i);
    window.open(URL.createObjectURL(new Blob([buf], {type:mime})), '_blank');
  } catch(e) { alert('Unable to open file.'); }
}

function buildDataPanel(sub) {
  const d    = sub.data || {};
  const call = AVAILABLE_CALLS.find(c => String(c.id) === String(sub.callId)) || {};
  const user = getUserData();
  const fullName = d.fullName || [user.name, user.surname].filter(Boolean).join(' ') || '—';

  const row = (label, value) =>
    `<div class="data-row d-flex gap-2 flex-wrap">
       <span class="data-label">${label}</span>
       <span class="data-value">${escHtml(value) || '—'}</span>
     </div>`;

  const fileItem = (name, dataUrl, label) => {
    const viewBtn = dataUrl
      ? `<button class="btn btn-sm btn-outline-primary btn-view-file" onclick="openFile('${encodeURIComponent(dataUrl)}')"><i class="bi bi-eye me-1"></i>View</button>`
      : `<button class="btn btn-sm btn-outline-secondary btn-view-file" disabled title="File data not available"><i class="bi bi-eye me-1"></i>View</button>`;
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
    ${row('Email',       d.email || user.email || '')}
    ${row('Phone',       d.phone || '')}
    ${row('Address',     d.address || '')}
    ${row('Position',    sub.title || call.title || sub.callId)}
    ${row('Department',  sub.department || call.department || '')}
    ${row('School',      sub.school || call.school || '')}
    ${(sub.courses || call.courses) ? row('Courses', (sub.courses || call.courses).join(', ')) : ''}
    <div class="data-section-title mt-3"><i class="bi bi-mortarboard me-1"></i>Academic &amp; Professional</div>
    ${row('Highest Degree',            d.degree || d.education || '')}
    ${row('Institution',               d.institution || '')}
    ${row('Field of Specialization',   d.specialization || '')}
    ${row('Years of Experience',       (d.experience !== undefined && d.experience !== '') ? d.experience + ' yr(s)' : '')}`;

  if (d.summary) {
    html += `<div class="data-section-title mt-3"><i class="bi bi-card-text me-1"></i>Professional Summary</div>
      <p style="font-size:.87rem;color:#1a1a2e;white-space:pre-wrap;">${escHtml(d.summary)}</p>`;
  }

  html += `<div class="data-section-title mt-3"><i class="bi bi-paperclip me-1"></i>Documents</div>`;
  if (d.cvFileName) {
    html += fileItem(d.cvFileName, d.cvFileData || d.cvFilePath || null, 'Curriculum Vitae (CV)');
  } else {
    html += `<p class="text-muted" style="font-size:.85rem;">No documents recorded.</p>`;
  }
  if (d.clFileName)  html += fileItem(d.clFileName, d.clFileData || null, 'Cover Letter');
  if (d.supFileNames && d.supFileNames.length) {
    d.supFileNames.forEach((name,i) => {
      html += fileItem(name, (d.supFilesData && d.supFilesData[i]) || null, `Supporting Document ${i+1}`);
    });
  }
  return html;
}

function renderApplication(sub) {
  const call   = AVAILABLE_CALLS.find(c => String(c.id) === String(sub.callId)) || {};
  const status = sub.status || 'Submitted';

  document.getElementById('contentTitle').textContent     = sub.title || call.title || sub.callId;
  document.getElementById('contentSubtitle').textContent  = [sub.department || call.department, sub.school || call.school].filter(Boolean).join(' · ');
  document.getElementById('currentBadge').innerHTML       = statusBadge(status);
  document.getElementById('statusStepper').innerHTML      = buildStepper(status);
  document.getElementById('sumStatus').innerHTML          = statusBadge(status);
  document.getElementById('sumSubmitDate').textContent    = formatDate(sub.submittedDate);
  document.getElementById('sumLastUpdate').textContent    = sub.updatedDate ? formatDateTime(sub.updatedDate) : (sub.data && sub.data.savedAt ? formatDateTime(sub.data.savedAt) : formatDate(sub.submittedDate));
  document.getElementById('statusTimeline').innerHTML     = buildTimeline(sub);
  document.getElementById('submittedDataPanel').innerHTML = buildDataPanel(sub);

  document.getElementById('placeholderState').classList.add('d-none');
  document.getElementById('emptyState').classList.add('d-none');
  document.getElementById('statusContent').classList.remove('d-none');
}

document.addEventListener('DOMContentLoaded', function () {
  const subs     = getSubmissions();
  const selector = document.getElementById('appSelector');
  document.getElementById('totalAppsLabel').textContent = `${subs.length} submitted`;

  if (subs.length === 0) {
    document.getElementById('selectorRow').classList.add('d-none');
    document.getElementById('placeholderState').classList.add('d-none');
    document.getElementById('emptyState').classList.remove('d-none');
    return;
  }

  subs.forEach((sub, i) => {
    const call  = AVAILABLE_CALLS.find(c => String(c.id) === String(sub.callId));
    const label = sub.title || (call ? call.title : sub.callId);
    const opt   = document.createElement('option');
    opt.value   = i;
    opt.textContent = `${label} — ${formatDate(sub.submittedDate)}`;
    selector.appendChild(opt);
  });

  if (subs.length === 1) { selector.value = '0'; renderApplication(subs[0]); }

  selector.addEventListener('change', function () {
    const idx = parseInt(this.value, 10);
    if (isNaN(idx)) {
      document.getElementById('statusContent').classList.add('d-none');
      document.getElementById('placeholderState').classList.remove('d-none');
    } else {
      renderApplication(subs[idx]);
    }
  });

  const u = getUserData();
  const el = document.getElementById('navbarUserName');
  if (el && u.name) el.textContent = `${u.name} ${u.surname || ''}`.trim();
});

<?php include('../../includes/layout.php'); include('../../includes/header.php'); include('../../includes/nav.php'); ?>
<style>
/* ── Application Status page ──────────────────────────────────────── */
.section-heading {
  font-size: 1rem; font-weight: 700; color: #1a1a2e; letter-spacing: .01em;
  border-left: 4px solid #0d6efd; padding-left: .6rem; margin-bottom: 0;
}
.badge-draft        { background:#e9ecef; color:#495057; }
.badge-submitted    { background:#cfe2ff; color:#084298; }
.badge-reviewing    { background:#fff3cd; color:#664d03; }
.badge-evaluated    { background:#e0cffc; color:#3b0764; }
.badge-approved     { background:#d1e7dd; color:#0a3622; }
.badge-rejected     { background:#f8d7da; color:#58151c; }

.selector-card { border:none; border-radius:.75rem; box-shadow:0 2px 12px rgba(0,0,0,.07); }

.status-stepper {
  display:flex; align-items:flex-start; justify-content:center;
  padding:2rem 1.5rem 1.5rem; overflow-x:auto;
}
.status-step {
  display:flex; flex-direction:column; align-items:center;
  flex:1; position:relative; min-width:70px;
}
.status-step:not(:last-child)::after {
  content:''; position:absolute; top:20px;
  left:calc(50% + 22px); right:calc(-50% + 22px);
  height:2px; background:#dee2e6; z-index:0;
}
.status-step.done:not(:last-child)::after    { background:#0d6efd; }
.step-circle {
  width:42px; height:42px; border-radius:50%; display:flex;
  align-items:center; justify-content:center; font-size:1.05rem;
  font-weight:700; border:2px solid #dee2e6; background:#f8f9fa;
  color:#adb5bd; position:relative; z-index:1; transition:all .25s ease;
}
.status-step.done    .step-circle { background:#0d6efd; border-color:#0d6efd; color:#fff; }
.status-step.current .step-circle {
  background:#fff; border-color:#0d6efd; color:#0d6efd;
  box-shadow:0 0 0 4px rgba(13,110,253,.15);
}
.status-step.approved-step.current .step-circle,
.status-step.approved-step.done   .step-circle { background:#198754; border-color:#198754; color:#fff; }
.status-step.rejected-step.current .step-circle {
  background:#dc3545; border-color:#dc3545; color:#fff;
  box-shadow:0 0 0 4px rgba(220,53,69,.15);
}
.step-label {
  font-size:.72rem; font-weight:600; color:#adb5bd;
  text-align:center; margin-top:.5rem; line-height:1.25; max-width:80px;
}
.status-step.done    .step-label { color:#0d6efd; }
.status-step.current .step-label { color:#212529; font-weight:700; }
.status-step.approved-step.done    .step-label,
.status-step.approved-step.current .step-label { color:#198754; }
.status-step.rejected-step.current .step-label { color:#dc3545; }

.summary-card { border:none; border-radius:.75rem; box-shadow:0 2px 12px rgba(0,0,0,.07); }
.summary-stat { border-right:1px solid #e9ecef; padding:1rem 1.5rem; }
.summary-stat:last-child { border-right:none; }
.summary-stat .stat-label {
  font-size:.75rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.05em; color:#6c757d; margin-bottom:.25rem;
}
.summary-stat .stat-value { font-size:1rem; font-weight:700; color:#1a1a2e; }

.timeline { list-style:none; padding:0; margin:0; position:relative; }
.timeline::before {
  content:''; position:absolute; left:18px; top:0; bottom:0;
  width:2px; background:#e9ecef;
}
.timeline-item { position:relative; padding:0 0 1.25rem 3.25rem; }
.timeline-item:last-child { padding-bottom:0; }
.timeline-dot {
  position:absolute; left:8px; top:2px; width:22px; height:22px;
  border-radius:50%; display:flex; align-items:center; justify-content:center;
  font-size:.65rem; z-index:1; border:2px solid #fff;
}
.timeline-dot.dot-primary  { background:#0d6efd; color:#fff; }
.timeline-dot.dot-success  { background:#198754; color:#fff; }
.timeline-dot.dot-warning  { background:#ffc107; color:#664d03; }
.timeline-dot.dot-purple   { background:#6f42c1; color:#fff; }
.timeline-dot.dot-danger   { background:#dc3545; color:#fff; }
.timeline-dot.dot-secondary{ background:#adb5bd; color:#fff; }
.timeline-title { font-size:.88rem; font-weight:700; color:#1a1a2e; line-height:1.3; }
.timeline-date  { font-size:.78rem; color:#6c757d; margin-top:.1rem; }
.timeline-desc  { font-size:.82rem; color:#495057; margin-top:.2rem; }

.data-section-title {
  font-size:.78rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.06em; color:#6c757d;
  border-bottom:1px solid #e9ecef; padding-bottom:.4rem; margin-bottom:.75rem;
}
.data-row { margin-bottom:.6rem; font-size:.88rem; }
.data-label { font-weight:600; color:#495057; min-width:160px; }
.data-value { color:#1a1a2e; }

.file-item {
  display:flex; align-items:center; gap:.6rem; padding:.55rem .75rem;
  border:1px solid #e9ecef; border-radius:.5rem; margin-bottom:.45rem;
  font-size:.85rem; background:#f8f9fa;
}
.file-item .bi-file-earmark-fill { color:#0d6efd; font-size:1rem; }
.file-item .btn-view-file { margin-left:auto; font-size:.78rem; padding:.2rem .65rem; white-space:nowrap; }

.empty-state { text-align:center; padding:4rem 1rem; color:#adb5bd; }
.empty-state i { font-size:3.5rem; margin-bottom:1rem; display:block; }
.empty-state h5 { font-size:1.05rem; color:#6c757d; font-weight:600; }
.empty-state p  { font-size:.88rem; color:#adb5bd; max-width:360px; margin:.35rem auto 0; }

@media (max-width:576px) {
  .status-stepper { padding:1.25rem .25rem 1rem; }
  .step-label     { font-size:.62rem; max-width:60px; }
  .step-circle    { width:34px; height:34px; font-size:.85rem; }
  .status-step:not(:last-child)::after { top:17px; }
  .summary-stat   { border-right:none; border-bottom:1px solid #e9ecef; }
  .summary-stat:last-child { border-bottom:none; }
}
</style>

<!--begin::App Main-->
<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Application Status</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Application Status</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">

      <!-- Selector -->
      <div class="row mb-4" id="selectorRow">
        <div class="col-12">
          <div class="card selector-card">
            <div class="card-body py-3">
              <div class="row align-items-center g-3">
                <div class="col-auto"><span class="section-heading">Select Application</span></div>
                <div class="col-md-5 col-lg-4">
                  <select class="form-select form-select-sm" id="appSelector">
                    <option value="">— Choose a submitted application —</option>
                  </select>
                </div>
                <div class="col-auto ms-md-auto">
                  <span class="badge rounded-pill bg-light text-secondary border" id="totalAppsLabel" style="font-size:.8rem;">0 submitted</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div id="emptyState" class="d-none">
        <div class="card border-0 shadow-sm" style="border-radius:.75rem;">
          <div class="card-body">
            <div class="empty-state">
              <i class="bi bi-inbox"></i>
              <h5>No Submitted Applications</h5>
              <p>You have not submitted any applications yet. Head over to <strong>My Application</strong> to apply for an open position.</p>
              <a href="./myapplication.php" class="btn btn-primary btn-sm mt-3" style="font-size:.75rem; padding:.25rem .65rem;">
                <i class="bi bi-send me-1"></i>Browse Open Calls
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Placeholder -->
      <div id="placeholderState">
        <div class="card border-0 shadow-sm" style="border-radius:.75rem;">
          <div class="card-body">
            <div class="empty-state">
              <i class="bi bi-arrow-up-circle" style="color:#0d6efd; font-size:2rem;"></i>
              <h5 style="color:#495057;">Select an Application</h5>
              <p>Use the dropdown above to view the status and details of one of your submitted applications.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Status content -->
      <div id="statusContent" class="d-none">

        <!-- Stepper card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius:.75rem;">
          <div class="card-header bg-white" style="border-radius:.75rem .75rem 0 0;border-bottom:1px solid #e9ecef;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
              <span class="section-heading" id="contentTitle">—</span>
              <span id="currentBadge"></span>
            </div>
            <div class="text-muted mt-1" id="contentSubtitle" style="font-size:.82rem;"></div>
          </div>
          <div class="card-body p-0">
            <div class="status-stepper" id="statusStepper"></div>
          </div>
        </div>

        <!-- Summary -->
        <div class="card border-0 shadow-sm mb-4 summary-card" style="border-radius:.75rem;">
          <div class="card-body p-0">
            <div class="row g-0 flex-wrap">
              <div class="col-sm-4 summary-stat">
                <div class="stat-label">Current Status</div>
                <div class="stat-value" id="sumStatus">—</div>
              </div>
              <div class="col-sm-4 summary-stat">
                <div class="stat-label">Submission Date</div>
                <div class="stat-value" id="sumSubmitDate">—</div>
              </div>
              <div class="col-sm-4 summary-stat">
                <div class="stat-label">Last Updated</div>
                <div class="stat-value" id="sumLastUpdate">—</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Timeline + Data -->
        <div class="row g-4">
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:.75rem;">
              <div class="card-header bg-white" style="border-radius:.75rem .75rem 0 0;border-bottom:1px solid #e9ecef;padding:.85rem 1.25rem;">
                <span class="section-heading">Status History</span>
              </div>
              <div class="card-body">
                <ul class="timeline" id="statusTimeline"></ul>
              </div>
            </div>
          </div>
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:.75rem;">
              <div class="card-header bg-white" style="border-radius:.75rem .75rem 0 0;border-bottom:1px solid #e9ecef;padding:.85rem 1.25rem;">
                <span class="section-heading">Submitted Application Data</span>
              </div>
              <div class="card-body" id="submittedDataPanel"></div>
            </div>
          </div>
        </div>

      </div><!-- /#statusContent -->

    </div>
  </div>
</main>

<?php include('../../includes/footer.php'); ?>

<script>
const AVAILABLE_CALLS = [
  { id:'CALL-2026-001', title:'Lecturer in Computer Science', department:'Department of Computer Science', school:'School of Engineering & Applied Sciences', courses:['CS101 – Introduction to Programming','CS201 – Data Structures','CS305 – Algorithms'], startDate:'2026-01-15', endDate:'2026-03-31' },
  { id:'CALL-2026-002', title:'Assistant Professor in Mathematics', department:'Department of Mathematics', school:'School of Natural Sciences', courses:['MATH101 – Calculus I','MATH201 – Linear Algebra','MATH302 – Probability & Statistics'], startDate:'2026-02-01', endDate:'2026-04-15' },
  { id:'CALL-2026-003', title:'Adjunct Instructor – Business Administration', department:'Department of Business & Management', school:'School of Economics & Business', courses:['BUS101 – Principles of Management','BUS210 – Marketing Fundamentals'], startDate:'2026-01-20', endDate:'2026-02-28' },
  { id:'CALL-2026-004', title:'Research Associate – Environmental Studies', department:'Department of Environmental Sciences', school:'School of Natural Sciences', courses:['ENV201 – Environmental Policy','ENV303 – Climate Change & Society'], startDate:'2026-03-01', endDate:'2026-05-30' },
];

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
  const s = localStorage.getItem('submittedApplications');
  return s ? JSON.parse(s) : [];
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
      desc:'Application draft was started.' },
    { dot:'dot-primary', icon:'bi-send-fill', title:'Application Submitted',
      date: formatDate(sub.submittedDate),
      desc:'Your application was successfully submitted.' },
  ];
  const idx = stageIndex(status);
  if (idx >= 2 || status === 'Rejected')
    events.push({ dot:'dot-warning', icon:'bi-search', title:'Review Started', date:'—', desc:'Your application is currently under review by the committee.' });
  if (idx >= 3 || status === 'Rejected')
    events.push({ dot:'dot-purple', icon:'bi-clipboard-check', title:'Evaluation Completed', date:'—', desc:'The evaluation committee has completed their assessment.' });
  if (status === 'Approved')
    events.push({ dot:'dot-success', icon:'bi-patch-check-fill', title:'Application Approved', date:'—', desc:'Congratulations! Your application has been approved.' });
  if (status === 'Rejected')
    events.push({ dot:'dot-danger', icon:'bi-x-circle-fill', title:'Application Rejected', date:'—', desc:'Unfortunately your application was not selected at this time.' });

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
  const call = AVAILABLE_CALLS.find(c => c.id === sub.callId) || {};
  const user = (() => { try { return JSON.parse(localStorage.getItem('userProfileData')) || {}; } catch { return {}; } })();
  const fullName = [user.name, user.surname].filter(Boolean).join(' ') || '—';

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
    ${row('Email',       user.email || '')}
    ${row('Phone',       d.phone || '')}
    ${row('Position',    call.title || sub.callId)}
    ${row('Department',  call.department || '')}
    ${row('School',      call.school     || '')}
    ${call.courses ? row('Courses', call.courses.join(', ')) : ''}
    <div class="data-section-title mt-3"><i class="bi bi-mortarboard me-1"></i>Academic &amp; Professional</div>
    ${row('Highest Degree',            d.degree || '')}
    ${row('Institution',               d.institution || '')}
    ${row('Field of Specialization',   d.specialization || '')}
    ${row('Years of Experience',       (d.experience !== undefined && d.experience !== '') ? d.experience + ' yr(s)' : '')}`;

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
  if (d.clFileName)  html += fileItem(d.clFileName, d.clFileData || null, 'Cover Letter');
  if (d.supFileNames && d.supFileNames.length) {
    d.supFileNames.forEach((name,i) => {
      html += fileItem(name, (d.supFilesData && d.supFilesData[i]) || null, `Supporting Document ${i+1}`);
    });
  }
  return html;
}

function renderApplication(sub) {
  const call   = AVAILABLE_CALLS.find(c => c.id === sub.callId) || {};
  const status = sub.status || 'Submitted';

  document.getElementById('contentTitle').textContent     = call.title || sub.callId;
  document.getElementById('contentSubtitle').textContent  = [call.department, call.school].filter(Boolean).join(' · ');
  document.getElementById('currentBadge').innerHTML       = statusBadge(status);
  document.getElementById('statusStepper').innerHTML      = buildStepper(status);
  document.getElementById('sumStatus').innerHTML          = statusBadge(status);
  document.getElementById('sumSubmitDate').textContent    = formatDate(sub.submittedDate);
  document.getElementById('sumLastUpdate').textContent    = sub.data && sub.data.savedAt ? formatDateTime(sub.data.savedAt) : formatDate(sub.submittedDate);
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
    const call  = AVAILABLE_CALLS.find(c => c.id === sub.callId);
    const label = call ? call.title : sub.callId;
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

  // Sync navbar username
  try {
    const u = JSON.parse(localStorage.getItem('userProfileData')) || {};
    const el = document.getElementById('navbarUserName');
    if (el && u.name) el.textContent = `${u.name} ${u.surname || ''}`.trim();
  } catch(e) {}
});
</script>

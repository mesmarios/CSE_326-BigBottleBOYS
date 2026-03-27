/* ================================================================
   myapplication.js  –  Recruitment Module: My Applications
   All data is fetched from / persisted to the PHP API.
   No localStorage is used for application data.
================================================================= */

const API_BASE = '../../api';

/* ── In-memory state (loaded from server on init) ─────────────── */
let AVAILABLE_CALLS = [];   // published announcements
let myApplications  = [];   // current user's applications

/* ── Helpers ──────────────────────────────────────────────────── */
function formatDate(iso) {
  if (!iso) return '—';
  const [y, m, d] = iso.split('-');
  const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return `${mn[parseInt(m, 10) - 1]} ${parseInt(d, 10)}, ${y}`;
}

function isCallOpen(call) {
  const today = new Date().toISOString().slice(0, 10);
  return today >= call.startDate && today <= call.endDate;
}

function statusBadge(status) {
  const map = {
    'Draft':        ['badge-draft',     'bi-pencil-square'],
    'Submitted':    ['badge-submitted', 'bi-send'],
    'Under Review': ['badge-reviewing', 'bi-hourglass-split'],
    'Approved':     ['badge-approved',  'bi-check-circle-fill'],
    'Rejected':     ['badge-rejected',  'bi-x-circle-fill'],
  };
  const [cls, icon] = map[status] || ['badge-draft', 'bi-circle'];
  return `<span class="badge rounded-pill ${cls}"><i class="bi ${icon} me-1"></i>${status}</span>`;
}

function showToast(msg, type = 'primary') {
  const el   = document.getElementById('appToast');
  const msgEl = document.getElementById('appToastMsg');
  msgEl.textContent = msg;
  el.className = `toast align-items-center text-bg-${type} border-0`;
  bootstrap.Toast.getOrCreateInstance(el, { delay: 3500 }).show();
}

/* ── API calls ────────────────────────────────────────────────── */
async function loadAnnouncements() {
  try {
    const res  = await fetch(`${API_BASE}/announcements.php`);
    const data = await res.json();
    if (data.success) AVAILABLE_CALLS = data.announcements;
  } catch (e) {
    console.error('Failed to load announcements', e);
  }
}

async function loadApplications() {
  try {
    const res  = await fetch(`${API_BASE}/applications.php`);
    const data = await res.json();
    if (data.success) myApplications = data.applications;
  } catch (e) {
    console.error('Failed to load applications', e);
  }
}

/* ── Render: Available Calls ──────────────────────────────────── */
function renderCalls() {
  const container    = document.getElementById('callsContainer');
  const submittedIds = myApplications
    .filter(a => a.status !== 'Draft')
    .map(a => a.callId);
  const draftIds = myApplications
    .filter(a => a.status === 'Draft')
    .map(a => a.callId);

  container.innerHTML = '';

  if (!AVAILABLE_CALLS.length) {
    container.innerHTML = '<div class="col-12 text-center text-muted py-4">No open positions at this time.</div>';
    return;
  }

  AVAILABLE_CALLS.forEach(call => {
    const open             = isCallOpen(call);
    const alreadySubmitted = submittedIds.includes(call.id);
    const hasDraft         = draftIds.includes(call.id);

    let actionBtn = '';
    if (alreadySubmitted) {
      actionBtn = `<span class="badge text-bg-success px-3 py-2" style="font-size:.8rem;">
                     <i class="bi bi-check-lg me-1"></i>Applied
                   </span>`;
    } else if (!open) {
      actionBtn = `<button class="btn btn-sm btn-secondary" disabled>
                     <i class="bi bi-lock me-1"></i>Closed
                   </button>`;
    } else if (hasDraft) {
      actionBtn = `<button class="btn btn-sm btn-warning fw-semibold open-wizard" data-call-id="${call.id}">
                     <i class="bi bi-pencil-square me-1"></i>Continue Application
                   </button>`;
    } else {
      actionBtn = `<button class="btn btn-sm btn-primary fw-semibold open-wizard" data-call-id="${call.id}">
                     <i class="bi bi-send me-1"></i>Apply
                   </button>`;
    }

    const statusHtml = open
      ? `<span class="badge text-bg-success"><i class="bi bi-circle-fill me-1" style="font-size:.5rem;vertical-align:middle;"></i>Open</span>`
      : `<span class="badge text-bg-danger"><i class="bi bi-circle-fill me-1"  style="font-size:.5rem;vertical-align:middle;"></i>Closed</span>`;

    container.insertAdjacentHTML('beforeend', `
      <div class="col-12 col-lg-6">
        <div class="card call-card h-100">
          <div class="card-header d-flex justify-content-between align-items-start gap-2">
            <h6 class="lh-sm">${call.title}</h6>
            ${statusHtml}
          </div>
          <div class="card-body d-flex flex-column gap-2 py-3">
            <div class="call-meta"><i class="bi bi-building me-2 text-primary"></i>${call.department}</div>
            <div class="call-meta"><i class="bi bi-mortarboard me-2 text-primary"></i>${call.school}</div>
            <div class="call-meta">
              <i class="bi bi-journal-bookmark me-2 text-primary"></i>
              <span class="call-courses">${call.courses.join(' &bull; ')}</span>
            </div>
            <div class="call-meta">
              <i class="bi bi-calendar-range me-2 text-primary"></i>
              ${formatDate(call.startDate)} &ndash; ${formatDate(call.endDate)}
            </div>
          </div>
          <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-3 d-flex justify-content-end">
            ${actionBtn}
          </div>
        </div>
      </div>
    `);
  });

  container.querySelectorAll('.open-wizard').forEach(btn => {
    btn.addEventListener('click', () => openWizard(parseInt(btn.dataset.callId, 10)));
  });
}

/* ── Render: My Applications Table ───────────────────────────── */
function renderMyApplications() {
  const tbody = document.getElementById('myApplicationsBody');
  const noRow = document.getElementById('noApplicationsRow');

  tbody.querySelectorAll('tr.dyn-row').forEach(r => r.remove());

  if (!myApplications.length) { noRow.style.display = ''; return; }
  noRow.style.display = 'none';

  myApplications.forEach(app => {
    const callInfo = AVAILABLE_CALLS.find(c => c.id === app.callId) || app.callInfo || {};
    const isDraft  = app.status === 'Draft';

    const btn = isDraft
      ? `<div class="d-flex align-items-center justify-content-end gap-2">
           <button class="btn btn-sm btn-warning open-wizard" data-call-id="${app.callId}">
             <i class="bi bi-pencil-square me-1"></i>Continue
           </button>
           <button class="btn btn-sm btn-outline-danger delete-draft" data-call-id="${app.callId}" title="Delete draft">
             <i class="bi bi-x-lg"></i>
           </button>
         </div>`
      : `<button class="btn btn-sm btn-outline-secondary view-app" data-call-id="${app.callId}">
           <i class="bi bi-eye me-1"></i>View
         </button>`;

    tbody.insertAdjacentHTML('beforeend', `
      <tr class="dyn-row">
        <td class="fw-semibold">${callInfo.title || app.callId}</td>
        <td>${callInfo.department || '—'}</td>
        <td>${app.submittedDate ? formatDate(app.submittedDate) : '<span class="text-muted">—</span>'}</td>
        <td>${statusBadge(app.status)}</td>
        <td class="text-end">${btn}</td>
      </tr>
    `);
  });

  tbody.querySelectorAll('.open-wizard').forEach(b =>
    b.addEventListener('click', () => openWizard(parseInt(b.dataset.callId, 10))));
  tbody.querySelectorAll('.view-app').forEach(b =>
    b.addEventListener('click', () => openWizard(parseInt(b.dataset.callId, 10), true)));
  tbody.querySelectorAll('.delete-draft').forEach(b =>
    b.addEventListener('click', () => deleteDraft(parseInt(b.dataset.callId, 10))));
}

/* ── Delete Draft ─────────────────────────────────────────────── */
async function deleteDraft(callId) {
  if (!confirm('Delete this draft? This cannot be undone.')) return;
  try {
    await fetch(`${API_BASE}/applications.php?action=delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ announcement_id: callId }),
    });
    await loadApplications();
    renderCalls();
    renderMyApplications();
  } catch (e) {
    showToast('Could not delete draft.', 'danger');
  }
}

/* ── Wizard State ─────────────────────────────────────────────── */
let currentCallId = null;
let currentStep   = 1;
const TOTAL_STEPS = 4;
let uploadedFiles     = { cv: null, cl: null, supporting: [] };
let uploadedFilesData = { cv: null, cl: null, supporting: [] };
let isReadonly        = false;

function openWizard(callId, viewOnly = false) {
  // Find call info – check available calls first, then embedded callInfo
  let call = AVAILABLE_CALLS.find(c => c.id === callId);
  if (!call) {
    const appRecord = myApplications.find(a => a.callId === callId);
    call = appRecord ? appRecord.callInfo : null;
  }
  if (!call) return;

  const appRecord = myApplications.find(a => a.callId === callId);
  isReadonly      = viewOnly || (appRecord && appRecord.status !== 'Draft');
  currentCallId   = callId;
  currentStep     = 1;
  uploadedFiles     = { cv: null, cl: null, supporting: [] };
  uploadedFilesData = { cv: null, cl: null, supporting: [] };

  document.getElementById('modalCallSubtitle').textContent = `${call.title} — ${call.department}`;

  const saved = appRecord ? (appRecord.data || {}) : {};

  // Step 1
  const navUser = (() => {
    const el = document.querySelector('.user-header p');
    if (!el) return { name: '', email: '' };
    const txt = el.textContent || '';
    return { name: txt.split('—')[0]?.trim() || '', email: '' };
  })();

  document.getElementById('s1FullName').textContent   = (window.CareerTrack?.fullName) || navUser.name || '';
  document.getElementById('s1Email').textContent      = (window.CareerTrack?.email)    || '';
  document.getElementById('s1Position').textContent   = call.title;
  document.getElementById('s1Department').textContent = call.department;
  document.getElementById('s1School').textContent     = call.school;
  document.getElementById('s1Courses').textContent    = call.courses.join(', ');
  document.getElementById('s1Phone').value            = saved.phone || '';

  // Step 2
  document.getElementById('s2Degree').value         = saved.degree         || '';
  document.getElementById('s2Institution').value    = saved.institution    || '';
  document.getElementById('s2Specialization').value = saved.specialization || '';
  document.getElementById('s2Experience').value     = saved.experience     || '';
  document.getElementById('s2Summary').value        = saved.summary        || '';
  updateSummaryCount();

  // Step 3 – file previews from server
  resetFilePreviews();
  if (saved.cvFileName) addFilePreviewItem('cvPreview', saved.cvFileName, 'cv', saved.cvFileData || null);
  if (saved.clFileName) addFilePreviewItem('clPreview', saved.clFileName, 'cl', saved.clFileData || null);
  if (saved.supFileNames) {
    saved.supFileNames.forEach((n, i) => {
      const url = saved.supFilesData ? saved.supFilesData[i] : null;
      addFilePreviewItem('supPreview', n, 'sup', url);
    });
  }

  // Step 4
  document.getElementById('declarationCheck').checked = saved.declared || false;
  document.getElementById('declarationError').classList.add('d-none');
  document.getElementById('cvError').classList.add('d-none');

  // Readonly / editable UI state
  const overlay   = document.getElementById('submittedOverlay');
  const declSect  = document.getElementById('declarationSection');
  const btnSubmit = document.getElementById('btnSubmit');
  const btnDraft  = document.getElementById('btnSaveDraft');
  if (isReadonly) {
    overlay.classList.remove('d-none');
    declSect.classList.add('d-none');
    btnSubmit.classList.add('d-none');
    btnDraft.style.display = 'none';
  } else {
    overlay.classList.add('d-none');
    declSect.classList.remove('d-none');
    btnDraft.style.display = '';
  }

  setFormReadonly(isReadonly);
  goToStep(1);
  bootstrap.Modal.getOrCreateInstance(document.getElementById('applicationModal')).show();
}

function setFormReadonly(ro) {
  ['s1Phone','s2Degree','s2Institution','s2Specialization','s2Experience','s2Summary','declarationCheck']
    .forEach(id => { const el = document.getElementById(id); if (el) el.disabled = ro; });
  ['cvFile','clFile','supFiles'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.disabled = ro;
  });
  document.querySelectorAll('.upload-zone').forEach(z => {
    z.style.pointerEvents = ro ? 'none' : '';
    z.style.opacity       = ro ? '.6'   : '';
  });
  document.querySelector('#applicationModal .modal-content')
    ?.classList.toggle('wizard-readonly', ro);
}

/* ── Stepper navigation ───────────────────────────────────────── */
function goToStep(n) {
  for (let i = 1; i <= TOTAL_STEPS; i++) {
    document.getElementById(`step${i}`).classList.toggle('active', i === n);
    const nav    = document.getElementById(`stepNav${i}`);
    const circle = nav.querySelector('.step-circle');
    nav.classList.remove('active', 'done');
    if      (i < n)  { nav.classList.add('done');   circle.innerHTML = '<i class="bi bi-check-lg"></i>'; }
    else if (i === n) { nav.classList.add('active'); circle.textContent = i; }
    else              { circle.textContent = i; }
  }
  currentStep = n;
  document.getElementById('btnPrev').style.display = n > 1 ? '' : 'none';
  const btnNext   = document.getElementById('btnNext');
  const btnSubmit = document.getElementById('btnSubmit');
  if (n < TOTAL_STEPS) {
    btnNext.classList.remove('d-none');
    btnSubmit.classList.add('d-none');
  } else {
    btnNext.classList.add('d-none');
    if (!isReadonly) btnSubmit.classList.remove('d-none');
  }
  document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  document.querySelectorAll('.field-invalid').forEach(el => el.classList.remove('field-invalid'));
}

/* ── Field validation helper ──────────────────────────────────── */
function setFieldInvalid(el, invalid) {
  el.classList.toggle('is-invalid', invalid);
  const col = el.closest('[class*="col-"]') || el.closest('.mb-4');
  if (col) col.classList.toggle('field-invalid', invalid);
}

function validateStep(step) {
  let ok = true;
  if (step === 1) {
    const phone = document.getElementById('s1Phone');
    const pVal  = phone.value.trim();
    if (!pVal || /[a-zA-Z]/.test(pVal) || !/\d/.test(pVal)) {
      setFieldInvalid(phone, true);
      ok = false;
    } else {
      setFieldInvalid(phone, false);
    }
  }
  if (step === 2) {
    ['s2Degree','s2Institution','s2Specialization','s2Experience','s2Summary'].forEach(id => {
      const el = document.getElementById(id);
      if (!el.value.trim()) { setFieldInvalid(el, true); ok = false; }
      else setFieldInvalid(el, false);
    });
    const exp = document.getElementById('s2Experience');
    const v   = parseFloat(exp.value);
    if (isNaN(v) || v < 0 || !Number.isInteger(v) || v > 60) {
      setFieldInvalid(exp, true); ok = false;
    }
  }
  if (step === 3) {
    const hasCv = uploadedFiles.cv || document.getElementById('cvPreview').children.length > 0;
    if (!hasCv) { document.getElementById('cvError').classList.remove('d-none'); ok = false; }
    else          document.getElementById('cvError').classList.add('d-none');
  }
  return ok;
}

/* ── Wizard button events ─────────────────────────────────────── */
document.getElementById('btnNext').addEventListener('click', () => {
  if (!isReadonly && !validateStep(currentStep)) return;
  if (currentStep < TOTAL_STEPS) goToStep(currentStep + 1);
});

document.getElementById('btnPrev').addEventListener('click', () => {
  if (currentStep > 1) goToStep(currentStep - 1);
});

document.getElementById('btnSaveDraft').addEventListener('click', async () => {
  const btn = document.getElementById('btnSaveDraft');
  btn.disabled = true;
  try {
    await persistDraft();
    showToast('Draft saved. You can continue your application at any time.', 'primary');
  } catch (e) {
    showToast('Could not save draft.', 'danger');
  } finally {
    btn.disabled = false;
  }
});

document.getElementById('btnSubmit').addEventListener('click', async () => {
  const check = document.getElementById('declarationCheck');
  const errEl  = document.getElementById('declarationError');
  if (!check.checked) {
    errEl.classList.remove('d-none');
    check.closest('.mb-4').classList.add('field-invalid');
    return;
  }
  errEl.classList.add('d-none');
  check.closest('.mb-4').classList.remove('field-invalid');

  if (!validateStep(1) || !validateStep(2) || !validateStep(3)) {
    showToast('Please complete all required fields (Steps 1–3) before submitting.', 'danger');
    return;
  }

  const btn = document.getElementById('btnSubmit');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting…';
  try {
    await submitApplication();
  } catch (e) {
    showToast('Submission failed. Please try again.', 'danger');
    btn.disabled = false;
    btn.innerHTML = 'Submit Application';
  }
});

/* ── Persist draft to server ──────────────────────────────────── */
async function persistDraft() {
  const res = await fetch(`${API_BASE}/applications.php?action=save_draft`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      announcement_id: currentCallId,
      phone:           document.getElementById('s1Phone').value,
      degree:          document.getElementById('s2Degree').value,
      institution:     document.getElementById('s2Institution').value,
      specialization:  document.getElementById('s2Specialization').value,
      experience:      document.getElementById('s2Experience').value,
      summary:         document.getElementById('s2Summary').value,
    }),
  });
  const data = await res.json();
  if (!data.success) throw new Error(data.error || 'Save failed');
  await loadApplications();
  renderCalls();
  renderMyApplications();
}

/* ── Submit application to server ─────────────────────────────── */
async function submitApplication() {
  const fd = new FormData();
  fd.append('announcement_id', currentCallId);
  fd.append('phone',           document.getElementById('s1Phone').value);
  fd.append('degree',          document.getElementById('s2Degree').value);
  fd.append('institution',     document.getElementById('s2Institution').value);
  fd.append('specialization',  document.getElementById('s2Specialization').value);
  fd.append('experience',      document.getElementById('s2Experience').value);
  fd.append('summary',         document.getElementById('s2Summary').value);

  if (uploadedFiles.cv)  fd.append('cv', uploadedFiles.cv);
  if (uploadedFiles.cl)  fd.append('cl', uploadedFiles.cl);
  uploadedFiles.supporting.forEach(f => fd.append('sup[]', f));

  const res  = await fetch(`${API_BASE}/applications.php?action=submit`, {
    method: 'POST',
    body:   fd,
  });
  const data = await res.json();
  if (!data.success) throw new Error(data.error || 'Submit failed');

  // Show locked overlay inside modal
  document.getElementById('submittedOverlay').classList.remove('d-none');
  document.getElementById('declarationSection').classList.add('d-none');
  document.getElementById('btnSubmit').classList.add('d-none');
  document.getElementById('btnSaveDraft').style.display = 'none';
  isReadonly = true;
  setFormReadonly(true);

  showToast('Application submitted successfully!', 'success');

  const modalEl = document.getElementById('applicationModal');
  modalEl.addEventListener('hidden.bs.modal', async function handler() {
    modalEl.removeEventListener('hidden.bs.modal', handler);
    await loadApplications();
    renderCalls();
    renderMyApplications();
  });
}

/* ── File upload handling ─────────────────────────────────────── */
const MAX_FILE_SIZE = 5 * 1024 * 1024;

function addFilePreviewItem(listId, filename, type, viewUrl = null) {
  const ul = document.getElementById(listId);
  const li = document.createElement('li');
  li.dataset.filename = filename;

  const previewHtml = viewUrl
    ? `<button class="preview-file"><i class="bi bi-eye me-1"></i>View</button>`
    : `<button class="preview-file" disabled title="Preview not available"><i class="bi bi-eye me-1"></i>View</button>`;

  const removeHtml = !isReadonly
    ? `<button class="remove-file" data-type="${type}" data-name="${filename}"><i class="bi bi-x-lg"></i></button>`
    : '';

  li.innerHTML = `<i class="bi bi-file-earmark-fill"></i>
    <span class="text-truncate" style="max-width:220px;" title="${filename}">${filename}</span>
    ${previewHtml}
    ${removeHtml}`;

  if (!isReadonly) {
    li.querySelector('.remove-file')?.addEventListener('click', () => removeUploadedFile(li, type, filename));
  }

  if (viewUrl) {
    li.querySelector('.preview-file').addEventListener('click', () => {
      if (viewUrl.startsWith('data:')) {
        // Convert base64 data URL to Blob for preview
        try {
          const [header, b64] = viewUrl.split(',');
          const mime  = header.match(/:(.*?);/)[1];
          const bytes = atob(b64);
          const buf   = new Uint8Array(bytes.length);
          for (let i = 0; i < bytes.length; i++) buf[i] = bytes.charCodeAt(i);
          window.open(URL.createObjectURL(new Blob([buf], { type: mime })), '_blank');
        } catch (e) { window.open(viewUrl, '_blank'); }
      } else {
        // Server-side file URL – open directly
        window.open(viewUrl, '_blank');
      }
    });
  }

  ul.appendChild(li);
}

function removeUploadedFile(li, type, name) {
  if (type === 'cv') {
    li.remove();
    uploadedFiles.cv     = null;
    uploadedFilesData.cv = null;
    document.getElementById('cvFile').value = '';
    document.getElementById('cvChosen').style.display = 'none';
  } else if (type === 'cl') {
    li.remove();
    uploadedFiles.cl     = null;
    uploadedFilesData.cl = null;
    document.getElementById('clFile').value = '';
    document.getElementById('clChosen').style.display = 'none';
  } else {
    const ul     = document.getElementById('supPreview');
    const domIdx = Array.from(ul.children).indexOf(li);
    li.remove();
    const fileIdx = uploadedFiles.supporting.findIndex(f => f.name === name);
    if (fileIdx !== -1) {
      uploadedFiles.supporting.splice(fileIdx, 1);
      uploadedFilesData.supporting.splice(fileIdx, 1);
    } else if (domIdx !== -1) {
      uploadedFilesData.supporting.splice(domIdx, 1);
    }
  }
}

function resetFilePreviews() {
  ['cvPreview','clPreview','supPreview'].forEach(id => {
    document.getElementById(id).innerHTML = '';
  });
  ['cvChosen','clChosen'].forEach(id => {
    const el = document.getElementById(id);
    el.textContent = ''; el.style.display = 'none';
  });
  ['cvFile','clFile','supFiles'].forEach(id => {
    document.getElementById(id).value = '';
  });
}

function bindSingleFile(inputId, chosenId, previewId, type) {
  document.getElementById(inputId).addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    if (file.size > MAX_FILE_SIZE) {
      showToast(`"${file.name}" exceeds the 5 MB limit.`, 'danger'); return;
    }
    document.getElementById(previewId).innerHTML = '';
    if (type === 'cv') uploadedFiles.cv = file;
    if (type === 'cl') uploadedFiles.cl = file;
    const chosen = document.getElementById(chosenId);
    chosen.textContent = file.name;
    chosen.style.display = 'block';
    document.getElementById('cvError').classList.add('d-none');
    const reader = new FileReader();
    reader.onload = e => {
      const dataUrl = e.target.result;
      if (type === 'cv') uploadedFilesData.cv = dataUrl;
      if (type === 'cl') uploadedFilesData.cl = dataUrl;
      addFilePreviewItem(previewId, file.name, type, dataUrl);
    };
    reader.readAsDataURL(file);
  });
}

function bindMultiFile(inputId, previewId) {
  document.getElementById(inputId).addEventListener('change', function () {
    const files     = Array.from(this.files);
    const oversized = files.filter(f => f.size > MAX_FILE_SIZE);
    const valid     = files.filter(f => f.size <= MAX_FILE_SIZE);
    if (oversized.length) showToast(`${oversized.length} file(s) exceeded 5 MB and were skipped.`, 'warning');
    const slots = 5 - uploadedFiles.supporting.length;
    if (valid.length > slots) showToast(`Max 5 supporting documents. ${slots} slot(s) remaining.`, 'warning');
    valid.slice(0, slots).forEach(f => {
      uploadedFiles.supporting.push(f);
      const reader = new FileReader();
      reader.onload = e => {
        uploadedFilesData.supporting.push(e.target.result);
        addFilePreviewItem(previewId, f.name, 'sup', e.target.result);
      };
      reader.readAsDataURL(f);
    });
    this.value = '';
  });
}

document.querySelectorAll('.upload-zone').forEach(zone => {
  zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', ()  => zone.classList.remove('dragover'));
  zone.addEventListener('drop',      ()  => zone.classList.remove('dragover'));
});

bindSingleFile('cvFile', 'cvChosen', 'cvPreview', 'cv');
bindSingleFile('clFile', 'clChosen', 'clPreview', 'cl');
bindMultiFile ('supFiles', 'supPreview');

/* ── Live validation ──────────────────────────────────────────── */
document.getElementById('s1Phone').addEventListener('input', function () {
  const cleaned = this.value.replace(/[a-zA-Z]/g, '');
  if (cleaned !== this.value) {
    const pos = this.selectionStart - (this.value.length - cleaned.length);
    this.value = cleaned;
    this.setSelectionRange(pos, pos);
    setFieldInvalid(this, true);
  } else if (this.value.trim() && /\d/.test(this.value)) {
    setFieldInvalid(this, false);
  }
});

['s2Degree','s2Institution','s2Specialization','s2Summary'].forEach(id => {
  const el  = document.getElementById(id);
  const evt = el.tagName === 'SELECT' ? 'change' : 'input';
  el.addEventListener(evt, function () {
    if (this.value.trim()) setFieldInvalid(this, false);
  });
});

document.getElementById('s2Experience').addEventListener('input', function () {
  const v = parseFloat(this.value);
  if (this.value.trim() && !isNaN(v) && Number.isInteger(v) && v >= 0 && v <= 60) {
    setFieldInvalid(this, false);
  }
});

document.getElementById('declarationCheck').addEventListener('change', function () {
  if (this.checked) {
    document.getElementById('declarationError').classList.add('d-none');
    this.closest('.mb-4').classList.remove('field-invalid');
  }
});

/* ── Summary counter ──────────────────────────────────────────── */
function updateSummaryCount() {
  const ta = document.getElementById('s2Summary');
  if (ta.value.length > 1500) ta.value = ta.value.slice(0, 1500);
  document.getElementById('summaryCount').textContent = ta.value.length;
}
document.getElementById('s2Summary').addEventListener('input', updateSummaryCount);

/* ── Bootstrap-ready init ─────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', async function () {
  // Show loading state
  const callsContainer = document.getElementById('callsContainer');
  callsContainer.innerHTML = '<div class="col-12 loading-spinner"><span class="spinner-border spinner-border-sm me-2"></span>Loading positions…</div>';

  await Promise.all([loadAnnouncements(), loadApplications()]);
  renderCalls();
  renderMyApplications();
});

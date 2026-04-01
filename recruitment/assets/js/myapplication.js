/* ================================================================
  Bootstrap data from PHP (DB-backed). Falls back to local sample
  data when bootstrap is not provided.
================================================================= */
const BOOTSTRAP = window.MYAPPLICATION_BOOTSTRAP || {};

const AVAILABLE_CALLS = Array.isArray(BOOTSTRAP.calls)
  ? BOOTSTRAP.calls.map(call => ({
      ...call,
      id: String(call.id),
      courses: Array.isArray(call.courses) ? call.courses : [call.courses || '—'],
    }))
  : [];

let serverSubmissions = Array.isArray(BOOTSTRAP.submissions)
  ? BOOTSTRAP.submissions.map(sub => ({ ...sub, callId: String(sub.callId) }))
  : null;

/* ================================================================
   Helpers
================================================================= */
function formatDate(iso) {
  if (!iso) return '—';
  const [y, m, d] = iso.split('-');
  const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return `${mn[parseInt(m,10)-1]} ${parseInt(d,10)}, ${y}`;
}

function today() { return new Date().toISOString(); }

function isCallOpen(call) {
  if (call.status === 'closed' || call.status === 'cancelled' || call.status === 'draft') return false;
  if (call.status === 'published') return true;
  const t = today();
  return t >= call.startDate && t <= call.endDate;
}

function getUserData() {
  if (BOOTSTRAP.user && Object.keys(BOOTSTRAP.user).length) {
    return {
      name: BOOTSTRAP.user.name || '',
      surname: BOOTSTRAP.user.surname || '',
      email: BOOTSTRAP.user.email || '',
      phone: BOOTSTRAP.user.phone || '',
      degree: BOOTSTRAP.user.degree || '',
      institution: BOOTSTRAP.user.institution || '',
      specialization: BOOTSTRAP.user.specialization || '',
      experience: BOOTSTRAP.user.experience || '',
      summary: BOOTSTRAP.user.summary || '',
    };
  }

  const s = localStorage.getItem('userProfileData');
  if (s) return JSON.parse(s);
  return {
    name: 'Alexander', surname: 'Pierce', email: 'alexander@example.com',
    phone: '', degree: '', institution: '', specialization: '', experience: '', summary: ''
  };
}

function getDrafts()            { const s = localStorage.getItem('applicationDrafts');       return s ? JSON.parse(s) : {}; }
function saveDraftsLS(d)        { localStorage.setItem('applicationDrafts', JSON.stringify(d)); }
function getSubmissions()       {
  if (Array.isArray(serverSubmissions)) return serverSubmissions;
  const s = localStorage.getItem('submittedApplications');
  return s ? JSON.parse(s) : [];
}
function saveSubmissionsLS(a)   {
  if (Array.isArray(serverSubmissions)) {
    serverSubmissions = a.map(sub => ({ ...sub, callId: String(sub.callId) }));
  }
  localStorage.setItem('submittedApplications', JSON.stringify(a));
}

function showToast(msg, type = 'primary') {
  const el  = document.getElementById('appToast');
  const msg$ = document.getElementById('appToastMsg');
  msg$.textContent = msg;
  el.className = `toast align-items-center text-bg-${type} border-0`;
  bootstrap.Toast.getOrCreateInstance(el, { delay: 3500 }).show();
}

function statusBadge(status) {
  const map = {
    'Draft':        ['badge-draft',     'bi-pencil-square'],
    'Submitted':    ['badge-submitted', 'bi-send'],
    'Under Review': ['badge-reviewing', 'bi-hourglass-split'],
    'Approved':     ['badge-approved',  'bi-check-circle-fill'],
    'Rejected':     ['badge-rejected',  'bi-x-circle-fill'],
  };
  const [cls, icon] = map[status] || ['badge-draft','bi-circle'];
  return `<span class="badge rounded-pill ${cls}"><i class="bi ${icon} me-1"></i>${status}</span>`;
}

/* ================================================================
   Render Available Calls
================================================================= */
function renderCalls() {
  const container   = document.getElementById('callsContainer');
  const drafts      = getDrafts();
  const submissions = getSubmissions();
  const submittedIds = submissions.map(s => s.callId);
  container.innerHTML = '';

  if (!AVAILABLE_CALLS.length) {
    container.innerHTML = `
      <div class="col-12">
        <div class="card call-card">
          <div class="card-body text-center text-muted py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            <h6 class="mb-2">No application calls available</h6>
            <p class="mb-0">There are currently no recruitment announcements in the database.</p>
          </div>
        </div>
      </div>
    `;
    return;
  }

  AVAILABLE_CALLS.forEach(call => {
    const open             = isCallOpen(call);
    const alreadySubmitted = submittedIds.includes(call.id);
    const hasDraft         = drafts[call.id] != null;

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

  document.querySelectorAll('#callsContainer .open-wizard').forEach(btn => {
    btn.addEventListener('click', () => openWizard(btn.dataset.callId));
  });
}

/* ================================================================
   Render My Applications Table
================================================================= */
function renderMyApplications() {
  const tbody    = document.getElementById('myApplicationsBody');
  const noRow    = document.getElementById('noApplicationsRow');
  const drafts   = getDrafts();
  const subs     = getSubmissions();
  const rows     = [];

  subs.forEach(sub => {
    const call = AVAILABLE_CALLS.find(c => String(c.id) === String(sub.callId)) || {};
    rows.push({ callId: String(sub.callId), title: sub.title || call.title || String(sub.callId),
                department: sub.department || call.department || '—',
                submittedDate: sub.submittedDate, status: sub.status || 'Submitted', isDraft: false });
  });

  Object.keys(drafts).forEach(callId => {
    if (!subs.find(s => String(s.callId) === String(callId))) {
      const call = AVAILABLE_CALLS.find(c => String(c.id) === String(callId)) || {};
      rows.push({ callId, title: call.title || callId, department: call.department || '—',
                  submittedDate: null, status: 'Draft', isDraft: true });
    }
  });

  tbody.querySelectorAll('tr.dyn-row').forEach(r => r.remove());

  if (!rows.length) { noRow.style.display = ''; return; }
  noRow.style.display = 'none';

  rows.forEach(row => {
    const btn = row.isDraft
      ? `<div class="d-flex align-items-center justify-content-end gap-2">
           <button class="btn btn-sm btn-warning open-wizard" data-call-id="${row.callId}">
             <i class="bi bi-pencil-square me-1"></i>Continue
           </button>
           <button class="btn btn-sm btn-outline-danger delete-draft" data-call-id="${row.callId}"
                   title="Delete draft">
             <i class="bi bi-x-lg"></i>
           </button>
         </div>`
      : `<button class="btn btn-sm btn-outline-secondary view-app" data-call-id="${row.callId}">
           <i class="bi bi-eye me-1"></i>View
         </button>`;

    tbody.insertAdjacentHTML('beforeend', `
      <tr class="dyn-row">
        <td class="fw-semibold">${row.title}</td>
        <td>${row.department}</td>
        <td>${row.submittedDate ? formatDate(row.submittedDate) : '<span class="text-muted">—</span>'}</td>
        <td>${statusBadge(row.status)}</td>
        <td class="text-end">${btn}</td>
      </tr>
    `);
  });

  tbody.querySelectorAll('.open-wizard').forEach(b => b.addEventListener('click', () => openWizard(b.dataset.callId)));
  tbody.querySelectorAll('.view-app').forEach(b  => b.addEventListener('click', () => openWizard(b.dataset.callId, true)));
  tbody.querySelectorAll('.delete-draft').forEach(b => b.addEventListener('click', () => deleteDraft(b.dataset.callId)));
}

/* ================================================================
   Delete Draft
================================================================= */
function deleteDraft(callId) {
  if (!confirm(`Delete the draft for "${callId}"? This cannot be undone.`)) return;
  const drafts = getDrafts();
  delete drafts[callId];
  saveDraftsLS(drafts);
  renderMyApplications();
  renderCalls();
}

/* ================================================================
   Wizard State
================================================================= */
let currentCallId  = null;
let currentStep    = 1;
const TOTAL_STEPS  = 4;
let uploadedFiles     = { cv: null, cl: null, supporting: [] };
let uploadedFilesData = { cv: null, cl: null, supporting: [] }; // base64 data URLs for persistence
let isReadonly        = false;

function openWizard(callId, viewOnly = false) {
  const normalizedCallId = String(callId);
  const subs   = getSubmissions();
  isReadonly   = viewOnly || subs.some(s => String(s.callId) === normalizedCallId);
  currentCallId = normalizedCallId;
  currentStep   = 1;
  uploadedFiles     = { cv: null, cl: null, supporting: [] };
  uploadedFilesData = { cv: null, cl: null, supporting: [] };

  const call = AVAILABLE_CALLS.find(c => String(c.id) === normalizedCallId);
  if (!call) return;

  document.getElementById('modalCallSubtitle').textContent = `${call.title} — ${call.department}`;

  const user  = getUserData();
  const draft = (getDrafts())[normalizedCallId] || {};
  // For submitted applications the draft was deleted; fall back to the saved submission data
  const submissionRecord = subs.find(s => String(s.callId) === normalizedCallId);
  const saved = (submissionRecord && submissionRecord.data) ? submissionRecord.data : draft;

  // Step 1 – static fields
  document.getElementById('s1FullName').textContent   = `${user.name} ${user.surname}`;
  document.getElementById('s1Email').textContent      = user.email;
  document.getElementById('s1Position').textContent   = call.title;
  document.getElementById('s1Department').textContent = call.department;
  document.getElementById('s1School').textContent     = call.school;
  document.getElementById('s1Courses').textContent    = call.courses.join(', ');
  document.getElementById('s1Phone').value            = saved.phone || user.phone || '';

  // Step 2 – pre-fill from saved/submission data, fall back to profile
  document.getElementById('s2Degree').value         = saved.degree         || user.degree         || '';
  document.getElementById('s2Institution').value    = saved.institution    || user.institution    || '';
  document.getElementById('s2Specialization').value = saved.specialization || user.specialization || '';
  document.getElementById('s2Experience').value     = saved.experience     || user.experience     || '';
  document.getElementById('s2Summary').value        = saved.summary        || user.summary        || '';
  updateSummaryCount();

  // Step 3 – file preview names (File objects can't be persisted)
  resetFilePreviews();
  if (saved.cvFileName)  addFilePreviewItem('cvPreview',  saved.cvFileName, 'cv',  saved.cvFileData  || null);
  if (saved.clFileName)  addFilePreviewItem('clPreview',  saved.clFileName, 'cl',  saved.clFileData  || null);
  (saved.supFileNames || []).forEach((n, i) => addFilePreviewItem('supPreview', n, 'sup', (saved.supFilesData && saved.supFilesData[i]) || null));

  // Restore persisted base64 data back into uploadedFilesData so that
  // collectFormData() / submitApplication() can carry it into the submission record
  if (saved.cvFileData)  uploadedFilesData.cv = saved.cvFileData;
  if (saved.clFileData)  uploadedFilesData.cl = saved.clFileData;
  if (saved.supFilesData && saved.supFilesData.length) uploadedFilesData.supporting = [...saved.supFilesData];

  // Step 4 – declaration
  document.getElementById('declarationCheck').checked = saved.declared || false;
  document.getElementById('declarationError').classList.add('d-none');
  document.getElementById('cvError').classList.add('d-none');

  // Readonly toggle
  const overlay    = document.getElementById('submittedOverlay');
  const declSect   = document.getElementById('declarationSection');
  const btnSubmit  = document.getElementById('btnSubmit');
  const btnDraft   = document.getElementById('btnSaveDraft');
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
  // Hide all red required asterisks and validation messages in view mode
  const modalContent = document.querySelector('#applicationModal .modal-content');
  if (modalContent) modalContent.classList.toggle('wizard-readonly', ro);
}

/* ================================================================
   Stepper navigation
================================================================= */
function goToStep(n) {
  for (let i = 1; i <= TOTAL_STEPS; i++) {
    document.getElementById(`step${i}`).classList.toggle('active', i === n);
    const nav    = document.getElementById(`stepNav${i}`);
    const circle = nav.querySelector('.step-circle');
    nav.classList.remove('active','done');
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

/* ================================================================
   Helper: mark / unmark a field as invalid and toggle the parent
   column's .field-invalid class so required asterisks become visible
================================================================= */
function setFieldInvalid(el, invalid) {
  el.classList.toggle('is-invalid', invalid);
  const col = el.closest('[class*="col-"]') || el.closest('.mb-4');
  if (col) col.classList.toggle('field-invalid', invalid);
}

/* ================================================================
   Per-step validation
================================================================= */
function validateStep(step) {
  let ok = true;

  if (step === 1) {
    const phone = document.getElementById('s1Phone');
    const pVal  = phone.value.trim();
    const hasLetters = /[a-zA-Z]/.test(pVal);
    const hasDigits  = /\d/.test(pVal);
    if (!pVal || hasLetters || !hasDigits) {
      setFieldInvalid(phone, true);
      // Update the feedback message dynamically
      const fb = phone.nextElementSibling;
      if (fb && fb.classList.contains('invalid-feedback')) {
        fb.textContent = hasLetters ? 'Phone number must contain only digits, spaces, +, -, or parentheses.' : 'Please enter a valid phone number.';
      }
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
    const cvPreview = document.getElementById('cvPreview');
    const hasCv     = uploadedFiles.cv || cvPreview.children.length > 0;
    if (!hasCv) { document.getElementById('cvError').classList.remove('d-none'); ok = false; }
    else          document.getElementById('cvError').classList.add('d-none');
  }

  return ok;
}

/* ================================================================
   Wizard button events
================================================================= */
document.getElementById('btnNext').addEventListener('click', () => {
  // Skip validation entirely when just viewing a submitted application
  if (!isReadonly && !validateStep(currentStep)) return;
  if (currentStep < TOTAL_STEPS) goToStep(currentStep + 1);
});

document.getElementById('btnPrev').addEventListener('click', () => {
  if (currentStep > 1) goToStep(currentStep - 1);
});

document.getElementById('btnSaveDraft').addEventListener('click', () => {
  persistDraft();
  showToast('Draft saved. You can continue your application at any time.', 'primary');
});

document.getElementById('btnSubmit').addEventListener('click', () => {
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

  submitApplication();
});

/* ================================================================
   Collect / persist draft
================================================================= */
function collectFormData() {
  const cvPreview  = document.getElementById('cvPreview');
  const clPreview  = document.getElementById('clPreview');
  const supPreview = document.getElementById('supPreview');
  return {
    phone:          document.getElementById('s1Phone').value,
    degree:         document.getElementById('s2Degree').value,
    institution:    document.getElementById('s2Institution').value,
    specialization: document.getElementById('s2Specialization').value,
    experience:     document.getElementById('s2Experience').value,
    summary:        document.getElementById('s2Summary').value,
    declared:       document.getElementById('declarationCheck').checked,
    cvFileName:    uploadedFiles.cv  ? uploadedFiles.cv.name  : (cvPreview.children.length  ? cvPreview.children[0].dataset.filename  : null),
    clFileName:    uploadedFiles.cl  ? uploadedFiles.cl.name  : (clPreview.children.length  ? clPreview.children[0].dataset.filename  : null),
    supFileNames:  uploadedFiles.supporting.length
                     ? uploadedFiles.supporting.map(f => f.name)
                     : Array.from(supPreview.children).map(li => li.dataset.filename),
    cvFileData:    uploadedFilesData.cv  || null,
    clFileData:    uploadedFilesData.cl  || null,
    supFilesData:  uploadedFilesData.supporting.length ? [...uploadedFilesData.supporting] : [],
    savedAt: new Date().toISOString(),
  };
}

function persistDraft() {
  const drafts = getDrafts();
  drafts[currentCallId] = collectFormData();
  saveDraftsLS(drafts);
  renderCalls();
  renderMyApplications();
}

/* ================================================================
   Submit application
================================================================= */
function submitApplication() {
  // Remove draft
  const drafts = getDrafts();
  delete drafts[currentCallId];
  saveDraftsLS(drafts);

  // Store submission
  const subs = getSubmissions();
  if (!subs.find(s => s.callId === currentCallId)) {
    subs.push({ callId: currentCallId, submittedDate: today(), status: 'Submitted', data: collectFormData() });
    saveSubmissionsLS(subs);
  }

  // Show locked state in modal
  document.getElementById('submittedOverlay').classList.remove('d-none');
  document.getElementById('declarationSection').classList.add('d-none');
  document.getElementById('btnSubmit').classList.add('d-none');
  document.getElementById('btnSaveDraft').style.display = 'none';
  isReadonly = true;
  setFormReadonly(true);

  showToast('Application submitted successfully!', 'success');

  const modalEl = document.getElementById('applicationModal');
  modalEl.addEventListener('hidden.bs.modal', function handler() {
    renderCalls();
    renderMyApplications();
    modalEl.removeEventListener('hidden.bs.modal', handler);
  });
}

/* ================================================================
   File upload handling
================================================================= */
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB

function addFilePreviewItem(listId, filename, type, viewUrl = null) {
  const ul = document.getElementById(listId);
  const li = document.createElement('li');
  li.dataset.filename = filename;

  const previewHtml = viewUrl
    ? `<button class="preview-file" aria-label="View ${filename}">
         <i class="bi bi-eye me-1"></i>View
       </button>`
    : `<button class="preview-file" disabled title="File preview unavailable">
         <i class="bi bi-eye me-1"></i>View
       </button>`;

  const removeHtml = !isReadonly
    ? `<button class="remove-file" aria-label="Remove" data-type="${type}" data-name="${filename}">
         <i class="bi bi-x-lg"></i>
       </button>`
    : '';

  li.innerHTML = `<i class="bi bi-file-earmark-fill"></i>
    <span class="text-truncate" style="max-width:220px;" title="${filename}">${filename}</span>
    ${previewHtml}
    ${removeHtml}`;

  if (!isReadonly) {
    li.querySelector('.remove-file').addEventListener('click', () => removeUploadedFile(li, type, filename));
  }
  // View button: convert data URL → Blob URL so browser can open it
  if (viewUrl) {
    li.querySelector('.preview-file').addEventListener('click', () => {
      let url = viewUrl;
      if (viewUrl.startsWith('data:')) {
        try {
          const [header, b64] = viewUrl.split(',');
          const mime  = header.match(/:(.*?);/)[1];
          const bytes = atob(b64);
          const buf   = new Uint8Array(bytes.length);
          for (let i = 0; i < bytes.length; i++) buf[i] = bytes.charCodeAt(i);
          url = URL.createObjectURL(new Blob([buf], { type: mime }));
        } catch(e) { /* fallback: try opening as-is */ }
      }
      window.open(url, '_blank');
    });
  }
  ul.appendChild(li);
}

function removeUploadedFile(li, type, name) {
  if (type === 'cv') {
    li.remove();
    uploadedFiles.cv = null;
    uploadedFilesData.cv = null;
    document.getElementById('cvFile').value = '';
    document.getElementById('cvChosen').style.display = 'none';
  } else if (type === 'cl') {
    li.remove();
    uploadedFiles.cl = null;
    uploadedFilesData.cl = null;
    document.getElementById('clFile').value = '';
    document.getElementById('clChosen').style.display = 'none';
  } else {
    // Determine DOM index BEFORE removing the element
    const ul      = document.getElementById('supPreview');
    const domIdx  = Array.from(ul.children).indexOf(li);
    li.remove();
    const fileIdx = uploadedFiles.supporting.findIndex(f => f.name === name);
    if (fileIdx !== -1) {
      uploadedFiles.supporting.splice(fileIdx, 1);
      uploadedFilesData.supporting.splice(fileIdx, 1);
    } else if (domIdx !== -1) {
      // File was loaded from a saved draft (no File object); remove by DOM position
      uploadedFilesData.supporting.splice(domIdx, 1);
    }
  }
}

function resetFilePreviews() {
  ['cvPreview','clPreview','supPreview'].forEach(id => document.getElementById(id).innerHTML = '');
  ['cvChosen','clChosen'].forEach(id => {
    const el = document.getElementById(id);
    el.textContent = ''; el.style.display = 'none';
  });
  ['cvFile','clFile','supFiles'].forEach(id => { document.getElementById(id).value = ''; });
}

function bindSingleFile(inputId, chosenId, previewId, type) {
  document.getElementById(inputId).addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    if (file.size > MAX_FILE_SIZE) { showToast(`"${file.name}" exceeds the 5 MB limit.`, 'danger'); return; }
    document.getElementById(previewId).innerHTML = '';
    if (type === 'cv') uploadedFiles.cv = file;
    if (type === 'cl') uploadedFiles.cl = file;
    const chosen = document.getElementById(chosenId);
    chosen.textContent = file.name;
    chosen.style.display = 'block';
    document.getElementById('cvError').classList.add('d-none');
    // Read as base64 data URL so the View button works even after submission
    const reader = new FileReader();
    reader.onload = function (e) {
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
    const files    = Array.from(this.files);
    const oversized = files.filter(f => f.size > MAX_FILE_SIZE);
    const valid     = files.filter(f => f.size <= MAX_FILE_SIZE);
    if (oversized.length) showToast(`${oversized.length} file(s) exceeded 5 MB and were skipped.`, 'warning');
    const slots = 5 - uploadedFiles.supporting.length;
    if (valid.length > slots) showToast(`Max 5 supporting documents. ${slots} slot(s) remaining.`, 'warning');
    valid.slice(0, slots).forEach(f => {
      uploadedFiles.supporting.push(f);
      const reader = new FileReader();
      reader.onload = function (e) {
        uploadedFilesData.supporting.push(e.target.result);
        addFilePreviewItem(previewId, f.name, 'sup', e.target.result);
      };
      reader.readAsDataURL(f);
    });
    this.value = '';
  });
}

// Drag-over visual feedback
document.querySelectorAll('.upload-zone').forEach(zone => {
  zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', ()  => zone.classList.remove('dragover'));
  zone.addEventListener('drop',      ()  => zone.classList.remove('dragover'));
});

bindSingleFile('cvFile', 'cvChosen', 'cvPreview', 'cv');
bindSingleFile('clFile', 'clChosen', 'clPreview', 'cl');
bindMultiFile ('supFiles', 'supPreview');

/* ================================================================
   Live validation clearing – remove red errors as user fixes fields
================================================================= */
(function bindLiveValidation() {
  // Phone: clear when valid content present
  document.getElementById('s1Phone').addEventListener('input', function () {
    if (this.value.trim() && /\d/.test(this.value) && !/[a-zA-Z]/.test(this.value)) {
      setFieldInvalid(this, false);
    }
  });

  // Step 2 text / select fields: clear as soon as they have a value
  ['s2Degree','s2Institution','s2Specialization','s2Summary'].forEach(id => {
    const el = document.getElementById(id);
    const evt = el.tagName === 'SELECT' ? 'change' : 'input';
    el.addEventListener(evt, function () {
      if (this.value.trim()) setFieldInvalid(this, false);
    });
  });

  // Experience: clear when a valid non-negative integer is entered
  document.getElementById('s2Experience').addEventListener('input', function () {
    const v = parseFloat(this.value);
    if (this.value.trim() && !isNaN(v) && Number.isInteger(v) && v >= 0 && v <= 60) {
      setFieldInvalid(this, false);
    }
  });
  // Declaration checkbox: clear error as soon as checked
  document.getElementById('declarationCheck').addEventListener('change', function () {
    if (this.checked) {
      document.getElementById('declarationError').classList.add('d-none');
      this.closest('.mb-4').classList.remove('field-invalid');
    }
  });
})();

/* ================================================================
   Phone – strip letters on input
================================================================= */
document.getElementById('s1Phone').addEventListener('input', function () {
  // Remove any letter characters as they are typed
  const cleaned = this.value.replace(/[a-zA-Z]/g, '');
  if (cleaned !== this.value) {
    const pos = this.selectionStart - (this.value.length - cleaned.length);
    this.value = cleaned;
    this.setSelectionRange(pos, pos);
    setFieldInvalid(this, true);
    const fb = this.nextElementSibling;
    if (fb && fb.classList.contains('invalid-feedback')) {
      fb.textContent = 'Phone number must contain only digits, spaces, +, -, or parentheses.';
    }
  }
});

/* ================================================================
   Summary character counter
================================================================= */
function updateSummaryCount() {
  const ta = document.getElementById('s2Summary');
  if (ta.value.length > 1500) ta.value = ta.value.slice(0, 1500);
  document.getElementById('summaryCount').textContent = ta.value.length;
}
document.getElementById('s2Summary').addEventListener('input', updateSummaryCount);

/* ================================================================
   Navbar user name sync
================================================================= */
(function syncNavbar() {
  const u = getUserData();
  const navName = document.getElementById('navbarUserName');
  if (navName) navName.textContent = `${u.name} ${u.surname}`;
  const uhp = document.querySelector('.user-header p');
  if (uhp) {
    const small = uhp.querySelector('small');
    uhp.innerHTML = `${u.name} ${u.surname} - Web Developer${small ? `<small>${small.textContent}</small>` : ''}`;
  }
})();

/* ================================================================
   Bootstrap-ready initialisation
================================================================= */
document.addEventListener('DOMContentLoaded', function () {
  renderCalls();
  renderMyApplications();
});

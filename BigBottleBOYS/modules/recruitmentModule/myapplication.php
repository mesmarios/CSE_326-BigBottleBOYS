<?php include('../../includes/layout.php'); include('../../includes/header.php'); include('../../includes/nav.php'); ?>
<style>
/* ── My Applications page ───────────────────────────────────────── */

/* ---- Call cards ------------------------------------------------- */
.call-card {
  border: none;
  border-radius: .75rem;
  box-shadow: 0 2px 12px rgba(0,0,0,.07);
  transition: box-shadow .2s ease, transform .2s ease;
}
.call-card:hover {
  box-shadow: 0 6px 24px rgba(0,0,0,.12);
  transform: translateY(-2px);
}
.call-card .card-header {
  border-radius: .75rem .75rem 0 0 !important;
  border-bottom: 1px solid #e9ecef;
  background: #fff;
  padding: .85rem 1.25rem;
}
.call-card .card-header h6 {
  font-size: .95rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
}
.call-meta {
  font-size: .82rem;
  color: #6c757d;
}
.call-meta i { width: 16px; text-align: center; }
.call-courses { font-size: .80rem; }

/* ---- Section headings ------------------------------------------ */
.section-heading {
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a2e;
  letter-spacing: .01em;
  border-left: 4px solid #0d6efd;
  padding-left: .6rem;
  margin-bottom: 0;
}

/* ---- My Applications table ------------------------------------- */
.table-app td, .table-app th { vertical-align: middle; font-size: .87rem; }
.table-app thead th {
  background: #f8f9fa;
  font-weight: 700;
  font-size: .78rem;
  text-transform: uppercase;
  letter-spacing: .04em;
  color: #6c757d;
  border-top: none;
}

/* ---- Status badges --------------------------------------------- */
.badge-draft     { background:#e9ecef; color:#495057; }
.badge-submitted { background:#cfe2ff; color:#084298; }
.badge-reviewing { background:#fff3cd; color:#664d03; }
.badge-approved  { background:#d1e7dd; color:#0a3622; }
.badge-rejected  { background:#f8d7da; color:#58151c; }

/* ---- Stepper --------------------------------------------------- */
.wizard-stepper {
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 1.5rem 1rem 1rem;
}
.wizard-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  position: relative;
}
.wizard-step:not(:last-child)::after {
  content: '';
  position: absolute;
  top: 18px;
  left: calc(50% + 18px);
  right: calc(-50% + 18px);
  height: 2px;
  background: #dee2e6;
  z-index: 0;
  transition: background .3s;
}
.wizard-step.done:not(:last-child)::after { background: #0d6efd; }
.step-circle {
  width: 36px; height: 36px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: .85rem;
  background: #dee2e6; color: #6c757d;
  border: 2px solid #dee2e6;
  z-index: 1;
  transition: background .3s, color .3s, border-color .3s;
  position: relative;
}
.wizard-step.active .step-circle {
  background: #0d6efd; color: #fff; border-color: #0d6efd;
  box-shadow: 0 0 0 4px rgba(13,110,253,.15);
}
.wizard-step.done .step-circle { background: #198754; color: #fff; border-color: #198754; }
.step-label {
  font-size: .72rem; margin-top: .4rem; color: #6c757d;
  font-weight: 500; text-align: center; line-height: 1.3;
}
.wizard-step.active .step-label { color: #0d6efd; font-weight: 700; }
.wizard-step.done  .step-label { color: #198754; }

/* ---- Step pane ------------------------------------------------- */
.step-pane { display: none; }
.step-pane.active { display: block; animation: fadeIn .2s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

/* ---- Readonly field -------------------------------------------- */
.form-control-readonly {
  background: #f8f9fa; border: 1px solid #dee2e6; border-radius: .375rem;
  padding: .375rem .75rem; font-size: .9rem; color: #495057;
  min-height: 38px; display: flex; align-items: center;
}

/* ---- File upload zone ------------------------------------------ */
.upload-zone {
  border: 2px dashed #ced4da; border-radius: .5rem;
  padding: 1.25rem; text-align: center; cursor: pointer;
  transition: border-color .2s, background .2s;
  position: relative; background: #fafafa;
}
.upload-zone:hover, .upload-zone.dragover { border-color: #0d6efd; background: #f0f4ff; }
.upload-zone input[type=file] {
  position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.upload-zone .upload-icon { font-size: 1.8rem; color: #adb5bd; }
.upload-zone p { margin: .3rem 0 0; font-size: .83rem; color: #6c757d; }
.upload-zone .file-chosen {
  font-size: .83rem; color: #0d6efd; margin-top: .4rem;
  display: none; font-weight: 600;
}
.file-preview-list { list-style: none; padding: 0; margin-top: .75rem; }
.file-preview-list li {
  display: flex; align-items: center; gap: .5rem;
  background: #f0f4ff; border-radius: .375rem;
  padding: .35rem .65rem; margin-bottom: .4rem; font-size: .83rem;
}
.file-preview-list li .bi { color: #0d6efd; }
.file-preview-list li .remove-file {
  margin-left: auto; cursor: pointer; color: #dc3545;
  background: none; border: none; padding: 0 .2rem; font-size: .85rem;
}
.file-preview-list li .preview-file {
  cursor: pointer; color: #0d6efd;
  background: none; border: none; padding: 0 .2rem; font-size: .85rem;
  text-decoration: none; white-space: nowrap;
}
.file-preview-list li .preview-file:hover { text-decoration: underline; }
.file-preview-list li .preview-file:disabled,
.file-preview-list li .preview-file[disabled] {
  cursor: default; color: #adb5bd; text-decoration: none;
}

/* ---- Declaration card ------------------------------------------ */
.declaration-box {
  background: #f8f9fa; border-radius: .5rem;
  border: 1px solid #dee2e6; padding: 1rem 1.25rem;
  font-size: .87rem; color: #495057; line-height: 1.6;
}

/* ---- Submitted overlay ----------------------------------------- */
.submitted-overlay {
  background: linear-gradient(135deg,#d1e7dd,#a3cfbb);
  border-radius: .5rem; padding: 1.5rem; text-align: center;
}
.submitted-overlay i { font-size: 2.5rem; color: #0a3622; }

/* ---- Required asterisks: hidden by default, shown only on error */
#applicationModal .form-label .text-danger,
#applicationModal .form-check-label .text-danger { display: none; }
#applicationModal .field-invalid .form-label .text-danger,
#applicationModal .field-invalid .form-check-label .text-danger { display: inline !important; }

/* ---- Readonly mode: hide all red required markers & error text  */
.wizard-readonly .form-label .text-danger,
.wizard-readonly .form-check-label .text-danger,
.wizard-readonly .invalid-feedback,
.wizard-readonly #cvError,
.wizard-readonly #declarationError { display: none !important; }

/* ---- Responsive ----------------------------------------------- */
@media (max-width: 576px) {
  .wizard-stepper { padding: 1rem .25rem .75rem; }
  .step-label { font-size: .65rem; }
  .step-circle { width: 30px; height: 30px; font-size: .75rem; }
  .wizard-step:not(:last-child)::after { top: 14px; }
}

/* Blurry backdrop for all modals */
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
              <div class="col-sm-6"><h3 class="mb-0">My Applications</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">My Applications</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <div class="container-fluid">

            <!-- ── Available Application Calls ─────────────────── -->
            <div class="mb-4">
              <p class="section-heading mb-3">
                <i class="bi bi-megaphone-fill me-2 text-primary"></i>Available Application Calls
              </p>
              <div class="row g-3" id="callsContainer">
                <!-- Rendered by JS -->
              </div>
            </div>

            <!-- ── My Applications ─────────────────────────────── -->
            <div class="mb-4">
              <p class="section-heading mb-3">
                <i class="bi bi-folder2-open me-2 text-primary"></i>My Applications
              </p>
              <div class="card call-card">
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-app table-hover mb-0">
                      <thead>
                        <tr>
                          <th>Position</th>
                          <th>Department</th>
                          <th>Submitted</th>
                          <th>Status</th>
                          <th class="text-end">Action</th>
                        </tr>
                      </thead>
                      <tbody id="myApplicationsBody">
                        <tr id="noApplicationsRow">
                          <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            No applications yet. Browse the calls above to get started.
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->

<!-- ══════════════════════════════════════════════════════════════
     Application Wizard Modal
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="applicationModal" tabindex="-1"
     aria-labelledby="applicationModalLabel" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title fw-bold" id="applicationModalLabel">Application Form</h5>
          <p class="text-muted mb-0" id="modalCallSubtitle" style="font-size:.82rem;"></p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Stepper -->
      <div class="wizard-stepper" id="wizardStepper">
        <div class="wizard-step active" id="stepNav1">
          <div class="step-circle">1</div>
          <span class="step-label">General<br>Information</span>
        </div>
        <div class="wizard-step" id="stepNav2">
          <div class="step-circle">2</div>
          <span class="step-label">Academic /<br>Professional</span>
        </div>
        <div class="wizard-step" id="stepNav3">
          <div class="step-circle">3</div>
          <span class="step-label">Documents</span>
        </div>
        <div class="wizard-step" id="stepNav4">
          <div class="step-circle">4</div>
          <span class="step-label">Declaration &amp;<br>Submit</span>
        </div>
      </div>

      <div class="modal-body pt-2">

        <!-- ── STEP 1 – General Information ──────────────────── -->
        <div class="step-pane active" id="step1">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Full Name</label>
              <div class="form-control-readonly" id="s1FullName">—</div>
              <div class="form-text">Auto-filled from your profile.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email Address</label>
              <div class="form-control-readonly" id="s1Email">—</div>
              <div class="form-text">Auto-filled from your profile.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="s1Phone">
                Phone Number <span class="text-danger">*</span>
              </label>
              <input type="tel" class="form-control" id="s1Phone" placeholder="+1 555 123 4567" />
              <div class="invalid-feedback">Please enter a valid phone number.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Applied Position</label>
              <div class="form-control-readonly" id="s1Position">—</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Department</label>
              <div class="form-control-readonly" id="s1Department">—</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">School / Faculty</label>
              <div class="form-control-readonly" id="s1School">—</div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Course(s)</label>
              <div class="form-control-readonly" id="s1Courses">—</div>
            </div>
          </div>
        </div>

        <!-- ── STEP 2 – Academic / Professional ──────────────── -->
        <div class="step-pane" id="step2">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="s2Degree">
                Highest Degree <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="s2Degree">
                <option value="">— Select degree —</option>
                <option value="High School Diploma">High School Diploma</option>
                <option value="Associate's Degree">Associate's Degree</option>
                <option value="Bachelor's Degree">Bachelor's Degree</option>
                <option value="Master's Degree">Master's Degree</option>
                <option value="Doctoral Degree (PhD)">Doctoral Degree (PhD)</option>
                <option value="Professional Degree (MD / JD / etc.)">Professional Degree (MD / JD / etc.)</option>
                <option value="Other">Other</option>
              </select>
              <div class="invalid-feedback">Please select your highest degree.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="s2Institution">
                Institution of Graduation <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="s2Institution"
                     placeholder="e.g. University of Athens" />
              <div class="invalid-feedback">Please enter your institution.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="s2Specialization">
                Field of Specialization <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="s2Specialization"
                     placeholder="e.g. Computer Science" />
              <div class="invalid-feedback">Please enter your field of specialization.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="s2Experience">
                Years of Professional Experience <span class="text-danger">*</span>
              </label>
              <input type="number" class="form-control" id="s2Experience"
                     min="0" max="60" placeholder="0" />
              <div class="invalid-feedback">Please enter a valid number (0–60).</div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" for="s2Summary">
                Professional Summary <span class="text-danger">*</span>
              </label>
              <textarea class="form-control" id="s2Summary" rows="5"
                placeholder="Briefly describe your professional background, key achievements, and motivation for applying…"></textarea>
              <div class="form-text text-end">
                <span id="summaryCount">0</span> / 1500 characters
              </div>
              <div class="invalid-feedback">Please provide a professional summary.</div>
            </div>
          </div>
        </div>

        <!-- ── STEP 3 – Documents ────────────────────────────── -->
        <div class="step-pane" id="step3">
          <div class="row g-4">

            <!-- CV -->
            <div class="col-12">
              <label class="form-label fw-semibold">
                Curriculum Vitae (CV) <span class="text-danger">*</span>
                <span class="text-muted fw-normal ms-1" style="font-size:.8rem;">PDF only, max 5 MB</span>
              </label>
              <div class="upload-zone" id="cvZone">
                <input type="file" id="cvFile" accept=".pdf" aria-label="Upload CV" />
                <i class="bi bi-file-earmark-pdf upload-icon"></i>
                <p>Drag &amp; drop your CV here, or <strong>click to browse</strong></p>
                <div class="file-chosen" id="cvChosen"></div>
              </div>
              <ul class="file-preview-list" id="cvPreview"></ul>
              <div class="text-danger d-none mt-1" id="cvError" style="font-size:.83rem;">
                <i class="bi bi-exclamation-circle me-1"></i>Please upload your CV (PDF, max 5 MB).
              </div>
            </div>

            <!-- Cover Letter -->
            <div class="col-12">
              <label class="form-label fw-semibold">
                Cover Letter
                <span class="badge text-bg-secondary fw-normal ms-1">Optional</span>
                <span class="text-muted fw-normal ms-1" style="font-size:.8rem;">PDF only, max 5 MB</span>
              </label>
              <div class="upload-zone" id="clZone">
                <input type="file" id="clFile" accept=".pdf" aria-label="Upload cover letter" />
                <i class="bi bi-file-earmark-text upload-icon"></i>
                <p>Drag &amp; drop your cover letter, or <strong>click to browse</strong></p>
                <div class="file-chosen" id="clChosen"></div>
              </div>
              <ul class="file-preview-list" id="clPreview"></ul>
            </div>

            <!-- Supporting Documents -->
            <div class="col-12">
              <label class="form-label fw-semibold">
                Supporting Documents
                <span class="badge text-bg-secondary fw-normal ms-1">Optional</span>
                <span class="text-muted fw-normal ms-1" style="font-size:.8rem;">PDF / DOCX / JPG, max 5 MB each, up to 5 files</span>
              </label>
              <div class="upload-zone" id="supZone">
                <input type="file" id="supFiles"
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                       multiple aria-label="Upload supporting documents" />
                <i class="bi bi-paperclip upload-icon"></i>
                <p>Drag &amp; drop supporting documents, or <strong>click to browse</strong></p>
              </div>
              <ul class="file-preview-list" id="supPreview"></ul>
            </div>

          </div>
        </div>

        <!-- ── STEP 4 – Declaration & Submission ──────────────── -->
        <div class="step-pane" id="step4">

          <!-- Locked overlay (shown after final submission) -->
          <div class="submitted-overlay d-none mb-3" id="submittedOverlay">
            <i class="bi bi-patch-check-fill d-block mb-2"></i>
            <h5 class="fw-bold text-success mb-1">Application Submitted</h5>
            <p class="mb-0 text-muted" style="font-size:.88rem;">
              Your application has been successfully submitted. You may track its status
              under <strong>My Applications</strong>.<br>
              No further changes are permitted after submission.
            </p>
          </div>

          <div id="declarationSection">
            <div class="declaration-box mb-3">
              <p class="fw-semibold mb-2">
                <i class="bi bi-shield-check text-primary me-2"></i>Declaration of Accuracy
              </p>
              <p class="mb-0">
                I hereby declare that all information provided in this application is true,
                complete, and accurate to the best of my knowledge. I understand that any false
                or misleading information may result in the immediate disqualification of my
                application or, if discovered after an appointment, in the termination of my
                employment. I consent to the processing of my personal data for the purposes of
                this recruitment process in accordance with applicable data-protection legislation.
              </p>
            </div>

            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="declarationCheck" />
                <label class="form-check-label fw-semibold" for="declarationCheck">
                  I have read and agree to the above declaration.
                  <span class="text-danger">*</span>
                </label>
              </div>
              <div class="text-danger d-none mt-1" id="declarationError" style="font-size:.83rem;">
                <i class="bi bi-exclamation-circle me-1"></i>You must accept the declaration before submitting.
              </div>
            </div>

            <div class="alert alert-info py-2 mb-0" role="alert" style="font-size:.84rem;">
              <i class="bi bi-info-circle me-1"></i>
              <strong>Before you submit:</strong> Please review all steps to ensure the information
              is correct. Once submitted, the application cannot be edited.
            </div>
          </div>

        </div>
        <!-- ── end step4 ───────────────────────────────────────── -->

      </div><!-- /.modal-body -->

      <div class="modal-footer justify-content-between flex-wrap gap-2" id="wizardFooter">
        <div>
          <button class="btn btn-outline-secondary" id="btnPrev" style="display:none;">
            <i class="bi bi-arrow-left me-1"></i>Previous
          </button>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-outline-primary" id="btnSaveDraft">
            <i class="bi bi-floppy me-1"></i>Save as Draft
          </button>
          <button class="btn btn-primary" id="btnNext">
            Next <i class="bi bi-arrow-right ms-1"></i>
          </button>
          <button class="btn btn-success d-none" id="btnSubmit">
            <i class="bi bi-send-fill me-1"></i>Submit Application
          </button>
        </div>
      </div>

    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
<!-- ══ end applicationModal ══════════════════════════════════════ -->

    <!-- Toast notifications -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
      <div id="appToast" class="toast align-items-center text-bg-primary border-0"
           role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body" id="appToastMsg">Saved.</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto"
                  data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>

<script>
/* ================================================================
   Data – Available Application Calls (sample data)
   In production these would come from a PHP/database query.
================================================================= */
const AVAILABLE_CALLS = [
  {
    id: 'CALL-2026-001',
    title: 'Lecturer in Computer Science',
    department: 'Department of Computer Science',
    school: 'School of Engineering & Applied Sciences',
    courses: ['CS101 – Introduction to Programming', 'CS201 – Data Structures', 'CS305 – Algorithms'],
    startDate: '2026-01-15',
    endDate: '2026-03-31',
  },
  {
    id: 'CALL-2026-002',
    title: 'Assistant Professor in Mathematics',
    department: 'Department of Mathematics',
    school: 'School of Natural Sciences',
    courses: ['MATH101 – Calculus I', 'MATH201 – Linear Algebra', 'MATH302 – Probability & Statistics'],
    startDate: '2026-02-01',
    endDate: '2026-04-15',
  },
  {
    id: 'CALL-2026-003',
    title: 'Adjunct Instructor – Business Administration',
    department: 'Department of Business & Management',
    school: 'School of Economics & Business',
    courses: ['BUS101 – Principles of Management', 'BUS210 – Marketing Fundamentals'],
    startDate: '2026-01-20',
    endDate: '2026-02-28',
  },
  {
    id: 'CALL-2026-004',
    title: 'Research Associate – Environmental Studies',
    department: 'Department of Environmental Sciences',
    school: 'School of Natural Sciences',
    courses: ['ENV201 – Environmental Policy', 'ENV303 – Climate Change & Society'],
    startDate: '2026-03-01',
    endDate: '2026-05-30',
  },
];

/* ================================================================
   Helpers
================================================================= */
function formatDate(iso) {
  if (!iso) return '—';
  const [y, m, d] = iso.split('-');
  const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return `${mn[parseInt(m,10)-1]} ${parseInt(d,10)}, ${y}`;
}

function today() { return new Date().toISOString().slice(0,10); }

function isCallOpen(call) {
  const t = today();
  return t >= call.startDate && t <= call.endDate;
}

function getUserData() {
  const s = localStorage.getItem('userProfileData');
  if (s) return JSON.parse(s);
  return {
    name: 'Alexander', surname: 'Pierce', email: 'alexander@example.com',
    phone: '', degree: '', institution: '', specialization: '', experience: '', summary: ''
  };
}

function getDrafts()            { const s = localStorage.getItem('applicationDrafts');       return s ? JSON.parse(s) : {}; }
function saveDraftsLS(d)        { localStorage.setItem('applicationDrafts', JSON.stringify(d)); }
function getSubmissions()       { const s = localStorage.getItem('submittedApplications');   return s ? JSON.parse(s) : []; }
function saveSubmissionsLS(a)   { localStorage.setItem('submittedApplications', JSON.stringify(a)); }

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
    const call = AVAILABLE_CALLS.find(c => c.id === sub.callId) || {};
    rows.push({ callId: sub.callId, title: call.title || sub.callId,
                department: call.department || '—',
                submittedDate: sub.submittedDate, status: sub.status || 'Submitted', isDraft: false });
  });

  Object.keys(drafts).forEach(callId => {
    if (!subs.find(s => s.callId === callId)) {
      const call = AVAILABLE_CALLS.find(c => c.id === callId) || {};
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
  const subs   = getSubmissions();
  isReadonly   = viewOnly || subs.some(s => s.callId === callId);
  currentCallId = callId;
  currentStep   = 1;
  uploadedFiles     = { cv: null, cl: null, supporting: [] };
  uploadedFilesData = { cv: null, cl: null, supporting: [] };

  const call = AVAILABLE_CALLS.find(c => c.id === callId);
  if (!call) return;

  document.getElementById('modalCallSubtitle').textContent = `${call.title} — ${call.department}`;

  const user  = getUserData();
  const draft = (getDrafts())[callId] || {};
  // For submitted applications the draft was deleted; fall back to the saved submission data
  const submissionRecord = subs.find(s => s.callId === callId);
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
</script>

<?php include('../../includes/footer.php'); ?>
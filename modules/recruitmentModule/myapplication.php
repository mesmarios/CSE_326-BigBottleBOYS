<?php
include('../../includes/layout.php');
include('../../includes/header.php');
include('../../includes/nav.php');

$bootCalls = [];
$bootSubmissions = [];
$bootUser = [];

if (!empty($_SESSION['user_id']) && isset($pdo)) {
  $candidateId = (int)$_SESSION['user_id'];

  try {
    $callsSql = "
      SELECT
        ja.id,
        ja.title,
        ja.status,
        COALESCE(d.name, '—') AS department,
        COALESCE(s.name, '—') AS school,
        COALESCE(c.name, '—') AS course_name,
        rp.start_date,
        rp.end_date
      FROM job_announcements ja
      INNER JOIN recruitment_periods rp ON rp.id = ja.period_id
      LEFT JOIN departments d ON d.id = ja.department_id
      LEFT JOIN schools s ON s.id = ja.school_id
      LEFT JOIN courses c ON c.id = ja.course_id
      WHERE ja.status IN ('published', 'closed')
      ORDER BY rp.start_date DESC, ja.id DESC
    ";
    $callsStmt = $pdo->query($callsSql);
    $callsRows = $callsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($callsRows as $row) {
      $bootCalls[] = [
        'id' => (string)$row['id'],
        'title' => $row['title'],
        'status' => $row['status'],
        'department' => $row['department'],
        'school' => $row['school'],
        'courses' => [$row['course_name']],
        'startDate' => $row['start_date'],
        'endDate' => $row['end_date'],
      ];
    }

    $appsSql = "
      SELECT
        ca.announcement_id,
        ca.status,
        ca.submitted_at,
        ja.title,
        COALESCE(d.name, '—') AS department
      FROM candidate_applications ca
      INNER JOIN job_announcements ja ON ja.id = ca.announcement_id
      LEFT JOIN departments d ON d.id = ja.department_id
      WHERE ca.candidate_id = :candidate_id
      ORDER BY ca.created_at DESC
    ";
    $appsStmt = $pdo->prepare($appsSql);
    $appsStmt->execute([':candidate_id' => $candidateId]);
    $appRows = $appsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($appRows as $row) {
      $statusMap = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'accepted' => 'Approved',
        'rejected' => 'Rejected',
        'withdrawn' => 'Rejected',
      ];

      $bootSubmissions[] = [
        'callId' => (string)$row['announcement_id'],
        'title' => $row['title'],
        'department' => $row['department'],
        'submittedDate' => $row['submitted_at'] ? date('Y-m-d', strtotime($row['submitted_at'])) : null,
        'status' => $statusMap[$row['status']] ?? 'Submitted',
      ];
    }

    $userStmt = $pdo->prepare('SELECT first_name, last_name, email, phone FROM users WHERE id = :id LIMIT 1');
    $userStmt->execute([':id' => $candidateId]);
    $userRow = $userStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $bootUser = [
      'name' => $userRow['first_name'] ?? '',
      'surname' => $userRow['last_name'] ?? '',
      'email' => $userRow['email'] ?? '',
      'phone' => $userRow['phone'] ?? '',
      'degree' => '',
      'institution' => '',
      'specialization' => '',
      'experience' => '',
      'summary' => '',
    ];
  } catch (Throwable $e) {
    // Keep frontend working with local fallback if DB bootstrap fails.
    $bootCalls = [];
    $bootSubmissions = [];
    $bootUser = [];
  }
}
?>
<link rel="stylesheet" href="../../recruitment/assets/css/myapplication.css">
<link rel="stylesheet" href="../../assets/css/user-ui.css">

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
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="s2Institution">
                Institution of Graduation <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="s2Institution"
                     placeholder="e.g. University of Athens" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="s2Specialization">
                Field of Specialization <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="s2Specialization"
                     placeholder="e.g. Computer Science" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="s2Experience">
                Years of Professional Experience <span class="text-danger">*</span>
              </label>
              <input type="number" class="form-control" id="s2Experience"
                     min="0" max="60" placeholder="0" />
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
  window.MYAPPLICATION_BOOTSTRAP = {
    calls: <?= json_encode($bootCalls, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    submissions: <?= json_encode($bootSubmissions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    user: <?= json_encode($bootUser, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
  };
</script>
<script src="../../recruitment/assets/js/myapplication.js"></script>

<?php include('../../includes/footer.php'); ?>
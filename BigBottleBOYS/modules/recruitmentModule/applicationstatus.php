<?php include('../../includes/layout.php'); include('../../includes/header.php'); include('../../includes/nav.php'); ?>
<link rel="stylesheet" href="../../recruitment/assets/css/applicationstatus.css">

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

<script src="../../recruitment/assets/js/applicationstatus.js"></script>


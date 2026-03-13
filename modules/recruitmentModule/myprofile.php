<?php
include('../../includes/layout.php');
include('../../includes/header.php');
include('../../includes/nav.php');

// Fetch full user record (header.php already validated session and set $pdo)
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

$profilePicSrc = (!empty($user['profilepic']))
    ? 'data:image/jpeg;base64,' . base64_encode($user['profilepic'])
    : '../../recruitment/assets/images/user2-160x160.jpg';
$fullName = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
?>
<link rel="stylesheet" href="../../recruitment/assets/css/myprofile.css">
<link rel="stylesheet" href="../../assets/css/user-ui.css">

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">My Profile</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <div class="container-fluid">
            <div class="row justify-content-center">
              <div class="col-xl-8 col-lg-10 col-12">

                <!-- ── Profile Banner ─────────────────────────── -->
                <div class="profile-banner mb-4 d-flex align-items-center gap-4">
                  <div class="profile-avatar-wrap">
                    <img
                      id="profilePicSmallBox"
                      src="<?= $profilePicSrc ?>"
                      alt="User Profile"
                    />
                  </div>
                  <div>
                    <h4 class="mb-0 fw-bold" id="bannerFullName"><?= $fullName ?></h4>
                    <p class="mb-0 opacity-75" style="font-size:.85rem;">
                      <i class="bi bi-envelope me-1"></i><span id="bannerEmail"><?= htmlspecialchars($user['email']) ?></span>
                    </p>
                  </div>
                </div>

                <!-- ── User Information Card ──────────────────── -->
                <div class="card profile-card mb-4">
                  <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="bi bi-person-fill me-2 text-primary"></i>User Information</h5>
                    <button id="editBtn" class="btn btn-sm btn-outline-primary btn-edit">
                      <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                  </div>
                  <div class="card-body" id="userTable">
                    <div class="info-row">
                      <span class="info-label">Name</span>
                      <span id="nameCell" class="info-value user-field" data-field="name"><?= htmlspecialchars($user['first_name']) ?></span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Surname</span>
                      <span id="surnameCell" class="info-value user-field" data-field="surname"><?= htmlspecialchars($user['last_name']) ?></span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Email</span>
                      <span id="emailCell" class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Address</span>
                      <span id="addressCell" class="info-value user-field" data-field="address"><?= htmlspecialchars($user['address'] ?? '—') ?></span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Phone</span>
                      <span id="phoneCell" class="info-value user-field" data-field="phone"><?= htmlspecialchars($user['phone'] ?? '—') ?></span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Date of Birth</span>
                      <span id="dobCell" class="info-value user-field" data-field="dob">—</span>
                    </div>
                  </div>
                </div>

                <!-- ── Academic / Professional Card ──────────── -->
                <div class="card profile-card mb-4">
                  <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>Academic / Professional Information</h5>
                    <button id="editAcademicBtn" class="btn btn-sm btn-outline-primary btn-edit">
                      <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                  </div>
                  <div class="card-body" id="academicTable">
                    <div id="academicValidationAlert" class="alert alert-danger d-none py-2 mb-3" role="alert"></div>
                    <div class="info-row">
                      <span class="info-label">Highest Degree</span>
                      <span id="degreeCell" class="info-value academic-field" data-field="degree">—</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Institution</span>
                      <span id="institutionCell" class="info-value academic-field" data-field="institution">—</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Specialization</span>
                      <span id="specializationCell" class="info-value academic-field" data-field="specialization">—</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Experience</span>
                      <span id="experienceCell" class="info-value academic-field" data-field="experience">—</span>
                    </div>
                    <div class="info-row" style="align-items:flex-start;">
                      <span class="info-label" style="padding-top:.15rem;">Summary</span>
                      <span id="summaryCell" class="info-value academic-field" data-field="summary" style="white-space:pre-wrap;">—</span>
                    </div>
                  </div>
                </div>

              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.container-fluid -->
        </div>
        <!--end::App Content-->

    <script src="../../recruitment/assets/js/myprofile.js"></script>

<?php include('../../includes/footer.php'); ?>

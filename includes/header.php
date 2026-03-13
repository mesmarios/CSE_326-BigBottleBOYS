<?php
// Session is already started and guard already ran in layout.php (included before this).
// DB connection — require_once so it won't double-load if already included.
require_once dirname(__DIR__) . '/database/db.php';

// Fetch minimal user data for navbar (profile pic + name)
$_nav_user = null;
try {
    $s = $pdo->prepare('SELECT first_name, last_name, email, profilepic FROM users WHERE id = :id');
    $s->execute([':id' => $_SESSION['user_id']]);
    $_nav_user = $s->fetch();
} catch (Exception $e) { /* fallback to session */ }

$_nav_full  = htmlspecialchars(
    ($_nav_user['first_name'] ?? $_SESSION['first_name'] ?? '') . ' ' .
    ($_nav_user['last_name']  ?? $_SESSION['last_name']  ?? '')
);
$_nav_email = htmlspecialchars($_nav_user['email'] ?? $_SESSION['email'] ?? '');
$_nav_role  = htmlspecialchars(ucfirst($_SESSION['role'] ?? 'user'));
$_nav_pic   = (!empty($_nav_user['profilepic']))
    ? 'data:image/jpeg;base64,' . base64_encode($_nav_user['profilepic'])
    : '../../recruitment/assets/images/user2-160x160.jpg';
// ─────────────────────────────────────────────────────────────────────────────
?>
    <!--begin::Header-->
    <nav class="app-header navbar navbar-expand bg-body">
      <!--begin::Container-->
      <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link sidebar-toggle-btn" data-lte-toggle="sidebar" href="#" role="button" title="Toggle Sidebar">
              <i class="bi bi-chevron-left sidebar-toggle-icon"></i>
            </a>
          </li>
        </ul>
        <!--end::Start Navbar Links-->
        <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">
          <!--begin::Notifications Dropdown Menu-->
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
              <i class="bi bi-bell-fill"></i>
              <span class="navbar-badge badge text-bg-warning">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <span class="dropdown-item dropdown-header">15 Notifications</span>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="bi bi-envelope me-2"></i> 4 new messages
                <span class="float-end text-secondary fs-7">3 mins</span>
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="bi bi-people-fill me-2"></i> 8 friend requests
                <span class="float-end text-secondary fs-7">12 hours</span>
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
                <span class="float-end text-secondary fs-7">2 days</span>
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item dropdown-footer"> See All Notifications </a>
            </div>
          </li>
          <!--end::Notifications Dropdown Menu-->
          <!--begin::User Menu Dropdown-->
          <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
              <img
                src="<?= $_nav_pic ?>"
                class="user-image rounded-circle shadow"
                alt="User Image"
              />
              <span class="d-none d-md-inline"><?= $_nav_full ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <!--begin::User Image-->
              <li class="user-header text-bg-primary">
                <img
                  src="<?= $_nav_pic ?>"
                  class="rounded-circle shadow"
                  alt="User Image"
                />
                <p>
                  <?= $_nav_full ?> — <?= $_nav_role ?>
                  <small><?= $_nav_email ?></small>
                </p>
              </li>
              <!--end::User Image-->
              <!--begin::Menu Footer-->
              <li class="user-footer">
                <a href="./myprofile.php" class="btn btn-default btn-flat">Profile</a>
                <a href="../../logout.php" class="btn btn-default btn-flat float-end">Sign out</a>
              </li>
              <!--end::Menu Footer-->
            </ul>
          </li>
          <!--end::User Menu Dropdown-->
        </ul>
        <!--end::End Navbar Links-->
      </div>
      <!--end::Container-->
    </nav>
    <!--end::Header-->
<html lang="en">
  <!--begin::Head-->

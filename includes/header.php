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
    <header class="app-header">
      <nav class="navbar navbar-expand bg-body h-100" aria-label="Primary">
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
    </header>
    <!--end::Header-->

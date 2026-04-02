<?php
// layout.php — HTML document boilerplate: doctype, <head>, opens <body> and <div class="app-wrapper">
// Include this as the very first file on every page.
// Optionally define $extra_head (string of <style>/<link>/<script> tags) before including to inject
// page-specific head content.

// ── Session & Auth guard (must run before ANY output) ──────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
  $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
  if (strpos($scriptName, '/modules/') !== false) {
    $basePath = strstr($scriptName, '/modules/', true);
    $loginUrl = rtrim($basePath, '/') . '/login.php';
  } else {
    $basePath = rtrim(dirname($scriptName), '/');
    $loginUrl = ($basePath === '' ? '' : $basePath) . '/login.php';
  }
    header('Location: ' . $loginUrl);
    exit;
}
// ───────────────────────────────────────────────────────────────────────────
?>
<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>CareerTrack</title>
    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->
    <!--begin::Primary Meta Tags-->
    <meta name="title" content="CareerTrack" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="CareerTrack is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant"
    />
    <!--end::Primary Meta Tags-->
    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="../../recruitment/assets/css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../../recruitment/assets/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin::No-transition on load (prevents sidebar shake during init)-->
    <style>.preload-no-transition,.preload-no-transition *{transition:none!important;animation:none!important}</style>
    <!--begin::Sidebar toggle styles-->
    <style>
      /* Smooth sidebar slide */
      .app-sidebar {
        transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                    margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                    transform 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
      }
      /* Main content shifts in sync */
      .app-wrapper > .app-main,
      .app-header {
        transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
      }
      /* Toggle button */
      .sidebar-toggle-btn {
        padding: 0.35rem 0.6rem;
        border-radius: 0.4rem;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
      }
      .sidebar-toggle-btn:hover {
        background-color: rgba(0,0,0,0.08);
      }
      /* Arrow icon — rotates 180° when sidebar collapses */
      .sidebar-toggle-icon {
        display: inline-block;
        font-size: 1rem;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform;
      }
      body.sidebar-collapse .sidebar-toggle-icon {
        transform: rotate(180deg);
      }
    </style>
    <!--end::Sidebar toggle styles-->
    <script>document.documentElement.classList.add('preload-no-transition');</script>
    <!--end::No-transition on load-->
    <!-- apexcharts -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
      integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
      crossorigin="anonymous"
    />
    <?php
    // allow pages to inject additional head content
    if (!empty(
        /** @var string|null */
        $extra_head
    )) {
        echo $extra_head;
    }
    ?>
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
    <script>
      // Re-enable transitions after the first painted frame so AdminLTE's
      // sidebar initialisation doesn't cause a visible shake/jump.
      window.addEventListener('DOMContentLoaded', function () {
        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            document.documentElement.classList.remove('preload-no-transition');
          });
        });
      });
    </script>

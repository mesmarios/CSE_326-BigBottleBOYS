<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Αν δεν υπάρχει maintenance.lock, πήγαινε στη login
if (!file_exists(__DIR__ . '/maintenance.lock')) {
    header('Location: login.php');
    exit;
}
// Admins πηγαίνουν στο admin dashboard
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin') {
    header('Location: modules/admin/index.php');
    exit;
}
?>
<!doctype html>
<html lang="el">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Υπό Συντήρηση — CareerTrack</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous">
  <link rel="stylesheet" href="recruitment/assets/css/adminlte.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    html, body {
      height: 100%;
      margin: 0;
      font-family: 'Source Sans 3', sans-serif;
      background-color: var(--bs-body-bg, #f4f6f9);
    }
    .maintenance-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .maintenance-card {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 4px 32px rgba(0,0,0,.10);
      max-width: 520px;
      width: 100%;
      padding: 3rem 2.5rem 2.5rem;
      text-align: center;
    }
    .maintenance-icon-wrap {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background: #fff3cd;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
    }
    .maintenance-icon-wrap i {
      font-size: 2.8rem;
      color: #f59e0b;
    }
    .maintenance-title {
      font-size: 1.75rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: .5rem;
    }
    .maintenance-subtitle {
      font-size: 1rem;
      color: #64748b;
      margin-bottom: 2rem;
      line-height: 1.6;
    }
    .maintenance-divider {
      border: none;
      border-top: 1px solid #e2e8f0;
      margin: 1.5rem 0;
    }
    .maintenance-eta {
      font-size: .875rem;
      color: #94a3b8;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .4rem;
    }
    .maintenance-progress {
      height: 6px;
      border-radius: 3px;
      background: #e2e8f0;
      overflow: hidden;
      margin: 1.25rem 0 .5rem;
    }
    .maintenance-progress-bar {
      height: 100%;
      width: 65%;
      background: linear-gradient(90deg, #3b82f6, #6366f1);
      border-radius: 3px;
      animation: progress-pulse 2s ease-in-out infinite;
    }
    @keyframes progress-pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: .6; }
    }
    .maintenance-brand {
      font-size: .8rem;
      color: #cbd5e1;
      margin-top: 2rem;
    }
    .maintenance-brand strong {
      color: #94a3b8;
    }
  </style>
</head>
<body>
  <div class="maintenance-wrapper">
    <div class="maintenance-card">

      <div class="maintenance-icon-wrap">
        <i class="bi bi-cone-striped"></i>
      </div>

      <h1 class="maintenance-title">Η Σελίδα Βρίσκεται<br>Υπό Συντήρηση</h1>

      <p class="maintenance-subtitle">
        Προχωράμε σε βελτιώσεις για να σας προσφέρουμε καλύτερη εμπειρία.<br>
        Θα επιστρέψουμε σύντομα. Ευχαριστούμε για την κατανόησή σας.
      </p>

      <div class="maintenance-progress">
        <div class="maintenance-progress-bar"></div>
      </div>

      <hr class="maintenance-divider">

      <div class="maintenance-eta">
        <i class="bi bi-clock"></i>
        Εκτιμώμενη ολοκλήρωση: σύντομα
      </div>

      <p class="maintenance-brand">
        &copy; <?= date('Y') ?> <strong>CareerTrack — ΤΕΠΑΚ</strong>
      </p>

    </div>
  </div>
</body>
</html>

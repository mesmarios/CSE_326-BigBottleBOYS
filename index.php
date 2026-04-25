<?php
declare(strict_types=1);

$projectBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($projectBasePath === '') {
  $projectBasePath = '/';
}

$buildProjectUrl = static function (string $path) use ($projectBasePath): string {
  $normalizedPath = ltrim($path, '/');
  if ($projectBasePath === '/') {
    return '/' . $normalizedPath;
  }

  return $projectBasePath . '/' . $normalizedPath;
};
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BigBottleBOYS | Landing</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
  <?php $authCssVersion = @filemtime(__DIR__ . '/authent.css') ?: time(); ?>
  <link href="authent.css?v=<?= $authCssVersion ?>" rel="stylesheet">
  <style>
    body.auth-page.landing-page {
      height: 100vh;
      min-height: 100vh;
      overflow: hidden;
      padding: 16px 18px;
    }

    .auth-wrapper {
      width: min(1020px, 100%);
      height: calc(100vh - 32px);
      max-height: calc(100vh - 32px);
    }

    .auth-left {
      padding: 28px 32px;
    }

    .auth-left h1 {
      font-size: 28px;
    }

    .auth-left p {
      font-size: 14px;
    }

    .landing-right {
      position: relative;
      gap: 10px;
      padding: 28px 34px;
      overflow: hidden;
    }

    .landing-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      color: #0b2a6b;
      background: #eaf2ff;
      border: 1px solid #c8dcff;
      width: fit-content;
    }

    .landing-title {
      margin: 0;
      font-size: 34px;
      line-height: 1.15;
      color: #13213d;
      font-weight: 800;
      letter-spacing: -0.02em;
    }

    .landing-subtitle {
      margin: 0;
      color: #5b6475;
      font-size: 15px;
      line-height: 1.45;
      max-width: 45ch;
    }

    .landing-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 12px;
      margin-top: 6px;
    }

    .landing-grid-modules {
      margin-top: 14px;
    }

    .landing-grid-auth {
      margin-top: 10px;
    }

    .landing-option {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 15px 16px;
      border-radius: 16px;
      text-decoration: none;
      border: 1px solid #e6ebf5;
      background: #fff;
      box-shadow: 0 6px 18px rgba(24, 39, 75, 0.06);
      color: inherit;
      transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .landing-option:hover {
      transform: translateY(-2px);
      border-color: #b8ccf5;
      box-shadow: 0 12px 24px rgba(24, 39, 75, 0.12);
    }

    .landing-option-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 19px;
      flex-shrink: 0;
    }

    .landing-option-admin .landing-option-icon {
      background: #e8f0ff;
      color: #1f5fbf;
    }

    .landing-option-recruitment .landing-option-icon {
      background: #eefbf3;
      color: #147d43;
    }

    .landing-option-enrollment .landing-option-icon {
      background: #fff4e8;
      color: #b45b00;
    }

    .landing-option-login .landing-option-icon {
      background: #e8f0ff;
      color: #1f5fbf;
    }

    .landing-option-register .landing-option-icon {
      background: #fff4e8;
      color: #b45b00;
    }

    .landing-option-title {
      margin: 0;
      font-size: 17px;
      font-weight: 700;
      color: #1b263f;
    }

    .landing-option-desc {
      margin: 2px 0 0;
      font-size: 13px;
      color: #677185;
      line-height: 1.4;
    }

    .landing-arrow {
      margin-left: auto;
      color: #98a2b7;
      font-size: 16px;
    }

    .landing-footer-note {
      font-size: 12px;
      color: #7a8398;
      margin-top: 6px;
    }

    @media (max-height: 860px) {
      body.auth-page.landing-page {
        padding: 12px 14px;
      }

      .auth-wrapper {
        height: calc(100vh - 24px);
        max-height: calc(100vh - 24px);
      }

      .auth-left {
        padding: 24px 28px;
      }

      .landing-right {
        padding: 24px 28px;
      }

      .landing-title {
        font-size: 30px;
      }

      .landing-option {
        padding: 13px 14px;
      }
    }

    @media (max-width: 768px) {
      body.auth-page.landing-page {
        overflow: auto;
        height: auto;
        min-height: 100vh;
      }

      .auth-wrapper {
        height: auto;
        max-height: none;
      }

      .landing-title {
        font-size: 24px;
      }

      .landing-right {
        overflow: visible;
      }
    }
  </style>
</head>
<body class="auth-page landing-page">
  <div class="auth-wrapper">
    <div class="auth-left">
      <div class="auth-left-logo">
        <img src="assets/images/17780_100tepak-logo.png" alt="ΤΕΠΑΚ Logo">
      </div>
      <header class="auth-left-content">
        <h1>Διαχείριση<br>Ειδικών Επιστημόνων<br>ΤΕΠΑΚ</h1>
        <p>Σύγχρονο περιβάλλον για υποβολές, αξιολογήσεις και διοικητική διαχείριση σε ένα σημείο.</p>
      </header>
    </div>

    <main class="auth-right landing-right">
      <h2 class="landing-title">Καλώς ήρθες</h2>
      <p class="landing-subtitle">
        Επίλεξε πρώτα το module που θέλεις να χρησιμοποιήσεις και μετά συνέχισε σε σύνδεση ή εγγραφή.
      </p>

      <section class="landing-grid landing-grid-modules" aria-label="Module options">
        <a class="landing-option landing-option-admin" href="<?= htmlspecialchars($buildProjectUrl('login.php?module=admin')) ?>">
          <span class="landing-option-icon"><i class="bi bi-shield-lock-fill"></i></span>
          <span>
            <h3 class="landing-option-title">Admin Module</h3>
            <p class="landing-option-desc">Dashboard, διαχείριση χρηστών, recruitment configuration και reports.</p>
          </span>
          <i class="bi bi-arrow-up-right landing-arrow"></i>
        </a>

        <a class="landing-option landing-option-recruitment" href="<?= htmlspecialchars($buildProjectUrl('login.php?module=recruitment')) ?>">
          <span class="landing-option-icon"><i class="bi bi-person-workspace"></i></span>
          <span>
            <h3 class="landing-option-title">Recruitment Module</h3>
            <p class="landing-option-desc">Προφίλ υποψηφίου, αιτήσεις και παρακολούθηση κατάστασης.</p>
          </span>
          <i class="bi bi-arrow-up-right landing-arrow"></i>
        </a>

        <a class="landing-option landing-option-enrollment" href="<?= htmlspecialchars($buildProjectUrl('login.php?module=enrollment')) ?>">
          <span class="landing-option-icon"><i class="bi bi-mortarboard-fill"></i></span>
          <span>
            <h3 class="landing-option-title">Enrollment Module</h3>
            <p class="landing-option-desc">LMS sync, full sync και αναφορές πρόσβασης ειδικών επιστημόνων.</p>
          </span>
          <i class="bi bi-arrow-up-right landing-arrow"></i>
        </a>
      </section>

      <section class="landing-grid landing-grid-auth" aria-label="Authentication options">
        <a class="landing-option landing-option-login" href="<?= htmlspecialchars($buildProjectUrl('login.php?module=recruitment')) ?>">
          <span class="landing-option-icon"><i class="bi bi-box-arrow-in-right"></i></span>
          <span>
            <h3 class="landing-option-title">Σύνδεση</h3>
            <p class="landing-option-desc">Γενική είσοδος στο σύστημα με role-based ανακατεύθυνση.</p>
          </span>
          <i class="bi bi-arrow-up-right landing-arrow"></i>
        </a>

        <a class="landing-option landing-option-register" href="<?= htmlspecialchars($buildProjectUrl('register.php')) ?>">
          <span class="landing-option-icon"><i class="bi bi-person-plus-fill"></i></span>
          <span>
            <h3 class="landing-option-title">Νέα Εγγραφή</h3>
            <p class="landing-option-desc">Δημιούργησε νέο candidate λογαριασμό για υποβολή αιτήσεων.</p>
          </span>
          <i class="bi bi-arrow-up-right landing-arrow"></i>
        </a>
      </section>

    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

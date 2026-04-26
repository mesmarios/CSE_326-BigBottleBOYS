<?php
declare(strict_types=1);

require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/includes/admin-branding.php';

$projectBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($projectBasePath === '') {
  $projectBasePath = '/';
}

$brandingContext = adminGetBrandingContext($pdo, 'assets/images');
$landingFavicon = $brandingContext['favicon'] ?? null;

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
  <?php if (!empty($landingFavicon)): ?>
  <link rel="icon" href="<?= htmlspecialchars($landingFavicon, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
  <?php $authCssVersion = @filemtime(__DIR__ . '/authent.css') ?: time(); ?>
  <link href="authent.css?v=<?= $authCssVersion ?>" rel="stylesheet">
  <style>
    .auth-wrapper {
      width: 1020px;
    }

    .auth-left {
      padding: 34px 36px;
    }

    .auth-left h1 {
      font-size: 30px;
    }

    .auth-left p {
      font-size: 14px;
    }

    .landing-right {
      position: relative;
      gap: 12px;
      padding: 34px 40px;
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
      font-size: 40px;
      line-height: 1.15;
      color: #13213d;
      font-weight: 800;
      letter-spacing: -0.02em;
    }

    .landing-subtitle {
      margin: 0;
      color: #5b6475;
      font-size: 17px;
      line-height: 1.6;
      max-width: 45ch;
    }

    .landing-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 14px;
      margin-top: 8px;
    }

    .landing-option {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 18px;
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
      width: 52px;
      height: 52px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 21px;
      flex-shrink: 0;
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
      font-size: 18px;
      font-weight: 700;
      color: #1b263f;
    }

    .landing-option-desc {
      margin: 2px 0 0;
      font-size: 14px;
      color: #677185;
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

    @media (max-width: 768px) {
      .landing-title {
        font-size: 24px;
      }
    }
  </style>
</head>
<body class="auth-page">
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
        Επίλεξε πώς θέλεις να συνεχίσεις.
      </p>

      <section class="landing-grid" aria-label="Role options">
        <a class="landing-option landing-option-login" href="<?= htmlspecialchars($buildProjectUrl('login.php')) ?>">
          <span class="landing-option-icon"><i class="bi bi-box-arrow-in-right"></i></span>
          <span>
            <h3 class="landing-option-title">Σύνδεση</h3>
            <p class="landing-option-desc">Μία είσοδος για όλους τους χρήστες και διαχειριστές.</p>
          </span>
          <i class="bi bi-arrow-up-right landing-arrow"></i>
        </a>

        <a class="landing-option landing-option-register" href="<?= htmlspecialchars($buildProjectUrl('register.php')) ?>">
          <span class="landing-option-icon"><i class="bi bi-person-plus-fill"></i></span>
          <span>
            <h3 class="landing-option-title">Νέα Εγγραφή</h3>
            <p class="landing-option-desc">Δημιούργησε λογαριασμό για πρόσβαση στο σύστημα.</p>
          </span>
          <i class="bi bi-arrow-up-right landing-arrow"></i>
        </a>
      </section>

    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

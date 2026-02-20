<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BigBottleBOYS | Landing</title>
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #eef2ff, #f8fafc);
      color: #0f172a;
    }
    .wrap {
      width: min(760px, 92vw);
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
      padding: 28px;
    }
    h1 { margin: 0 0 8px; font-size: 2rem; }
    p { margin: 0 0 18px; color: #334155; }
    .actions { display: flex; gap: 10px; flex-wrap: wrap; }
    a {
      text-decoration: none;
      font-weight: 600;
      padding: 10px 14px;
      border-radius: 8px;
      border: 1px solid #cbd5e1;
      color: #0f172a;
      background: #f8fafc;
    }
    a.primary {
      background: #2563eb;
      color: #fff;
      border-color: #2563eb;
    }
  </style>
</head>
<body>
  <main class="wrap">
    <h1>Welcome to BigBottleBOYS</h1>
    <p>This is the landing page visitors see first.</p>
    <div class="actions">
      <a class="primary" href="modules/admin/index.php">Go to Admin</a>
      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    </div>
  </main>
</body>
</html>

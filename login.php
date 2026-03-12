<?php
// Simple login redirect page
// - Click "Admin Login" to open the admin dashboard
// - Click "User Login" to go to the user dashboard

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['go_admin'])) {
      header('Location: modules/admin/index.php');
        exit;
    }
    if (isset($_POST['login'])) {
        // Normally you'd validate credentials here.
        header('Location: modules/recruitmentModule/index.php');
        exit;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login | Role Selection</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;background:#f3f4f6}
    .card{background:#fff;padding:24px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.08);width:320px}
    .actions{display:flex;gap:12px;justify-content:space-between;margin-top:18px}
    button{padding:10px 14px;border:0;border-radius:6px;cursor:pointer}
    .btn-primary{background:#007bff;color:#fff}
    .btn-secondary{background:#6c757d;color:#fff}
  </style>
</head>
<body>
  <div class="card">
    <h2 style="margin:0 0 12px 0">Welcome</h2>
    <form method="post">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" placeholder="you@example.com" style="width:100%;padding:8px;margin:6px 0 12px;border:1px solid #ddd;border-radius:4px">

      <label for="password">Password</label>
      <input id="password" name="password" type="password" placeholder="password" style="width:100%;padding:8px;margin:6px 0 12px;border:1px solid #ddd;border-radius:4px">

      <div class="actions">
        <button type="submit" name="go_admin" class="btn-secondary">Admin Login</button>
        <button type="submit" name="login" class="btn-primary">User Login</button>
      </div>
    </form>
  </div>
</body>
</html>

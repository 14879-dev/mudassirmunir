<?php
/**
 * Portfolio OS — Admin Login
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to dashboard
if (getAdminUser()) {
    header('Location: ' . APP_URL . '/admin/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';
    
    if (empty($email) || empty($pass)) {
        $error = "Please enter both email and password.";
    } else {
        $res = attemptLogin($email, $pass);
        if ($res['success']) {
            header('Location: ' . APP_URL . '/admin/dashboard.php');
            exit;
        } else {
            $error = $res['error'];
        }
    }
}

// Check URL params for session errors
if (isset($_GET['err'])) {
    if ($_GET['err'] === 'session') $error = "Session expired. Please log in again.";
    if ($_GET['err'] === 'locked') $error = "Account locked. Please try again later.";
}

$pageTitle = "Admin Login — Portfolio OS";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/design-tokens.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/neumorphic.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
  <style>
    body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: var(--space-4); }
    .login-card { width: 100%; max-width: 420px; }
  </style>
</head>
<body>

<div class="login-card neu-card">
  <div class="text-center mb-6">
    <div style="font-size:3rem; margin-bottom:var(--space-2);">🔐</div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">Admin Access</h1>
    <p class="text-muted text-sm">Portfolio Operating System</p>
  </div>

  <?php if ($error): ?>
    <div class="neu-alert neu-alert--error mb-4"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <?= csrfField() ?>
    
    <div class="neu-input-group neu-floating-label">
      <input type="email" id="email" name="email" class="neu-input" placeholder=" " required autofocus>
      <label for="email" class="neu-label">Email Address</label>
    </div>

    <div class="neu-input-group neu-floating-label mb-6">
      <input type="password" id="password" name="password" class="neu-input" placeholder=" " required>
      <label for="password" class="neu-label">Password</label>
    </div>

    <button type="submit" class="btn-neu btn-neu--primary w-full">
      Sign In
    </button>
  </form>
  
  <div class="text-center mt-6">
    <a href="<?= APP_URL ?>" class="text-muted text-sm" style="text-decoration:none;">
      <i class="bi bi-arrow-left"></i> Back to public site
    </a>
  </div>
</div>

</body>
</html>

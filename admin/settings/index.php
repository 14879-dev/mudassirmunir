<?php
/**
 * Portfolio OS — Admin Settings
 */
$pageTitle  = 'Settings';
$activeMenu = 'settings';
require_once dirname(__DIR__) . '/includes/admin-header.php';

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// The $adminUser array is provided by requireAdmin() from admin-header.php
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">Settings</h1>
    <p class="text-muted">Manage your admin account credentials and security.</p>
  </div>
  <a href="<?= APP_URL ?>/admin/dashboard.php" class="btn-neu btn-neu--secondary">
    <i class="bi bi-arrow-left"></i> Dashboard
  </a>
</div>

<?php if ($flashSuccess): ?>
  <div class="neu-alert neu-alert--success mb-4"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="neu-alert neu-alert--error mb-4"><?= e($flashError) ?></div>
<?php endif; ?>

<div class="row g-4">
  <!-- ── ACCOUNT INFO FORM ───────────────────────────── -->
  <div class="col-lg-6">
    <div class="neu-card h-100">
      <h2 style="font-size:var(--text-xl);font-weight:700;margin-bottom:var(--space-4);padding-bottom:var(--space-3);border-bottom:1px solid var(--color-border);">
        <i class="bi bi-person-badge me-2" style="color:var(--color-accent);"></i>Account Information
      </h2>
      <form method="POST" action="<?= APP_URL ?>/admin/settings/save.php">
        <?= csrfField() ?>
        <input type="hidden" name="action_type" value="update_email">

        <div class="neu-input-group mb-4">
          <input type="email" name="email" class="neu-input" value="<?= e($adminUser['email']) ?>" placeholder=" " required>
          <label class="neu-label">Email Address</label>
          <div class="form-text mt-2 text-muted" style="font-size:var(--text-xs);">
            This email is used for logging into the admin panel.
          </div>
        </div>

        <button type="submit" class="btn-neu btn-neu--primary">
          <i class="bi bi-save"></i> Save Email
        </button>
      </form>
    </div>
  </div>

  <!-- ── PASSWORD FORM ───────────────────────────── -->
  <div class="col-lg-6">
    <div class="neu-card h-100">
      <h2 style="font-size:var(--text-xl);font-weight:700;margin-bottom:var(--space-4);padding-bottom:var(--space-3);border-bottom:1px solid var(--color-border);">
        <i class="bi bi-shield-lock me-2" style="color:var(--color-error);"></i>Change Password
      </h2>
      <form method="POST" action="<?= APP_URL ?>/admin/settings/save.php">
        <?= csrfField() ?>
        <input type="hidden" name="action_type" value="update_password">

        <div class="neu-input-group mb-3">
          <input type="password" name="current_password" class="neu-input" placeholder=" " required>
          <label class="neu-label">Current Password</label>
        </div>
        
        <div class="neu-input-group mb-3">
          <input type="password" name="new_password" class="neu-input" placeholder=" " required minlength="8">
          <label class="neu-label">New Password</label>
        </div>
        
        <div class="neu-input-group mb-4">
          <input type="password" name="confirm_password" class="neu-input" placeholder=" " required minlength="8">
          <label class="neu-label">Confirm New Password</label>
        </div>

        <button type="submit" class="btn-neu" style="background:var(--color-error); color:white; border:none; box-shadow:0 4px 15px rgba(239,68,68,0.2);">
          <i class="bi bi-key"></i> Update Password
        </button>
      </form>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>

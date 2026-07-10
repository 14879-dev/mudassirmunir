<?php
/**
 * Portfolio OS — Admin CV Manager
 */
$pageTitle = 'Resume (CV) Management';
$activeMenu = 'cv';
require_once dirname(__DIR__) . '/includes/admin-header.php';
require_once dirname(__DIR__, 2) . '/includes/upload.php';

$error = '';
$success = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv_file'])) {
    verifyCsrf();
    
    $res = uploadCv($_FILES['cv_file']);
    if (!$res['success']) {
        $error = $res['error'];
    } else {
        // Mark existing CVs as not current
        Database::execute("UPDATE cv_files SET is_current = 0");
        
        // Insert new CV
        $newId = Database::insert(
            "INSERT INTO cv_files (filename, original_name, file_size, is_current, uploaded_by) VALUES (?, ?, ?, 1, ?)",
            [
                $res['filename'],
                sanitizeString($_FILES['cv_file']['name']),
                $_FILES['cv_file']['size'],
                $_SESSION['admin_user_id']
            ]
        );
        logAuditEvent($_SESSION['admin_user_id'], 'cv.upload', 'cv_files', (int)$newId);
        $success = "New CV uploaded and set as current.";
    }
}

// Fetch CV history
$cvs = Database::select(
    "SELECT c.*, u.email as uploader_email FROM cv_files c 
     LEFT JOIN users u ON u.id = c.uploaded_by 
     ORDER BY c.uploaded_at DESC LIMIT 10"
);
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">Resume (CV)</h1>
    <p class="text-muted">Upload and manage your publicly downloadable CV.</p>
  </div>
</div>

<?php if ($error): ?>
  <div class="neu-alert neu-alert--error mb-4"><?= e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="neu-alert neu-alert--success mb-4"><?= e($success) ?></div>
<?php endif; ?>

<div class="grid-2">
  
  <!-- Upload Form -->
  <div class="neu-card">
    <h3 style="font-size:var(--text-lg); font-weight:var(--weight-bold); margin-bottom:var(--space-4);">Upload New CV</h3>
    <form method="POST" enctype="multipart/form-data">
      <?= csrfField() ?>
      <div class="mb-4">
        <label class="neu-label">Select PDF File (Max 5MB)</label>
        <input type="file" name="cv_file" accept=".pdf,application/pdf" class="neu-input" style="padding: var(--space-2);" required>
      </div>
      <button type="submit" class="btn-neu btn-neu--primary w-full">
        <i class="bi bi-cloud-upload"></i> Upload & Replace
      </button>
    </form>
    <p class="text-xs text-muted mt-4">
      <i class="bi bi-info-circle"></i> Uploading a new CV will automatically set it as the active download on the public site. Old versions are kept for rollback.
    </p>
  </div>

  <!-- History -->
  <div class="neu-card">
    <h3 style="font-size:var(--text-lg); font-weight:var(--weight-bold); margin-bottom:var(--space-4);">Upload History</h3>
    
    <?php if (empty($cvs)): ?>
      <p class="text-muted">No CVs uploaded yet.</p>
    <?php else: ?>
      <div class="flex-col gap-3">
        <?php foreach ($cvs as $cv): ?>
          <div class="neu-card neu-card--inset p-3 flex-between">
            <div>
              <div class="font-semibold">
                <?= e($cv['original_name']) ?>
                <?php if ($cv['is_current']): ?>
                  <span class="neu-badge neu-badge--success ms-2">Current</span>
                <?php endif; ?>
              </div>
              <div class="text-xs text-muted mt-1">
                <?= date('M j, Y H:i', strtotime($cv['uploaded_at'])) ?> · 
                <?= round($cv['file_size'] / 1024, 1) ?> KB
              </div>
            </div>
            <?php if (!$cv['is_current']): ?>
              <!-- In a real app, you'd have a 'Make Current' or 'Delete' button here -->
              <span class="text-muted text-xs">Archived</span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>

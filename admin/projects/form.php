<?php
/**
 * Portfolio OS — Admin Projects: Add / Edit Form
 */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';

$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$project = null;

if ($isEdit) {
    $project = Database::selectOne("SELECT * FROM projects WHERE id = ?", [(int)$_GET['id']]);
    if (!$project) { header('Location: ' . APP_URL . '/admin/projects/'); exit; }
}

$pageTitle  = $isEdit ? 'Edit Project' : 'Add Project';
$activeMenu = 'projects';
require_once dirname(__DIR__) . '/includes/admin-header.php';

// Decode tags for display
$tagsStr = '';
if ($isEdit && !empty($project['tags'])) {
    $arr = json_decode($project['tags'], true);
    if (is_array($arr)) $tagsStr = implode(', ', $arr);
}
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);"><?= $isEdit ? 'Edit Project' : 'Add Project' ?></h1>
    <p class="text-muted"><?= $isEdit ? 'Update project details.' : 'Add a new project to your portfolio.' ?></p>
  </div>
  <a href="<?= APP_URL ?>/admin/projects/" class="btn-neu btn-neu--secondary">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<div class="neu-card" style="max-width:760px;">
  <form method="POST" action="<?= APP_URL ?>/admin/projects/save.php" enctype="multipart/form-data">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
      <input type="hidden" name="id" value="<?= (int)$project['id'] ?>">
    <?php endif; ?>

    <div class="grid-2" style="margin-bottom:var(--space-5);">
      <div class="neu-input-group">
        <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Title *</label>
        <input type="text" name="title" class="neu-input" required maxlength="255"
               value="<?= e($project['title'] ?? '') ?>" placeholder="My Awesome Project">
      </div>
      <div class="neu-input-group">
        <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Slug (URL key) *</label>
        <input type="text" name="slug" id="slug" class="neu-input" required maxlength="255"
               value="<?= e($project['slug'] ?? '') ?>" placeholder="my-awesome-project">
      </div>
    </div>

    <div class="neu-input-group" style="margin-bottom:var(--space-5);">
      <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Short Description</label>
      <input type="text" name="short_description" class="neu-input" maxlength="255"
             value="<?= e($project['short_description'] ?? '') ?>" placeholder="One-line summary for cards">
    </div>

    <div class="neu-input-group" style="margin-bottom:var(--space-5);">
      <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Full Description</label>
      <textarea name="full_description" class="neu-input neu-textarea" style="min-height:140px;" placeholder="Full project description..."><?= e($project['full_description'] ?? '') ?></textarea>
    </div>

    <div class="grid-2" style="margin-bottom:var(--space-5);">
      <div class="neu-input-group">
        <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">GitHub URL</label>
        <input type="url" name="github_url" class="neu-input"
               value="<?= e($project['github_url'] ?? '') ?>" placeholder="https://github.com/...">
      </div>
      <div class="neu-input-group">
        <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Demo URL</label>
        <input type="url" name="demo_url" class="neu-input"
               value="<?= e($project['demo_url'] ?? '') ?>" placeholder="https://demo.example.com">
      </div>
    </div>
    
    <div class="grid-2" style="margin-bottom:var(--space-5);">
      <div class="neu-input-group">
        <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Store URL</label>
        <input type="url" name="store_url" class="neu-input"
               value="<?= e($project['store_url'] ?? '') ?>" placeholder="https://play.google.com/...">
      </div>
      <div class="neu-input-group">
        <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Role</label>
        <input type="text" name="role" class="neu-input"
               value="<?= e($project['role'] ?? '') ?>" placeholder="Lead Developer">
      </div>
    </div>
    
    <div class="neu-input-group" style="margin-bottom:var(--space-5);">
      <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Tech Stack (comma-separated)</label>
      <?php
        $techStr = '';
        if ($isEdit && !empty($project['tech_stack'])) {
            $tArr = json_decode($project['tech_stack'], true);
            if (is_array($tArr)) $techStr = implode(', ', $tArr);
        }
      ?>
      <input type="text" name="tech_stack" class="neu-input"
             value="<?= e($techStr) ?>" placeholder="React, Node.js, MongoDB">
    </div>

    <div class="grid-2" style="margin-bottom:var(--space-5);">
      <div class="neu-input-group">
        <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Tags (comma-separated)</label>
        <input type="text" name="tags" class="neu-input"
               value="<?= e($tagsStr) ?>" placeholder="React, PHP, MySQL">
      </div>
      <div class="neu-input-group">
        <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Status</label>
        <select name="is_published" class="neu-input">
          <option value="1" <?= ($project['is_published'] ?? 1) == 1 ? 'selected' : '' ?>>Published</option>
          <option value="0" <?= isset($project['is_published']) && $project['is_published'] == 0 ? 'selected' : '' ?>>Draft</option>
        </select>
      </div>
    </div>

    <div class="grid-2" style="margin-bottom:var(--space-5);">
      <div class="neu-input-group">
        <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Thumbnail Image</label>
        <?php if (!empty($project['thumbnail'])): ?>
          <div style="margin-bottom:8px;">
            <img src="<?= APP_URL ?>/uploads/projects/<?= e($project['thumbnail']) ?>" alt="Thumbnail" style="height:60px;border-radius:var(--radius-sm);object-fit:cover;">
          </div>
          <label style="font-size:var(--text-xs);color:var(--color-text-muted);">
            <input type="checkbox" name="remove_thumbnail"> Remove current thumbnail
          </label><br><br>
        <?php endif; ?>
        <input type="file" name="thumbnail" class="neu-input" accept="image/jpeg,image/png,image/webp">
        <small class="text-muted" style="font-size:var(--text-xs);">Max 2MB. JPG, PNG or WebP.</small>
      </div>
      <div class="neu-input-group">
        <label class="neu-label" style="position:static;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px;display:block;">Sort Order</label>
        <input type="number" name="sort_order" class="neu-input" min="0" max="999"
               value="<?= (int)($project['sort_order'] ?? 0) ?>" placeholder="0">
        <small class="text-muted" style="font-size:var(--text-xs);">Lower = shown first.</small>
      </div>
    </div>

    <div style="display:flex; gap:var(--space-3); justify-content:flex-end;">
      <a href="<?= APP_URL ?>/admin/projects/" class="btn-neu btn-neu--ghost">Cancel</a>
      <button type="submit" class="btn-neu btn-neu--primary">
        <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Save Changes' : 'Create Project' ?>
      </button>
    </div>
  </form>
</div>

<script>
// Auto-generate slug from title
document.querySelector('[name="title"]').addEventListener('input', function() {
  const slugField = document.getElementById('slug');
  if (slugField && !<?= $isEdit ? 'true' : 'false' ?>) {
    slugField.value = this.value
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/\s+/g, '-');
  }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>

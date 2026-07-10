<?php
/**
 * Portfolio OS — Admin Projects: List
 */
$pageTitle  = 'Projects';
$activeMenu = 'projects';
require_once dirname(__DIR__) . '/includes/admin-header.php';

$projects = Database::select("SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC");

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">Projects</h1>
    <p class="text-muted">Manage your portfolio showcase. <?= count($projects) ?> project(s) total.</p>
  </div>
  <a href="<?= APP_URL ?>/admin/projects/form.php" class="btn-neu btn-neu--primary">
    <i class="bi bi-plus-lg"></i> Add Project
  </a>
</div>

<?php if ($flashSuccess): ?>
  <div class="neu-alert neu-alert--success mb-4"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="neu-alert neu-alert--error mb-4"><?= e($flashError) ?></div>
<?php endif; ?>

<div class="neu-card" style="padding: 0; overflow: hidden;">
  <div class="table-responsive">
    <table class="table table-hover table-borderless mb-0" style="color:var(--color-text-secondary);">
      <thead style="background:var(--color-surface-2); border-bottom:1px solid var(--color-border);">
        <tr>
          <th class="ps-4" style="width:60px;">#</th>
          <th>Project</th>
          <th>Status</th>
          <th>Tags</th>
          <th>Date Added</th>
          <th class="pe-4 text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($projects)): ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">No projects yet. <a href="<?= APP_URL ?>/admin/projects/form.php">Add your first one →</a></td></tr>
        <?php else: ?>
          <?php foreach ($projects as $i => $p):
            $tags = json_decode($p['tags'] ?? '[]', true) ?: [];
            $isPublished = (bool)($p['is_published'] ?? 1);
            $sc = $isPublished ? 'success' : 'secondary';
            $statusText = $isPublished ? 'Published' : 'Draft';
          ?>
          <tr style="border-bottom:1px solid var(--color-border);">
            <td class="ps-4" style="vertical-align:middle; color:var(--color-text-muted);"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></td>
            <td style="vertical-align:middle;">
              <div class="flex items-center gap-3">
                <?php if (!empty($p['thumbnail'])): ?>
                  <img src="<?= APP_URL ?>/uploads/projects/<?= e($p['thumbnail']) ?>" alt="" style="width:44px;height:36px;object-fit:cover;border-radius:var(--radius-sm);flex-shrink:0;">
                <?php else: ?>
                  <div style="width:44px;height:36px;background:var(--color-surface-2);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-image text-muted"></i>
                  </div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:600;color:var(--color-text-primary);"><?= e($p['title']) ?></div>
                  <div style="font-size:var(--text-xs);color:var(--color-text-muted);"><?= e($p['slug']) ?></div>
                </div>
              </div>
            </td>
            <td style="vertical-align:middle;">
              <span class="badge bg-<?= $sc ?>"><?= e($statusText) ?></span>
            </td>
            <td style="vertical-align:middle;">
              <div style="display:flex;gap:4px;flex-wrap:wrap;max-width:200px;">
                <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                  <span class="tag tag--neutral" style="font-size:10px;padding:2px 7px;"><?= e($tag) ?></span>
                <?php endforeach; ?>
                <?php if (count($tags) > 3): ?>
                  <span class="tag tag--neutral" style="font-size:10px;padding:2px 7px;">+<?= count($tags)-3 ?></span>
                <?php endif; ?>
              </div>
            </td>
            <td style="vertical-align:middle;white-space:nowrap;"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
            <td class="pe-4 text-end" style="vertical-align:middle;white-space:nowrap;">
              <a href="<?= APP_URL ?>/admin/projects/form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                <i class="bi bi-pencil"></i>
              </a>
              <?php if (!empty($p['demo_url'])): ?>
                <a href="<?= e($p['demo_url']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="View Demo">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
              <?php elseif (!empty($p['github_url'])): ?>
                <a href="<?= e($p['github_url']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="View GitHub">
                  <i class="bi bi-github"></i>
                </a>
              <?php endif; ?>
              <form method="POST" action="<?= APP_URL ?>/admin/projects/delete.php" style="display:inline-block;"
                    onsubmit="return confirm('Delete &quot;<?= e(addslashes($p['title'])) ?>&quot;? This cannot be undone.');">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>

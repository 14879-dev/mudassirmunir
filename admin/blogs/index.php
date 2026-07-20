<?php
/**
 * Portfolio OS — Admin Blogs
 */
$pageTitle  = 'Manage Blogs';
$activeMenu = 'blogs';
require_once dirname(__DIR__) . '/includes/admin-header.php';

$blogs = Database::select("SELECT id, title, slug, is_published, views, created_at FROM blogs ORDER BY created_at DESC");

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">Blogs</h1>
    <p class="text-muted">Manage your blog posts.</p>
  </div>
  <a href="<?= APP_URL ?>/admin/blogs/form.php" class="btn-neu btn-neu--primary">
    <i class="bi bi-plus-lg"></i> New Post
  </a>
</div>

<?php if ($flashSuccess): ?>
  <div class="neu-alert neu-alert--success mb-4"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="neu-alert neu-alert--error mb-4"><?= e($flashError) ?></div>
<?php endif; ?>

<div class="neu-card">
  <div class="table-responsive">
    <table class="table align-middle text-nowrap mb-0" style="color:var(--color-text-primary);">
      <thead style="border-bottom:2px solid var(--color-border); font-size:var(--text-sm);">
        <tr>
          <th>Title</th>
          <th>Views</th>
          <th>Status</th>
          <th>Date</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($blogs)): ?>
          <tr>
            <td colspan="5" class="text-center text-muted py-5">No blog posts found. Create one!</td>
          </tr>
        <?php else: ?>
          <?php foreach ($blogs as $b): ?>
            <tr style="border-bottom:1px solid rgba(255,255,255,0.4);">
              <td class="fw-semibold">
                <?= e($b['title']) ?><br>
                <small class="text-muted fw-normal">/blog.php?slug=<?= e($b['slug']) ?></small>
              </td>
              <td><?= e($b['views']) ?></td>
              <td>
                <?php if ($b['is_published']): ?>
                  <span class="neu-badge neu-badge--success">Published</span>
                <?php else: ?>
                  <span class="neu-badge neu-badge--peach">Draft</span>
                <?php endif; ?>
              </td>
              <td class="text-muted"><?= date('M j, Y', strtotime($b['created_at'])) ?></td>
              <td class="text-end">
                <a href="<?= APP_URL ?>/blog.php?slug=<?= e($b['slug']) ?>" class="btn btn--sm btn--ghost" target="_blank" title="View"><i class="bi bi-eye"></i></a>
                <a href="<?= APP_URL ?>/admin/blogs/form.php?id=<?= $b['id'] ?>" class="btn btn--sm btn--secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="<?= APP_URL ?>/admin/blogs/delete.php" class="d-inline-block" onsubmit="return confirm('Delete this post?');">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= $b['id'] ?>">
                  <button type="submit" class="btn btn--sm btn--ghost" style="color:var(--color-error); border-color:var(--color-error);" title="Delete">
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

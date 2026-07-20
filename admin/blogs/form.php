<?php
/**
 * Portfolio OS — Admin Blog Form
 */
$pageTitle  = 'Blog Form';
$activeMenu = 'blogs';
require_once dirname(__DIR__) . '/includes/admin-header.php';

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;

$blog = [
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'cover_image' => '',
    'is_published' => 1
];

if ($isEdit) {
    $record = Database::selectOne("SELECT * FROM blogs WHERE id = ?", [$id]);
    if ($record) {
        $blog = $record;
        $pageTitle = 'Edit Blog';
    } else {
        redirect('/admin/blogs/');
    }
} else {
    $pageTitle = 'New Blog';
}

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);"><?= $isEdit ? 'Edit Post' : 'New Post' ?></h1>
  </div>
  <a href="<?= APP_URL ?>/admin/blogs/" class="btn-neu btn-neu--secondary">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<?php if ($flashError): ?>
  <div class="neu-alert neu-alert--error mb-4"><?= e($flashError) ?></div>
<?php endif; ?>

<div class="neu-card">
  <form method="POST" action="<?= APP_URL ?>/admin/blogs/save.php" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <label class="neu-label position-static mb-2">Title</label>
        <input type="text" name="title" class="neu-input" value="<?= e($blog['title']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="neu-label position-static mb-2">URL Slug (e.g. my-first-blog)</label>
        <input type="text" name="slug" class="neu-input" value="<?= e($blog['slug']) ?>" required>
      </div>
    </div>

    <div class="mb-4">
      <label class="neu-label position-static mb-2">Excerpt (Short description for SEO & cards)</label>
      <textarea name="excerpt" class="neu-input" rows="2"><?= e($blog['excerpt']) ?></textarea>
    </div>

    <div class="mb-4">
      <label class="neu-label position-static mb-2">Content (Markdown or HTML)</label>
      <textarea name="content" class="neu-input" rows="12" required><?= e($blog['content']) ?></textarea>
    </div>

    <div class="mb-4">
      <label class="neu-label position-static mb-2">Cover Image</label>
      <input type="file" name="cover_image" class="neu-input" accept="image/*">
      <?php if (!empty($blog['cover_image'])): ?>
        <div class="mt-2 text-muted" style="font-size:var(--text-xs);">
          Current image: <?= e($blog['cover_image']) ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="mb-5 form-check form-switch" style="font-size:var(--text-lg);">
      <input class="form-check-input" type="checkbox" name="is_published" id="isPub" value="1" <?= $blog['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label ms-2" for="isPub">Published to public site</label>
    </div>

    <button type="submit" class="btn-neu btn-neu--primary">
      <i class="bi bi-save"></i> Save Post
    </button>
  </form>
</div>

<?php require_once dirname(__DIR__) . '/../includes/admin-footer.php'; ?>

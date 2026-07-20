<?php
/**
 * Portfolio OS — Admin Blog Form (New / Edit)
 */
$pageTitle  = 'Blog Post';
$activeMenu = 'blogs';
require_once dirname(__DIR__) . '/includes/admin-header.php';

$id     = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$blog   = ['id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'cover_image' => '', 'is_published' => 1];

if ($isEdit) {
    $record = Database::selectOne("SELECT * FROM blogs WHERE id = ?", [$id]);
    if ($record) {
        $blog = $record;
    } else {
        header('Location: ' . APP_URL . '/admin/blogs/');
        exit;
    }
}

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>

<!-- ── Top bar ── -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
  <div>
    <h1 style="font-size:1.6rem; font-weight:700; color:#0f1923; margin:0;">
      <?= $isEdit ? '✏️ Edit Blog Post' : '✍️ New Blog Post' ?>
    </h1>
    <p style="color:#6b7280; margin:4px 0 0 0; font-size:.875rem;">
      <?= $isEdit ? 'Update the details below.' : 'Fill in the details below to publish a new post.' ?>
    </p>
  </div>
  <a href="<?= APP_URL ?>/admin/blogs/"
     style="display:inline-flex; align-items:center; gap:6px; padding:10px 20px;
            background:#f1f5f9; border:1px solid #d1d5db; border-radius:10px;
            color:#374151; text-decoration:none; font-size:.875rem; font-weight:600;">
    <i class="bi bi-arrow-left"></i> Back to Blogs
  </a>
</div>

<?php if ($flashError): ?>
  <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:10px;
              padding:14px 18px; margin-bottom:1.5rem; color:#b91c1c; font-size:.875rem;">
    <i class="bi bi-exclamation-circle"></i> <?= e($flashError) ?>
  </div>
<?php endif; ?>

<!-- ── Form Card ── -->
<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px;
            box-shadow:0 4px 24px rgba(0,0,0,0.07); padding:2rem;">

  <form method="POST" action="<?= APP_URL ?>/admin/blogs/save.php" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= (int)$blog['id'] ?>">

    <!-- Row 1: Title + Slug -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
      <div>
        <label style="display:block; font-size:.8125rem; font-weight:600; color:#374151; margin-bottom:6px;">
          Post Title <span style="color:#ef4444;">*</span>
        </label>
        <input type="text" name="title" value="<?= e($blog['title']) ?>" required
               placeholder="My Awesome Blog Post"
               style="width:100%; padding:11px 14px; border:1.5px solid #d1d5db;
                      border-radius:10px; font-size:.9rem; color:#111827;
                      background:#f9fafb; box-sizing:border-box; outline:none;"
               onfocus="this.style.borderColor='#5b8ef0'; this.style.background='#fff';"
               onblur="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';">
      </div>
      <div>
        <label style="display:block; font-size:.8125rem; font-weight:600; color:#374151; margin-bottom:6px;">
          URL Slug <span style="color:#ef4444;">*</span>
          <span style="font-weight:400; color:#9ca3af; margin-left:4px;">(e.g. my-awesome-post)</span>
        </label>
        <input type="text" name="slug" value="<?= e($blog['slug']) ?>" required
               placeholder="my-awesome-post"
               style="width:100%; padding:11px 14px; border:1.5px solid #d1d5db;
                      border-radius:10px; font-size:.9rem; color:#111827;
                      background:#f9fafb; box-sizing:border-box; outline:none;"
               onfocus="this.style.borderColor='#5b8ef0'; this.style.background='#fff';"
               onblur="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';">
      </div>
    </div>

    <!-- Excerpt -->
    <div style="margin-bottom:1.25rem;">
      <label style="display:block; font-size:.8125rem; font-weight:600; color:#374151; margin-bottom:6px;">
        Excerpt
        <span style="font-weight:400; color:#9ca3af; margin-left:4px;">(Short description shown on cards & SEO)</span>
      </label>
      <textarea name="excerpt" rows="2" placeholder="A short summary of the post..."
                style="width:100%; padding:11px 14px; border:1.5px solid #d1d5db;
                       border-radius:10px; font-size:.9rem; color:#111827;
                       background:#f9fafb; box-sizing:border-box; resize:vertical; outline:none;"
                onfocus="this.style.borderColor='#5b8ef0'; this.style.background='#fff';"
                onblur="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';"><?= e($blog['excerpt']) ?></textarea>
    </div>

    <!-- Content -->
    <div style="margin-bottom:1.25rem;">
      <label style="display:block; font-size:.8125rem; font-weight:600; color:#374151; margin-bottom:6px;">
        Content <span style="color:#ef4444;">*</span>
        <span style="font-weight:400; color:#9ca3af; margin-left:4px;">(Supports HTML)</span>
      </label>
      <textarea name="content" rows="14" required placeholder="Write your blog content here... (HTML is supported)"
                style="width:100%; padding:11px 14px; border:1.5px solid #d1d5db;
                       border-radius:10px; font-size:.875rem; color:#111827; font-family:monospace;
                       background:#f9fafb; box-sizing:border-box; resize:vertical; outline:none;"
                onfocus="this.style.borderColor='#5b8ef0'; this.style.background='#fff';"
                onblur="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';"><?= e($blog['content']) ?></textarea>
    </div>

    <!-- Cover Image -->
    <div style="margin-bottom:1.25rem;">
      <label style="display:block; font-size:.8125rem; font-weight:600; color:#374151; margin-bottom:6px;">
        Cover Image
      </label>
      <?php if (!empty($blog['cover_image'])): ?>
        <div style="margin-bottom:10px; display:flex; align-items:center; gap:10px;">
          <img src="<?= APP_URL ?>/uploads/blogs/<?= e($blog['cover_image']) ?>"
               alt="Current Cover" style="height:60px; border-radius:8px; border:1px solid #e2e8f0;">
          <span style="font-size:.8rem; color:#6b7280;">Current: <?= e($blog['cover_image']) ?> — Upload new to replace.</span>
        </div>
      <?php endif; ?>
      <input type="file" name="cover_image" accept="image/*"
             style="width:100%; padding:10px 14px; border:1.5px dashed #d1d5db;
                    border-radius:10px; font-size:.875rem; color:#374151;
                    background:#f9fafb; box-sizing:border-box; cursor:pointer;">
    </div>

    <!-- Divider -->
    <div style="height:1px; background:#e5e7eb; margin:1.5rem 0;"></div>

    <!-- Publish toggle + Submit -->
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
      <label style="display:flex; align-items:center; gap:10px; cursor:pointer; user-select:none;">
        <input type="checkbox" name="is_published" id="isPub" value="1"
               <?= !empty($blog['is_published']) ? 'checked' : '' ?>
               style="width:18px; height:18px; cursor:pointer; accent-color:#5b8ef0;">
        <span style="font-size:.9375rem; font-weight:600; color:#374151;">
          Publish to public site
        </span>
        <span style="font-size:.8rem; color:#9ca3af;">(Uncheck to save as draft)</span>
      </label>

      <button type="submit"
              style="display:inline-flex; align-items:center; gap:8px;
                     padding:12px 28px; background:linear-gradient(135deg,#5b8ef0,#7bb3ff);
                     color:#fff; border:none; border-radius:10px; font-size:.9375rem;
                     font-weight:700; cursor:pointer; box-shadow:0 4px 14px rgba(91,142,240,0.4);"
              onmouseover="this.style.opacity='.9';" onmouseout="this.style.opacity='1';">
        <i class="bi bi-save"></i>
        <?= $isEdit ? 'Update Post' : 'Publish Post' ?>
      </button>
    </div>

  </form>
</div>

<!-- Auto-slug from title -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  var titleInput = document.querySelector('input[name="title"]');
  var slugInput  = document.querySelector('input[name="slug"]');
  if (titleInput && slugInput && !slugInput.value) {
    titleInput.addEventListener('input', function () {
      slugInput.value = this.value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    });
  }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>

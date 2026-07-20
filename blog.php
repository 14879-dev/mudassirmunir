<?php
/**
 * Portfolio OS — Single Blog Post
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

startSecureSession();
sendSecurityHeaders();

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . APP_URL . '/blogs.php');
    exit;
}

$post = Database::selectOne("SELECT * FROM blogs WHERE slug = ? AND is_published = 1", [$slug]);
if (!$post) {
    http_response_code(404);
    $pageTitle  = "Post Not Found — Mudassir";
    $pageDesc   = "The blog post you're looking for doesn't exist.";
    $activePage = 'blog';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container text-center py-5"><h1>404 — Post Not Found</h1><a href="' . APP_URL . '/blogs.php" class="btn btn--primary mt-4">Back to Blogs</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Increment view count
Database::execute("UPDATE blogs SET views = views + 1 WHERE id = ?", [$post['id']]);

$pageTitle  = e($post['title']) . " — Mudassir's Blog";
$pageDesc   = e($post['excerpt'] ?: mb_substr(strip_tags($post['content'] ?? ''), 0, 160));
$activePage = 'blog';
if (!empty($post['cover_image'])) {
    $ogImage = APP_URL . '/uploads/blogs/' . $post['cover_image'];
}

require_once __DIR__ . '/includes/header.php';
?>

<article class="page-section" style="min-height:90vh;" itemscope itemtype="https://schema.org/BlogPosting">
  <div class="container" style="max-width:800px;">

    <!-- Back button -->
    <div class="reveal mb-6">
      <a href="<?= APP_URL ?>/blogs.php" class="btn btn--ghost btn--sm">
        <i class="bi bi-arrow-left"></i> All Blogs
      </a>
    </div>

    <!-- Cover Image -->
    <?php if (!empty($post['cover_image'])): ?>
      <div class="reveal mb-6" style="border-radius:var(--radius-xl);overflow:hidden;max-height:420px;">
        <img src="<?= APP_URL ?>/uploads/blogs/<?= e($post['cover_image']) ?>"
             alt="<?= e($post['title']) ?>"
             style="width:100%;height:420px;object-fit:cover;"
             itemprop="image">
      </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="reveal mb-6">
      <div class="blog-card__meta mb-3">
        <span><i class="bi bi-calendar3"></i> <time itemprop="datePublished" datetime="<?= date('Y-m-d', strtotime($post['created_at'])) ?>"><?= date('F j, Y', strtotime($post['created_at'])) ?></time></span>
        <span><i class="bi bi-eye"></i> <?= (int)$post['views'] + 1 ?> views</span>
      </div>
      <h1 itemprop="headline" style="font-family:var(--font-display);font-size:clamp(2rem,5vw,3.5rem);font-weight:800;line-height:1.1;margin-bottom:var(--space-4);"><?= e($post['title']) ?></h1>
      <?php if (!empty($post['excerpt'])): ?>
        <p itemprop="description" style="font-size:var(--text-lg);color:var(--color-text-secondary);line-height:1.7;"><?= e($post['excerpt']) ?></p>
      <?php endif; ?>
    </header>

    <!-- Content -->
    <div class="neu-card reveal" style="line-height:1.85;font-size:var(--text-base);color:var(--color-text-primary);" itemprop="articleBody">
      <?= nl2br(e($post['content'])) ?>
    </div>

    <!-- Footer -->
    <div class="reveal text-center mt-8">
      <div class="neu-divider mb-6"></div>
      <p class="text-muted mb-4">Thanks for reading!</p>
      <a href="<?= APP_URL ?>/blogs.php" class="btn btn--ghost btn--lg">
        <i class="bi bi-journal-text"></i> More Posts
      </a>
      <a href="<?= APP_URL ?>/#contact" class="btn btn--primary btn--lg ms-3">
        <i class="bi bi-envelope"></i> Get in Touch
      </a>
    </div>

  </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

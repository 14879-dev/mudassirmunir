<?php
/**
 * Portfolio OS — Public Blogs Listing Page
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

startSecureSession();
sendSecurityHeaders();

$blogs = Database::select("SELECT id, title, slug, excerpt, cover_image, views, created_at FROM blogs WHERE is_published=1 ORDER BY created_at DESC");

$pageTitle  = "Blog — Mudassir Munir";
$pageDesc   = "Thoughts, insights, and articles by Mudassir Munir on web development, game development, and software engineering.";
$activePage = 'blog';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ─────────────────────────────────────────────────────── -->
<!--  BLOGS LISTING PAGE                                    -->
<!-- ─────────────────────────────────────────────────────── -->
<section class="page-section" style="min-height:90vh;" aria-labelledby="blogs-page-heading">
  <div class="container">

    <div class="section-header reveal">
      <div class="overline">THOUGHTS & INSIGHTS</div>
      <h1 class="section-title" id="blogs-page-heading">ALL <span style="color:var(--color-accent);">BLOGS</span></h1>
      <p class="section-sub">Writing about code, design, and building things.</p>
    </div>

    <?php if (empty($blogs)): ?>
      <div class="text-center py-5 reveal">
        <i class="bi bi-journal-x" style="font-size:4rem;opacity:.25;color:var(--color-accent);"></i>
        <h3 style="margin-top:var(--space-4);color:var(--color-text-secondary);">No posts yet</h3>
        <p class="text-muted">Check back soon — great content is coming!</p>
        <a href="<?= APP_URL ?>/" class="btn btn--primary mt-4"><i class="bi bi-house"></i> Back Home</a>
      </div>
    <?php else: ?>
      <div class="blog-grid reveal">
        <?php foreach ($blogs as $b): ?>
          <a href="<?= APP_URL ?>/blog.php?slug=<?= e($b['slug']) ?>" class="blog-card" style="text-decoration:none;">
            <?php if (!empty($b['cover_image'])): ?>
              <div class="blog-card__img">
                <img src="<?= APP_URL ?>/uploads/blogs/<?= e($b['cover_image']) ?>" alt="<?= e($b['title']) ?>" loading="lazy">
              </div>
            <?php else: ?>
              <div class="blog-card__img blog-card__img--placeholder">
                <i class="bi bi-journal-richtext"></i>
              </div>
            <?php endif; ?>
            <div class="blog-card__body">
              <div class="blog-card__meta">
                <span><i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($b['created_at'])) ?></span>
                <span><i class="bi bi-eye"></i> <?= e($b['views']) ?> views</span>
              </div>
              <h2 class="blog-card__title"><?= e($b['title']) ?></h2>
              <?php if (!empty($b['excerpt'])): ?>
                <p class="blog-card__excerpt"><?= e(mb_substr($b['excerpt'], 0, 150)) ?><?= strlen($b['excerpt']) > 150 ? '…' : '' ?></p>
              <?php endif; ?>
              <span class="blog-card__read">Read more <i class="bi bi-arrow-right"></i></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * Portfolio OS — Public All Projects Showcase Page
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

startSecureSession();
sendSecurityHeaders();

$projects = Database::select("SELECT * FROM projects WHERE is_published=1 ORDER BY sort_order ASC, created_at DESC");

// Collect all unique tags
$allTags = [];
foreach ($projects as $p) {
    foreach ((json_decode($p['tags'] ?? '[]', true) ?: []) as $t) {
        $allTags[$t] = true;
    }
}
$allTags = array_keys($allTags);
sort($allTags);

$pageTitle  = "All Projects & Case Studies — Mudassir Munir";
$pageDesc   = "Explore the complete portfolio of web applications, AI systems, Unity games, and software projects built by Mudassir Munir.";
$activePage = 'projects';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ─────────────────────────────────────────────────────── -->
<!--  ALL PROJECTS SHOWCASE PAGE                            -->
<!-- ─────────────────────────────────────────────────────── -->
<section class="page-section" style="min-height:90vh; padding-top:100px;" aria-labelledby="all-projects-heading">
  <div class="container">

    <div class="section-header reveal text-center mb-6">
      <div class="overline" style="color:var(--color-accent); font-weight:700; letter-spacing:.15em; font-size:var(--text-xs); text-transform:uppercase; margin-bottom:8px;">
        <i class="bi bi-layers-fill me-1"></i> COMPLETE ARCHIVE
      </div>
      <h1 class="section-title" id="all-projects-heading" style="font-size:clamp(2.4rem, 6vw, 4.2rem); font-weight:800;">
        ALL <span style="background:linear-gradient(135deg,#7bb3ff 0%,#3a6dd8 25%,#ffffff 50%,#3a6dd8 75%,#1d4ed8 100%);background-size:200% auto;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;animation:shine 5s linear infinite;">PROJECTS</span>
      </h1>
      <p class="section-sub text-muted" style="max-width:600px; margin:0 auto; font-size:var(--text-base);">
        Explore full-stack web applications, AI analysis platforms, immersive Unity games, and cross-platform mobile solutions.
      </p>

      <div class="flex-center mt-4">
        <span class="badge" style="background:var(--glass-bg); border:1px solid var(--glass-border); padding:8px 18px; border-radius:var(--radius-full); font-size:var(--text-sm); color:var(--color-text-secondary); font-weight:600;">
          <i class="bi bi-folder-check text-accent me-1"></i> <?= count($projects) ?> Total Projects Completed
        </span>
      </div>
    </div>

    <!-- Filter Bar + Search -->
    <div class="reveal delay-100 mb-6" style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:var(--space-4);">
      <?php if (!empty($allTags)): ?>
        <div class="filter-bar" style="margin-bottom:0;">
          <button class="filter-btn active" data-tag="all">All (<?= count($projects) ?>)</button>
          <?php foreach ($allTags as $tag): ?>
            <button class="filter-btn" data-tag="<?= e($tag) ?>"><?= e($tag) ?></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Search Input -->
      <div style="position:relative; width:100%; max-width:280px;">
        <i class="bi bi-search" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--color-text-muted);"></i>
        <input type="text" id="projectSearch" class="neu-input" placeholder="Search projects..."
               style="padding-left:38px; border-radius:var(--radius-full); height:42px; font-size:var(--text-sm);">
      </div>
    </div>

    <?php if (empty($projects)): ?>
      <div class="text-center py-5 reveal">
        <i class="bi bi-folder-x" style="font-size:4rem;opacity:.25;color:var(--color-accent);"></i>
        <h3 style="margin-top:var(--space-4);color:var(--color-text-secondary);">No projects found</h3>
        <p class="text-muted">Projects will appear here as soon as they are published from the admin panel.</p>
        <a href="<?= APP_URL ?>/" class="btn btn--primary mt-4"><i class="bi bi-house"></i> Back Home</a>
      </div>
    <?php else: ?>
      <div class="projects-grid" id="all-projects-grid">
        <?php foreach ($projects as $i => $project):
          $tags = json_decode($project['tags'] ?? '[]', true) ?: [];
          $num  = str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT);
        ?>
          <div class="project-card-wrapper reveal delay-<?= ($i % 6 + 1) * 100 ?>"
               data-tags="<?= htmlspecialchars(json_encode($tags), ENT_QUOTES, 'UTF-8') ?>"
               data-title="<?= htmlspecialchars(strtolower($project['title']), ENT_QUOTES, 'UTF-8') ?>">
            <div class="project-card" style="cursor:pointer;" onclick="window.location='<?= APP_URL ?>/project-detail.php?slug=<?= urlencode($project['slug']) ?>'">
              <div class="project-card__thumb">
                <?php if (!empty($project['thumbnail']) && file_exists(UPLOAD_PATH . '/projects/' . $project['thumbnail'])): ?>
                  <img src="<?= APP_URL ?>/uploads/projects/<?= e($project['thumbnail']) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
                <?php else: ?>
                  <div class="project-card__thumb--placeholder">
                    <i class="bi bi-image" style="font-size:3rem; color:rgba(91,142,240,.4);"></i>
                  </div>
                <?php endif; ?>
                <div class="project-card__num"><?= $num ?></div>
              </div>
              <div class="project-card__body">
                <div class="project-card__title"><?= e($project['title']) ?></div>
                <div class="project-card__desc"><?= e($project['short_description'] ?? '') ?></div>
                <div class="project-card__tags">
                  <?php foreach ($tags as $tag): ?>
                    <span class="tag tag--neutral"><?= e($tag) ?></span>
                  <?php endforeach; ?>
                </div>

                <div class="flex-between mt-3 pt-3" style="border-top:1px solid var(--color-border-inner);">
                  <span class="text-xs text-accent font-semibold">
                    View Case Study <i class="bi bi-arrow-right ms-1"></i>
                  </span>
                  <div class="flex gap-2" onclick="event.stopPropagation();">
                    <?php if (!empty($project['live_url'])): ?>
                      <a href="<?= e($project['live_url']) ?>" target="_blank" rel="noopener noreferrer"
                         class="btn btn--ghost btn--sm" style="padding:4px 8px; font-size:11px;" title="Live Preview">
                        <i class="bi bi-box-arrow-up-right"></i>
                      </a>
                    <?php endif; ?>
                    <?php if (!empty($project['github_url'])): ?>
                      <a href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener noreferrer"
                         class="btn btn--ghost btn--sm" style="padding:4px 8px; font-size:11px;" title="Source Code">
                        <i class="bi bi-github"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const filterBtns = document.querySelectorAll('.filter-btn');
  const projectWrappers = document.querySelectorAll('.project-card-wrapper');
  const searchInput = document.getElementById('projectSearch');

  let activeTag = 'all';
  let searchTerm = '';

  function filterProjects() {
    projectWrappers.forEach(card => {
      const tags = JSON.parse(card.getAttribute('data-tags') || '[]');
      const title = card.getAttribute('data-title') || '';

      const matchesTag = (activeTag === 'all') || tags.includes(activeTag);
      const matchesSearch = (searchTerm === '') || title.includes(searchTerm) || tags.some(t => t.toLowerCase().includes(searchTerm));

      if (matchesTag && matchesSearch) {
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  }

  filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      filterBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      activeTag = this.getAttribute('data-tag');
      filterProjects();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      searchTerm = this.value.trim().toLowerCase();
      filterProjects();
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

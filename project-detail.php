<?php
/**
 * Portfolio OS — Project Detail Page (FR-4.2)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

sendSecurityHeaders();

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header('Location: ' . APP_URL . '/projects.php');
    exit;
}

// Fetch project
$project = Database::selectOne(
    "SELECT * FROM projects WHERE slug = ? AND is_published = 1",
    [$slug]
);

if (!$project) {
    http_response_code(404);
    $pageTitle = "Project Not Found";
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container page-section text-center"><h1 class="mb-4">Project Not Found</h1><a href="' . APP_URL . '/projects.php" class="btn-neu btn-neu--primary">Back to Projects</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch gallery images
$gallery = Database::select(
    "SELECT filename, alt_text FROM project_images WHERE project_id = ? ORDER BY sort_order ASC",
    [$project['id']]
);

$techStack = json_decode($project['tech_stack'] ?? '[]', true) ?: [];
$tags      = json_decode($project['tags'] ?? '[]', true) ?: [];

$pageTitle  = e($project['title']) . " — Mudassir Portfolio";
$pageDesc   = e($project['short_description']);
$activePage = 'projects';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Project Hero ─────────────────────────────────── -->
<section class="page-section" style="padding-bottom:var(--space-8);">
  <div class="container">
    
    <div class="mb-6 reveal from-left">
      <a href="<?= APP_URL ?>/index.php#projects" class="text-muted" style="font-size:var(--text-sm); font-weight:var(--weight-semibold);">
        <i class="bi bi-arrow-left"></i> Back to Projects
      </a>
    </div>

    <div class="row g-5">
      <!-- Left: Info -->
      <div class="col-lg-5 reveal from-left delay-100">
        <h1 style="font-size:clamp(var(--text-3xl), 4vw, var(--text-5xl)); font-weight:var(--weight-extrabold); line-height:1.2; margin-bottom:var(--space-4);">
          <?= e($project['title']) ?>
        </h1>
        
        <p style="font-size:var(--text-lg); color:var(--color-text-secondary); margin-bottom:var(--space-6);">
          <?= e($project['short_description']) ?>
        </p>

        <div class="flex-col gap-4 mb-6">
          <?php if (!empty($project['role'])): ?>
            <div>
              <div class="text-xs text-muted font-bold text-uppercase mb-1">My Role</div>
              <div class="font-semibold"><?= e($project['role']) ?></div>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($techStack)): ?>
            <div>
              <div class="text-xs text-muted font-bold text-uppercase mb-2">Tech Stack</div>
              <div class="flex flex-wrap gap-2">
                <?php foreach ($techStack as $tech): ?>
                  <span class="neu-badge neu-badge--lavender"><?= e($tech) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Action Links -->
        <div class="flex flex-wrap gap-4 mt-8">
          <?php if (!empty($project['demo_url'])): ?>
            <a href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-neu btn-neu--primary">
              <i class="bi bi-box-arrow-up-right"></i> Live Demo
            </a>
          <?php endif; ?>
          <?php if (!empty($project['github_url'])): ?>
            <a href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-neu btn-neu--secondary">
              <i class="bi bi-github"></i> Source Code
            </a>
          <?php endif; ?>
          <?php if (!empty($project['store_url'])): ?>
            <a href="<?= e($project['store_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-neu btn-neu--secondary">
              <i class="bi bi-bag"></i> Get App
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right: Main Thumbnail -->
      <div class="col-lg-7 reveal from-right delay-200">
        <div class="neu-card neu-card--inset" style="padding:var(--space-2);">
          <?php if (!empty($project['thumbnail'])): ?>
            <img src="<?= APP_URL ?>/uploads/projects/<?= e($project['thumbnail']) ?>" 
                 alt="<?= e($project['title']) ?> cover"
                 style="width:100%; border-radius:var(--radius-md); aspect-ratio:16/9; object-fit:cover;"
                 data-lightbox
                 class="cursor-pointer">
          <?php else: ?>
            <div style="width:100%; aspect-ratio:16/9; background:linear-gradient(135deg,var(--color-surface-2),var(--color-surface-3)); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; font-size:64px;">🚀</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Full Description ─────────────────────────────── -->
<?php if (!empty($project['full_description'])): ?>
<section class="page-section" style="padding-top:0;" aria-label="Project Details">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 reveal">
        <div class="neu-card" style="padding:var(--space-8); font-size:var(--text-base); line-height:var(--leading-loose); color:var(--color-text-secondary);">
          <h2 style="font-size:var(--text-xl); font-weight:var(--weight-bold); color:var(--color-text-primary); margin-bottom:var(--space-4);">About this project</h2>
          
          <!-- Safely rendering HTML if admin uses a rich text editor, otherwise nl2br. 
               Assuming HTML is allowed from trusted admin, but we should run it through HTMLPurifier in a real app.
               For this v1, we use nl2br on plain text. -->
          <?= nl2br(e($project['full_description'])) ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Image Gallery ────────────────────────────────── -->
<?php if (!empty($gallery)): ?>
<section class="page-section" style="padding-top:0;" aria-label="Project Gallery">
  <div class="container">
    <h3 style="font-size:var(--text-lg); font-weight:var(--weight-bold); margin-bottom:var(--space-6);" class="reveal text-center">Gallery</h3>
    
    <div class="grid-3">
      <?php foreach ($gallery as $i => $img): ?>
        <div class="neu-card neu-card--inset reveal delay-<?= ($i % 3 + 1) * 100 ?>" style="padding:var(--space-2);">
          <img src="<?= APP_URL ?>/uploads/projects/<?= e($img['filename']) ?>"
               alt="<?= e($img['alt_text'] ?? 'Project image') ?>"
               style="width:100%; aspect-ratio:16/9; object-fit:cover; border-radius:var(--radius-md); cursor:zoom-in;"
               data-lightbox
               loading="lazy">
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

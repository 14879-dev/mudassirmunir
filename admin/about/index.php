<?php
/**
 * Portfolio OS — Admin Hero & Bio: Edit Form
 */
$pageTitle  = 'Hero & Bio';
$activeMenu = 'about';
require_once dirname(__DIR__) . '/includes/admin-header.php';

$hero  = Database::selectOne("SELECT * FROM hero_content WHERE id = 1");
$about = Database::selectOne("SELECT * FROM about_content WHERE id = 1");

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Decode taglines for textarea
$taglines = [];
if (!empty($hero['taglines'])) {
    $taglines = json_decode($hero['taglines'], true) ?: [];
}
$taglinesStr = implode("\n", $taglines);
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">Hero &amp; Bio</h1>
    <p class="text-muted">Edit your homepage intro, profile photo, bio and education.</p>
  </div>
  <a href="<?= APP_URL ?>/admin/dashboard.php" class="btn-neu btn-neu--secondary">
    <i class="bi bi-arrow-left"></i> Dashboard
  </a>
</div>

<?php if ($flashSuccess): ?>
  <div class="neu-alert neu-alert--success mb-4"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="neu-alert neu-alert--error mb-4"><?= e($flashError) ?></div>
<?php endif; ?>

<!-- ── HERO SECTION FORM ───────────────────────────── -->
<div class="neu-card mb-6">
  <h2 style="font-size:var(--text-xl);font-weight:700;margin-bottom:var(--space-5);padding-bottom:var(--space-4);border-bottom:1px solid var(--color-border);">
    <i class="bi bi-house-fill me-2" style="color:var(--color-accent);"></i>Homepage Hero
  </h2>
  <form method="POST" action="<?= APP_URL ?>/admin/about/save.php" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="section" value="hero">

    <div class="grid-2" style="gap:var(--space-5);margin-bottom:var(--space-5);">
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Full Name *</label>
        <input type="text" name="full_name" class="neu-input" required maxlength="100"
               value="<?= e($hero['full_name'] ?? 'Mudassir') ?>">
      </div>
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Title / Subtitle *</label>
        <input type="text" name="title" class="neu-input" required maxlength="255"
               value="<?= e($hero['title'] ?? '') ?>" placeholder="Software Engineering Student & Developer">
      </div>
    </div>

    <div style="margin-bottom:var(--space-5);">
      <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Typewriter Taglines (one per line)</label>
      <textarea name="taglines" class="neu-input neu-textarea" style="min-height:100px;" placeholder="Game Developer&#10;Web Developer&#10;Co-founder @ Hexspire"><?= e($taglinesStr) ?></textarea>
      <small class="text-muted" style="font-size:var(--text-xs);">Each line becomes a word in the animated typewriter effect.</small>
    </div>

    <div class="grid-2" style="gap:var(--space-5);margin-bottom:var(--space-5);">
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">CTA Button 1 Text</label>
        <input type="text" name="cta_primary" class="neu-input" maxlength="100"
               value="<?= e($hero['cta_primary'] ?? 'View Projects') ?>">
      </div>
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">CTA Button 2 Text</label>
        <input type="text" name="cta_secondary" class="neu-input" maxlength="100"
               value="<?= e($hero['cta_secondary'] ?? 'Contact Me') ?>">
      </div>
    </div>

    <div style="margin-bottom:var(--space-5);">
      <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Profile Photo</label>
      <?php if (!empty($hero['profile_photo'])): ?>
        <div style="margin-bottom:8px;display:flex;align-items:center;gap:12px;">
          <img src="<?= APP_URL ?>/uploads/hero/<?= e($hero['profile_photo']) ?>" alt="Profile"
               style="height:80px;width:80px;object-fit:cover;border-radius:50%;border:2px solid var(--color-border-accent);">
          <label style="font-size:var(--text-sm);"><input type="checkbox" name="remove_photo"> Remove current photo</label>
        </div>
      <?php endif; ?>
      <input type="file" name="profile_photo" class="neu-input" accept="image/jpeg,image/png,image/webp">
      <small class="text-muted" style="font-size:var(--text-xs);">Max 3MB. JPG, PNG or WebP. Recommended: 400×400px square.</small>
    </div>

    <div style="display:flex;justify-content:flex-end;">
      <button type="submit" class="btn-neu btn-neu--primary">
        <i class="bi bi-check-lg"></i> Save Hero Section
      </button>
    </div>
  </form>
</div>

<!-- ── ABOUT / BIO FORM ────────────────────────────── -->
<div class="neu-card mb-6">
  <h2 style="font-size:var(--text-xl);font-weight:700;margin-bottom:var(--space-5);padding-bottom:var(--space-4);border-bottom:1px solid var(--color-border);">
    <i class="bi bi-person-fill me-2" style="color:var(--color-accent);"></i>About Me &amp; Bio
  </h2>
  <form method="POST" action="<?= APP_URL ?>/admin/about/save.php" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="section" value="about">

    <div style="margin-bottom:var(--space-5);">
      <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Bio / Short Description</label>
      <textarea name="bio" class="neu-input neu-textarea" style="min-height:120px;"
                placeholder="Write a compelling bio about yourself..."><?= e($about['bio'] ?? '') ?></textarea>
    </div>

    <div class="grid-2" style="gap:var(--space-5);margin-bottom:var(--space-5);">
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Degree</label>
        <input type="text" name="education_degree" class="neu-input" maxlength="255"
               value="<?= e($about['education_degree'] ?? '') ?>" placeholder="BS Software Engineering">
      </div>
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Institution</label>
        <input type="text" name="education_institution" class="neu-input" maxlength="255"
               value="<?= e($about['education_institution'] ?? '') ?>" placeholder="CUSIT Peshawar">
      </div>
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Years</label>
        <input type="text" name="education_years" class="neu-input" maxlength="50"
               value="<?= e($about['education_years'] ?? '') ?>" placeholder="2022 – Present">
      </div>
    </div>

    <div style="margin-bottom:var(--space-5);">
      <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">
        Timeline Events
        <span style="font-weight:400;text-transform:none;margin-left:8px;" class="text-muted">— one event per box</span>
      </label>
      <?php
        $timelineItems = json_decode($about['timeline_items'] ?? '[]', true) ?: [];
        // Always show at least 4 rows
        while (count($timelineItems) < 4) $timelineItems[] = ['year'=>'','event'=>'','description'=>''];
      ?>
      <?php foreach ($timelineItems as $ti => $event): ?>
      <div class="grid-2" style="gap:var(--space-3);margin-bottom:var(--space-3);align-items:start;">
        <div>
          <input type="text" name="timeline_year[]" class="neu-input" maxlength="10"
                 value="<?= e($event['year'] ?? '') ?>" placeholder="Year (e.g. 2022)">
        </div>
        <div>
          <input type="text" name="timeline_event[]" class="neu-input" maxlength="150"
                 value="<?= e($event['event'] ?? '') ?>" placeholder="Event title">
          <input type="text" name="timeline_desc[]" class="neu-input mt-2" maxlength="200"
                 value="<?= e($event['description'] ?? '') ?>" placeholder="Short description (optional)">
        </div>
      </div>
      <?php endforeach; ?>
      <small class="text-muted" style="font-size:var(--text-xs);">Leave empty rows blank — they will be ignored on save.</small>
    </div>

    <div style="display:flex;justify-content:flex-end;">
      <button type="submit" class="btn-neu btn-neu--primary">
        <i class="bi bi-check-lg"></i> Save Bio &amp; Education
      </button>
    </div>
  </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>

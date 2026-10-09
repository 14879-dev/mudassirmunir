<?php
/**
 * Portfolio OS — Public Awards & Certificates Listing Page
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

startSecureSession();
sendSecurityHeaders();

Database::ensureCertificatesTable();

$certificates = Database::select("SELECT * FROM certificates WHERE is_published=1 ORDER BY sort_order ASC, created_at DESC");

// Counts by category
$totalCerts  = count($certificates);
$awardsCount = count(array_filter($certificates, fn($c) => ($c['category'] ?? '') === 'award'));
$certsCount  = count(array_filter($certificates, fn($c) => ($c['category'] ?? '') === 'certificate'));
$badgesCount = count(array_filter($certificates, fn($c) => ($c['category'] ?? '') === 'badge'));

$pageTitle  = "Awards & Certificates — Mudassir Munir";
$pageDesc   = "Verified credentials, awards, honors and technical certifications earned by Mudassir Munir.";
$activePage = 'certificates';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Certificate Page Enhancements */
.cert-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: var(--space-6);
  margin-top: var(--space-8);
}
.cert-card {
  background: var(--glass-bg);
  backdrop-filter: var(--blur-md);
  -webkit-backdrop-filter: var(--blur-md);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--glass-shadow);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all var(--dur-normal) var(--ease);
  position: relative;
}
.cert-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--glass-shadow-hover);
  border-color: var(--color-border-accent);
}
.cert-card__media {
  height: 200px;
  background: linear-gradient(135deg, rgba(91,142,240,0.08) 0%, rgba(255,255,255,0.4) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  border-bottom: 1px solid var(--color-border-inner);
}
.cert-card__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform var(--dur-slow) var(--ease);
}
.cert-card:hover .cert-card__media img {
  transform: scale(1.05);
}
.cert-card__badge-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-accent-soft), #fff);
  border: 2px solid var(--color-border-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: var(--color-accent);
  box-shadow: 0 8px 24px rgba(91,142,240,0.15);
}
.cert-card__type-pill {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 4px 12px;
  border-radius: var(--radius-full);
  font-size: var(--text-xs);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  backdrop-filter: var(--blur-sm);
}
.cert-card__type-pill--certificate {
  background: rgba(91,142,240,0.15);
  color: var(--color-accent);
  border: 1px solid var(--color-border-accent);
}
.cert-card__type-pill--award {
  background: rgba(245,158,11,0.18);
  color: #d97706;
  border: 1px solid rgba(245,158,11,0.4);
}
.cert-card__type-pill--badge {
  background: rgba(16,185,129,0.18);
  color: #059669;
  border: 1px solid rgba(16,185,129,0.4);
}
.cert-card__body {
  padding: var(--space-5);
  display: flex;
  flex-direction: column;
  flex: 1;
}
.cert-card__title {
  font-size: var(--text-lg);
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: var(--space-2);
  line-height: 1.3;
}
.cert-card__issuer {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  font-weight: 600;
  margin-bottom: var(--space-3);
}
.cert-card__desc {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  line-height: 1.6;
  margin-bottom: var(--space-4);
  flex: 1;
}
.cert-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: var(--space-3);
  border-top: 1px solid var(--color-border-inner);
  font-size: var(--text-xs);
}
.cert-stat-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border-radius: var(--radius-full);
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  font-size: var(--text-sm);
  font-weight: 600;
}
</style>

<!-- ─────────────────────────────────────────────────────── -->
<!--  AWARDS & CERTIFICATES LISTING PAGE                    -->
<!-- ─────────────────────────────────────────────────────── -->
<section class="page-section" style="min-height:90vh; padding-top:100px;" aria-labelledby="certs-page-heading">
  <div class="container">

    <div class="section-header reveal text-center mb-6">
      <div class="overline" style="color:var(--color-accent); font-weight:700; letter-spacing:.15em; font-size:var(--text-xs); text-transform:uppercase; margin-bottom:8px;">
        <i class="bi bi-patch-check-fill me-1"></i> VERIFIED CREDENTIALS &amp; RECOGNITIONS
      </div>
      <h1 class="section-title" id="certs-page-heading" style="font-size:clamp(2.4rem, 6vw, 4.2rem); font-weight:800;">
        AWARDS &amp; <span style="background:linear-gradient(135deg,#7bb3ff 0%,#3a6dd8 25%,#ffffff 50%,#3a6dd8 75%,#1d4ed8 100%);background-size:200% auto;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;animation:shine 5s linear infinite;">CERTIFICATES</span>
      </h1>
      <p class="section-sub text-muted" style="max-width:600px; margin:0 auto; font-size:var(--text-base);">
        A collection of official technical certifications, industry recognitions, and achievement badges validating real-world expertise.
      </p>

      <!-- Stat Badges Row -->
      <div class="flex-center flex-wrap gap-3 mt-4">
        <div class="cert-stat-badge">
          <i class="bi bi-award-fill text-accent"></i>
          <span><?= $totalCerts ?> Verified Total</span>
        </div>
        <div class="cert-stat-badge">
          <i class="bi bi-file-earmark-check text-success"></i>
          <span><?= $certsCount ?> Certificates</span>
        </div>
        <div class="cert-stat-badge">
          <i class="bi bi-trophy-fill" style="color:#d97706;"></i>
          <span><?= $awardsCount ?> Honors &amp; Awards</span>
        </div>
        <div class="cert-stat-badge">
          <i class="bi bi-patch-check" style="color:#059669;"></i>
          <span><?= $badgesCount ?> Skill Badges</span>
        </div>
      </div>
    </div>

    <!-- Filter Buttons -->
    <div class="filter-bar reveal delay-100 flex-center mb-6" style="gap:var(--space-2); flex-wrap:wrap;">
      <button class="filter-btn active" data-filter="all">All (<?= $totalCerts ?>)</button>
      <button class="filter-btn" data-filter="certificate">Certificates (<?= $certsCount ?>)</button>
      <button class="filter-btn" data-filter="award">Awards (<?= $awardsCount ?>)</button>
      <button class="filter-btn" data-filter="badge">Badges (<?= $badgesCount ?>)</button>
    </div>

    <?php if (empty($certificates)): ?>
      <div class="text-center py-5 reveal">
        <i class="bi bi-award" style="font-size:4rem;opacity:.25;color:var(--color-accent);"></i>
        <h3 style="margin-top:var(--space-4);color:var(--color-text-secondary);">No awards or certificates yet</h3>
        <p class="text-muted">Certificates added via admin panel will be proudly displayed here.</p>
        <a href="<?= APP_URL ?>/" class="btn btn--primary mt-4"><i class="bi bi-house"></i> Back Home</a>
      </div>
    <?php else: ?>
      <div class="cert-grid reveal delay-200" id="cert-grid">
        <?php foreach ($certificates as $c):
          $cat = htmlspecialchars($c['category'] ?? 'certificate');
          $hasImg = !empty($c['image']) && file_exists(UPLOAD_PATH . '/certificates/' . $c['image']);
        ?>
          <div class="cert-card cert-item" data-category="<?= $cat ?>">
            <div class="cert-card__media">
              <?php if ($hasImg): ?>
                <img src="<?= APP_URL ?>/uploads/certificates/<?= e($c['image']) ?>"
                     alt="<?= e($c['title']) ?>"
                     loading="lazy"
                     style="cursor:pointer;"
                     onclick="openCertModal('<?= APP_URL ?>/uploads/certificates/<?= e($c['image']) ?>', '<?= e(addslashes($c['title'])) ?>')">
              <?php else: ?>
                <div class="cert-card__badge-icon">
                  <i class="bi <?= $cat === 'award' ? 'bi-trophy' : ($cat === 'badge' ? 'bi-patch-check' : 'bi-award') ?>"></i>
                </div>
              <?php endif; ?>
              
              <span class="cert-card__type-pill cert-card__type-pill--<?= $cat ?>">
                <?= ucfirst($cat) ?>
              </span>
            </div>

            <div class="cert-card__body">
              <h2 class="cert-card__title"><?= e($c['title']) ?></h2>
              
              <div class="cert-card__issuer">
                <i class="bi bi-building text-accent"></i>
                <span><?= e($c['issuer']) ?></span>
              </div>

              <?php if (!empty($c['description'])): ?>
                <p class="cert-card__desc"><?= e($c['description']) ?></p>
              <?php else: ?>
                <p class="cert-card__desc" style="opacity:.6;">Officially verified and awarded by <?= e($c['issuer']) ?>.</p>
              <?php endif; ?>

              <div class="cert-card__footer">
                <span class="text-muted">
                  <i class="bi bi-calendar-event me-1"></i><?= e($c['issue_date'] ?: 'Verified') ?>
                  <?php if (!empty($c['credential_id'])): ?>
                    <span class="ms-2">· ID: <code><?= e($c['credential_id']) ?></code></span>
                  <?php endif; ?>
                </span>

                <div class="flex gap-2">
                  <?php if ($hasImg): ?>
                    <button type="button" class="btn btn--sm btn--ghost" style="padding:4px 8px; font-size:11px;"
                            onclick="openCertModal('<?= APP_URL ?>/uploads/certificates/<?= e($c['image']) ?>', '<?= e(addslashes($c['title'])) ?>')"
                            title="Preview Certificate">
                      <i class="bi bi-zoom-in"></i> View
                    </button>
                  <?php endif; ?>

                  <?php if (!empty($c['credential_url'])): ?>
                    <a href="<?= e($c['credential_url']) ?>" target="_blank" rel="noopener noreferrer"
                       class="btn btn--sm btn--primary" style="padding:4px 10px; font-size:11px;">
                      Verify <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- Certificate Preview Lightbox Modal -->
<div id="certModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); backdrop-filter:blur(8px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div style="position:relative; max-width:90vw; max-height:90vh; display:flex; flex-direction:column; align-items:center;">
    <button type="button" onclick="closeCertModal()" style="position:absolute; top:-40px; right:0; background:none; border:none; color:#fff; font-size:2rem; cursor:pointer;" aria-label="Close">
      <i class="bi bi-x-lg"></i>
    </button>
    <img id="certModalImg" src="" alt="Certificate Preview" style="max-width:100%; max-height:80vh; object-fit:contain; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.5);">
    <div id="certModalTitle" style="color:#fff; font-weight:700; margin-top:14px; font-size:1.1rem; text-align:center;"></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Category Filtering
  const filterBtns = document.querySelectorAll('.filter-btn');
  const certItems  = document.querySelectorAll('.cert-item');

  filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      filterBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      const filter = this.getAttribute('data-filter');
      certItems.forEach(item => {
        if (filter === 'all' || item.getAttribute('data-category') === filter) {
          item.style.display = 'flex';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
});

function openCertModal(src, title) {
  const modal = document.getElementById('certModal');
  const img   = document.getElementById('certModalImg');
  const cap   = document.getElementById('certModalTitle');
  img.src = src;
  cap.textContent = title;
  modal.style.display = 'flex';
}

function closeCertModal() {
  document.getElementById('certModal').style.display = 'none';
}

document.getElementById('certModal').addEventListener('click', function(e) {
  if (e.target === this) closeCertModal();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

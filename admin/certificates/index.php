<?php
/**
 * Portfolio OS — Admin Certificates & Awards: List
 */
$pageTitle  = 'Awards & Certificates';
$activeMenu = 'certificates';
require_once dirname(__DIR__) . '/includes/admin-header.php';

Database::ensureCertificatesTable();

$certificates = Database::select("SELECT * FROM certificates ORDER BY sort_order ASC, created_at DESC");

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">Awards &amp; Certificates</h1>
    <p class="text-muted">Manage your certificates, awards, honors and badges. <?= count($certificates) ?> item(s) total.</p>
  </div>
  <a href="<?= APP_URL ?>/admin/certificates/form.php" class="btn-neu btn-neu--primary">
    <i class="bi bi-plus-lg"></i> Add Award / Certificate
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
          <th>Title &amp; Issuer</th>
          <th>Category</th>
          <th>Date / Year</th>
          <th>Status</th>
          <th class="pe-4 text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($certificates)): ?>
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">
              <i class="bi bi-award mb-2" style="font-size:2.5rem; display:block; color:var(--color-accent); opacity:.6;"></i>
              No certificates or awards added yet.
              <br>
              <a href="<?= APP_URL ?>/admin/certificates/form.php" class="btn-neu btn-neu--primary mt-3" style="display:inline-flex;">
                <i class="bi bi-plus-lg me-1"></i> Add your first certificate
              </a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($certificates as $i => $c):
            $isPublished = (bool)($c['is_published'] ?? 1);
            $cat = htmlspecialchars($c['category'] ?? 'certificate');
            $badgeColor = match($cat) {
              'award' => 'warning',
              'badge' => 'info',
              default => 'primary'
            };
          ?>
          <tr style="border-bottom:1px solid var(--color-border);">
            <td class="ps-4" style="vertical-align:middle; color:var(--color-text-muted);">
              <?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?>
            </td>
            <td style="vertical-align:middle;">
              <div class="flex items-center gap-3">
                <?php if (!empty($c['image']) && file_exists(UPLOAD_PATH . '/certificates/' . $c['image'])): ?>
                  <img src="<?= APP_URL ?>/uploads/certificates/<?= e($c['image']) ?>" alt="Thumbnail"
                       style="width:50px; height:50px; object-fit:cover; border-radius:var(--radius-sm); border:1px solid var(--color-border);">
                <?php else: ?>
                  <div style="width:50px; height:50px; border-radius:var(--radius-sm); background:var(--color-accent-soft); display:flex; align-items:center; justify-content:center; color:var(--color-accent); font-size:1.5rem;">
                    <i class="bi bi-award"></i>
                  </div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:var(--weight-bold); color:var(--color-text-primary); font-size:var(--text-base);">
                    <?= e($c['title']) ?>
                  </div>
                  <div class="text-xs text-muted">
                    <?= e($c['issuer']) ?> <?= !empty($c['credential_id']) ? '· ID: ' . e($c['credential_id']) : '' ?>
                  </div>
                </div>
              </div>
            </td>
            <td style="vertical-align:middle;">
              <span class="badge bg-<?= $badgeColor ?> text-capitalize"><?= e($cat) ?></span>
            </td>
            <td style="vertical-align:middle; color:var(--color-text-secondary); font-size:var(--text-sm);">
              <?= e($c['issue_date'] ?: '—') ?>
            </td>
            <td style="vertical-align:middle;">
              <span class="badge bg-<?= $isPublished ? 'success' : 'secondary' ?>">
                <?= $isPublished ? 'Published' : 'Draft' ?>
              </span>
            </td>
            <td class="pe-4 text-end" style="vertical-align:middle;">
              <div class="flex gap-2 justify-end" style="justify-content:flex-end;">
                <?php if (!empty($c['credential_url'])): ?>
                  <a href="<?= e($c['credential_url']) ?>" target="_blank" rel="noopener noreferrer"
                     class="btn-neu btn-neu--ghost btn-neu--sm" title="View Credential URL">
                    <i class="bi bi-box-arrow-up-right"></i>
                  </a>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/admin/certificates/form.php?id=<?= (int)$c['id'] ?>"
                   class="btn-neu btn-neu--secondary btn-neu--sm" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="<?= APP_URL ?>/admin/certificates/delete.php" style="display:inline;"
                      onsubmit="return confirm('Are you sure you want to delete this certificate?');">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <button type="submit" class="btn-neu btn-neu--danger btn-neu--sm" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>

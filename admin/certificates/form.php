<?php
/**
 * Portfolio OS — Admin Certificate: Create / Edit Form
 */
require_once dirname(__DIR__) . '/../config/config.php';
require_once dirname(__DIR__) . '/../includes/db.php';
require_once dirname(__DIR__) . '/../includes/security.php';
require_once dirname(__DIR__) . '/../includes/auth.php';

Database::ensureCertificatesTable();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$certificate = null;
if ($id) {
    $certificate = Database::selectOne("SELECT * FROM certificates WHERE id = ?", [$id]);
    if (!$certificate) {
        $_SESSION['flash_error'] = 'Certificate not found.';
        header('Location: ' . APP_URL . '/admin/certificates/');
        exit;
    }
}

$pageTitle  = $id ? 'Edit Certificate' : 'New Award or Certificate';
$activeMenu = 'certificates';
require_once dirname(__DIR__) . '/includes/admin-header.php';
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">
      <?= $id ? 'Edit Award / Certificate' : 'Add New Award / Certificate' ?>
    </h1>
    <p class="text-muted">Fill in certificate or badge details to showcase on your portfolio.</p>
  </div>
  <a href="<?= APP_URL ?>/admin/certificates/" class="btn-neu btn-neu--secondary">
    <i class="bi bi-arrow-left"></i> Back to Certificates
  </a>
</div>

<div class="neu-card">
  <form method="POST" action="<?= APP_URL ?>/admin/certificates/save.php" enctype="multipart/form-data">
    <?= csrfField() ?>
    <?php if ($id): ?>
      <input type="hidden" name="id" value="<?= $id ?>">
    <?php endif; ?>

    <div class="row g-4 mb-4">
      <div class="col-md-8">
        <label class="form-label text-xs uppercase font-bold text-muted">Title / Award Name *</label>
        <input type="text" name="title" class="neu-input" required maxlength="255"
               value="<?= e($certificate['title'] ?? '') ?>"
               placeholder="e.g. AWS Certified Solutions Architect, Winner - Hackathon 2024">
      </div>

      <div class="col-md-4">
        <label class="form-label text-xs uppercase font-bold text-muted">Category *</label>
        <select name="category" class="neu-input" required>
          <option value="certificate" <?= ($certificate['category'] ?? '') === 'certificate' ? 'selected' : '' ?>>Certificate</option>
          <option value="award" <?= ($certificate['category'] ?? '') === 'award' ? 'selected' : '' ?>>Award / Honor</option>
          <option value="badge" <?= ($certificate['category'] ?? '') === 'badge' ? 'selected' : '' ?>>Badge / Credential</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label text-xs uppercase font-bold text-muted">Issuing Organization / Authority *</label>
        <input type="text" name="issuer" class="neu-input" required maxlength="255"
               value="<?= e($certificate['issuer'] ?? '') ?>"
               placeholder="e.g. Google, Coursera, Meta, City University Peshawar">
      </div>

      <div class="col-md-6">
        <label class="form-label text-xs uppercase font-bold text-muted">Issue Date / Year</label>
        <input type="text" name="issue_date" class="neu-input" maxlength="50"
               value="<?= e($certificate['issue_date'] ?? '') ?>"
               placeholder="e.g. 2024 or Oct 2024 or Present">
      </div>

      <div class="col-md-6">
        <label class="form-label text-xs uppercase font-bold text-muted">Credential ID / Number (Optional)</label>
        <input type="text" name="credential_id" class="neu-input" maxlength="100"
               value="<?= e($certificate['credential_id'] ?? '') ?>"
               placeholder="e.g. CERT-98234-XYZ">
      </div>

      <div class="col-md-6">
        <label class="form-label text-xs uppercase font-bold text-muted">Verification / Credential URL (Optional)</label>
        <input type="url" name="credential_url" class="neu-input" maxlength="500"
               value="<?= e($certificate['credential_url'] ?? '') ?>"
               placeholder="https://coursera.org/verify/... or https://badgr.com/...">
      </div>

      <div class="col-12">
        <label class="form-label text-xs uppercase font-bold text-muted">Description / Key Achievements</label>
        <textarea name="description" class="neu-input" rows="3"
                  placeholder="Brief details about what was accomplished or learned..."><?= e($certificate['description'] ?? '') ?></textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label text-xs uppercase font-bold text-muted">Certificate / Badge Image</label>
        <?php if (!empty($certificate['image']) && file_exists(UPLOAD_PATH . '/certificates/' . $certificate['image'])): ?>
          <div class="mb-3 d-flex align-items-center gap-3">
            <img src="<?= APP_URL ?>/uploads/certificates/<?= e($certificate['image']) ?>" alt="Certificate Image"
                 style="height:80px; width:120px; object-fit:cover; border-radius:var(--radius-sm); border:1px solid var(--color-border);">
            <label class="text-sm">
              <input type="checkbox" name="remove_image" value="1"> Remove current image
            </label>
          </div>
        <?php endif; ?>
        <input type="file" name="image" class="neu-input" accept="image/jpeg,image/png,image/webp,image/svg+xml">
        <small class="text-muted d-block mt-1">Recommended: Clean PNG badge or certificate JPG/WebP (Max 3MB).</small>
      </div>

      <div class="col-md-3">
        <label class="form-label text-xs uppercase font-bold text-muted">Display Order</label>
        <input type="number" name="sort_order" class="neu-input" value="<?= (int)($certificate['sort_order'] ?? 0) ?>">
        <small class="text-muted">Lower numbers appear first.</small>
      </div>

      <div class="col-md-3 d-flex align-items-center pt-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1"
                 <?= (!isset($certificate) || !empty($certificate['is_published'])) ? 'checked' : '' ?>>
          <label class="form-check-label font-bold ms-2" for="is_published">Publish Publicly</label>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end gap-3 pt-3 border-top">
      <a href="<?= APP_URL ?>/admin/certificates/" class="btn-neu btn-neu--ghost">Cancel</a>
      <button type="submit" class="btn-neu btn-neu--primary">
        <i class="bi bi-check-lg"></i> <?= $id ? 'Update Certificate' : 'Save Certificate' ?>
      </button>
    </div>
  </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>

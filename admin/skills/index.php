<?php
/**
 * Portfolio OS — Admin Skills: List + Inline Add/Delete
 */
$pageTitle  = 'Skills & Languages';
$activeMenu = 'skills';
require_once dirname(__DIR__) . '/includes/admin-header.php';

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$skills    = Database::select("SELECT * FROM skills ORDER BY category ASC, sort_order ASC");
$languages = Database::select("SELECT * FROM languages ORDER BY lang_type ASC, sort_order ASC");
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">Skills &amp; Languages</h1>
    <p class="text-muted">Manage your technical skills and spoken/programming languages.</p>
  </div>
  <div class="flex gap-2">
    <button class="btn-neu btn-neu--secondary" onclick="togglePanel('lang-panel')">
      <i class="bi bi-plus-lg"></i> Add Language
    </button>
    <button class="btn-neu btn-neu--primary" onclick="togglePanel('skill-panel')">
      <i class="bi bi-plus-lg"></i> Add Skill
    </button>
  </div>
</div>

<?php if ($flashSuccess): ?>
  <div class="neu-alert neu-alert--success mb-4"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="neu-alert neu-alert--error mb-4"><?= e($flashError) ?></div>
<?php endif; ?>

<!-- Add Skill Panel -->
<div id="skill-panel" class="neu-card mb-6" style="display:none;">
  <h3 style="font-size:var(--text-lg);font-weight:700;margin-bottom:var(--space-4);">Add New Skill</h3>
  <form method="POST" action="<?= APP_URL ?>/admin/skills/save.php">
    <?= csrfField() ?>
    <input type="hidden" name="type" value="skill">
    <div class="grid-2" style="gap:var(--space-4);margin-bottom:var(--space-4);">
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Name *</label>
        <input type="text" name="name" class="neu-input" required placeholder="e.g. React / TypeScript" maxlength="100">
      </div>
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Category *</label>
        <input type="text" name="category" class="neu-input" required placeholder="Web Frontend" maxlength="100">
      </div>
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Proficiency (0–100)</label>
        <input type="number" name="proficiency" class="neu-input" min="0" max="100" value="75">
      </div>
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Icon Class</label>
        <input type="text" name="icon" class="neu-input" placeholder="devicon-react-original" maxlength="100">
      </div>
    </div>
    <div style="display:flex;gap:var(--space-3);justify-content:flex-end;">
      <button type="button" class="btn-neu btn-neu--ghost" onclick="togglePanel('skill-panel')">Cancel</button>
      <button type="submit" class="btn-neu btn-neu--primary"><i class="bi bi-check-lg"></i> Save Skill</button>
    </div>
  </form>
</div>

<!-- Add Language Panel -->
<div id="lang-panel" class="neu-card mb-6" style="display:none;">
  <h3 style="font-size:var(--text-lg);font-weight:700;margin-bottom:var(--space-4);">Add New Language</h3>
  <form method="POST" action="<?= APP_URL ?>/admin/skills/save.php">
    <?= csrfField() ?>
    <input type="hidden" name="type" value="language">
    <div class="grid-2" style="gap:var(--space-4);margin-bottom:var(--space-4);">
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Name *</label>
        <input type="text" name="name" class="neu-input" required placeholder="e.g. English" maxlength="100">
      </div>
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Type</label>
        <select name="lang_type" class="neu-input">
          <option value="spoken">Spoken</option>
          <option value="programming">Programming</option>
        </select>
      </div>
      <div>
        <label style="font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);text-transform:uppercase;display:block;margin-bottom:6px;">Level</label>
        <select name="proficiency_level" class="neu-input">
          <option>Native</option>
          <option>Advanced</option>
          <option>Intermediate</option>
          <option>Elementary</option>
        </select>
      </div>
    </div>
    <div style="display:flex;gap:var(--space-3);justify-content:flex-end;">
      <button type="button" class="btn-neu btn-neu--ghost" onclick="togglePanel('lang-panel')">Cancel</button>
      <button type="submit" class="btn-neu btn-neu--primary"><i class="bi bi-check-lg"></i> Save Language</button>
    </div>
  </form>
</div>

<div class="grid-2">
  <!-- Skills Table -->
  <div class="neu-card" style="padding:0;overflow:hidden;">
    <div style="padding:var(--space-4);border-bottom:1px solid var(--color-border);background:var(--color-surface-2);">
      <h3 style="font-size:var(--text-lg);font-weight:700;margin:0;">Tech Skills</h3>
    </div>
    <div class="table-responsive">
      <table class="table table-hover table-borderless mb-0" style="font-size:var(--text-sm);">
        <tbody>
          <?php if (empty($skills)): ?>
            <tr><td class="text-center py-4 text-muted">No skills added yet.</td></tr>
          <?php else: ?>
            <?php foreach ($skills as $sk): ?>
            <tr style="border-bottom:1px solid var(--color-border);">
              <td class="ps-4" style="vertical-align:middle;">
                <div style="font-weight:600;color:var(--color-text-primary);"><?= e($sk['name']) ?></div>
                <div style="font-size:var(--text-xs);color:var(--color-text-muted);"><?= e($sk['category']) ?></div>
              </td>
              <td style="vertical-align:middle;">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="flex:1;height:5px;background:rgba(91,142,240,.15);border-radius:3px;min-width:60px;">
                    <div style="width:<?= (int)$sk['proficiency'] ?>%;height:100%;background:linear-gradient(90deg,var(--color-accent),#7bb3ff);border-radius:3px;"></div>
                  </div>
                  <span style="font-size:var(--text-xs);font-weight:600;color:var(--color-accent);white-space:nowrap;"><?= $sk['proficiency'] ?>%</span>
                </div>
              </td>
              <td class="pe-4 text-end" style="vertical-align:middle;">
                <form method="POST" action="<?= APP_URL ?>/admin/skills/delete.php" style="display:inline"
                      onsubmit="return confirm('Delete <?= e(addslashes($sk['name'])) ?>?');">
                  <?= csrfField() ?>
                  <input type="hidden" name="type" value="skill">
                  <input type="hidden" name="id" value="<?= $sk['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Languages Table -->
  <div class="neu-card" style="padding:0;overflow:hidden;align-self:start;">
    <div style="padding:var(--space-4);border-bottom:1px solid var(--color-border);background:var(--color-surface-2);">
      <h3 style="font-size:var(--text-lg);font-weight:700;margin:0;">Languages</h3>
    </div>
    <div class="table-responsive">
      <table class="table table-hover table-borderless mb-0" style="font-size:var(--text-sm);">
        <tbody>
          <?php if (empty($languages)): ?>
            <tr><td class="text-center py-4 text-muted">No languages added yet.</td></tr>
          <?php else: ?>
            <?php foreach ($languages as $lg): ?>
            <tr style="border-bottom:1px solid var(--color-border);">
              <td class="ps-4" style="vertical-align:middle;">
                <div style="font-weight:600;color:var(--color-text-primary);"><?= e($lg['name']) ?></div>
                <div style="font-size:var(--text-xs);color:var(--color-text-muted);"><?= ucfirst(e($lg['lang_type'])) ?></div>
              </td>
              <td style="vertical-align:middle;">
                <span class="badge bg-secondary"><?= e($lg['proficiency_level']) ?></span>
              </td>
              <td class="pe-4 text-end" style="vertical-align:middle;">
                <form method="POST" action="<?= APP_URL ?>/admin/skills/delete.php" style="display:inline"
                      onsubmit="return confirm('Delete <?= e(addslashes($lg['name'])) ?>?');">
                  <?= csrfField() ?>
                  <input type="hidden" name="type" value="language">
                  <input type="hidden" name="id" value="<?= $lg['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function togglePanel(id) {
  const el = document.getElementById(id);
  if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>

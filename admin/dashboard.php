<?php
/**
 * Portfolio OS — Admin Dashboard
 */
$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
require_once __DIR__ . '/includes/admin-header.php';

Database::ensureCertificatesTable();

// Fetch stats
$stats = [
    'projects'     => Database::selectOne("SELECT COUNT(*) as c FROM projects")['c'] ?? 0,
    'certificates' => Database::selectOne("SELECT COUNT(*) as c FROM certificates")['c'] ?? 0,
    'skills'       => Database::selectOne("SELECT COUNT(*) as c FROM skills")['c'] ?? 0,
    'unread'       => Database::selectOne("SELECT COUNT(*) as c FROM messages WHERE is_read=0")['c'] ?? 0,
];

// Fetch 3 recent projects
$recentProjects = Database::select(
    "SELECT id, title, slug, thumbnail, is_published, created_at FROM projects ORDER BY sort_order ASC, created_at DESC LIMIT 3"
);

// Fetch recent messages
$recentMsgs = Database::select(
    "SELECT id, name, subject, created_at, is_read FROM messages ORDER BY created_at DESC LIMIT 5"
);

// Fetch recent audit logs
$audits = Database::select(
    "SELECT a.action, a.entity_type, a.created_at, u.email 
     FROM audit_log a 
     LEFT JOIN users u ON u.id = a.user_id 
     ORDER BY a.created_at DESC LIMIT 5"
);
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">Dashboard</h1>
    <p class="text-muted">Welcome back, <?= e($adminUser['email']) ?></p>
  </div>
  <a href="<?= APP_URL ?>/" target="_blank" class="btn-neu btn-neu--secondary">
    <i class="bi bi-box-arrow-up-right"></i> View Public Site
  </a>
</div>

<div class="grid-4 mb-8">
  <div class="neu-card flex align-items-center gap-4">
    <div style="font-size:32px; color:var(--color-accent);"><i class="bi bi-envelope"></i></div>
    <div>
      <div class="text-xs text-muted font-bold text-uppercase">Unread Messages</div>
      <div style="font-size:var(--text-3xl); font-weight:var(--weight-bold);"><?= $stats['unread'] ?></div>
    </div>
  </div>
  
  <div class="neu-card flex align-items-center gap-4">
    <div style="font-size:32px; color:var(--color-peach, #f59e0b);"><i class="bi bi-briefcase"></i></div>
    <div>
      <div class="text-xs text-muted font-bold text-uppercase">Total Projects</div>
      <div style="font-size:var(--text-3xl); font-weight:var(--weight-bold);"><?= $stats['projects'] ?></div>
    </div>
  </div>

  <div class="neu-card flex align-items-center gap-4">
    <div style="font-size:32px; color:var(--color-accent-dark, #8b5cf6);"><i class="bi bi-award"></i></div>
    <div>
      <div class="text-xs text-muted font-bold text-uppercase">Awards &amp; Certs</div>
      <div style="font-size:var(--text-3xl); font-weight:var(--weight-bold);"><?= $stats['certificates'] ?></div>
    </div>
  </div>

  <div class="neu-card flex align-items-center gap-4">
    <div style="font-size:32px; color:var(--color-success);"><i class="bi bi-cpu"></i></div>
    <div>
      <div class="text-xs text-muted font-bold text-uppercase">Skills Listed</div>
      <div style="font-size:var(--text-3xl); font-weight:var(--weight-bold);"><?= $stats['skills'] ?></div>
    </div>
  </div>
</div>

<!-- Projects (Top 3) -->
<div class="neu-card mb-6">
  <div class="flex-between mb-4">
    <div>
      <h3 style="font-size:var(--text-lg); font-weight:var(--weight-bold); margin-bottom:2px;">
        <i class="bi bi-briefcase me-2" style="color:var(--color-accent);"></i>Projects
      </h3>
      <p class="text-muted text-xs mb-0">Showing 3 projects on dashboard</p>
    </div>
    <div class="flex gap-2">
      <a href="<?= APP_URL ?>/admin/projects/form.php" class="btn-neu btn-neu--primary btn-neu--sm">
        <i class="bi bi-plus-lg"></i> Add New
      </a>
      <a href="<?= APP_URL ?>/admin/projects/" class="btn-neu btn-neu--secondary btn-neu--sm">
        View All (<?= $stats['projects'] ?>) <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
  </div>

  <?php if (empty($recentProjects)): ?>
    <p class="text-muted text-sm py-3 mb-0">No projects added yet.</p>
  <?php else: ?>
    <div class="grid-3" style="gap:var(--space-4);">
      <?php foreach ($recentProjects as $p): ?>
        <div class="neu-card neu-card--inset p-3" style="display:flex; flex-direction:column; justify-content:space-between;">
          <div class="flex items-center gap-3 mb-2">
            <?php if (!empty($p['thumbnail']) && file_exists(UPLOAD_PATH . '/projects/' . $p['thumbnail'])): ?>
              <img src="<?= APP_URL ?>/uploads/projects/<?= e($p['thumbnail']) ?>" alt="Thumbnail"
                   style="width:48px; height:48px; object-fit:cover; border-radius:var(--radius-sm); border:1px solid var(--color-border);">
            <?php else: ?>
              <div style="width:48px; height:48px; border-radius:var(--radius-sm); background:var(--color-accent-soft); display:flex; align-items:center; justify-content:center; color:var(--color-accent); font-size:1.4rem;">
                <i class="bi bi-layers"></i>
              </div>
            <?php endif; ?>
            <div style="min-width:0; flex:1;">
              <div class="font-bold text-truncate text-sm" style="color:var(--color-text-primary);"><?= e($p['title']) ?></div>
              <span class="badge bg-<?= !empty($p['is_published']) ? 'success' : 'secondary' ?>" style="font-size:10px;">
                <?= !empty($p['is_published']) ? 'Published' : 'Draft' ?>
              </span>
            </div>
          </div>
          <div class="flex-between mt-2 pt-2" style="border-top:1px solid rgba(0,0,0,0.05);">
            <a href="<?= APP_URL ?>/project-detail.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="text-xs text-muted">
              <i class="bi bi-eye"></i> Preview
            </a>
            <a href="<?= APP_URL ?>/admin/projects/form.php?id=<?= $p['id'] ?>" class="text-xs text-accent font-semibold">
              Edit <i class="bi bi-pencil-square ms-1"></i>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<div class="grid-2">
  
  <!-- Recent Messages -->
  <div class="neu-card">
    <div class="flex-between mb-4">
      <h3 style="font-size:var(--text-lg); font-weight:var(--weight-bold);">Recent Messages</h3>
      <a href="<?= APP_URL ?>/admin/messages/" class="text-accent text-sm">View all</a>
    </div>
    
    <?php if (empty($recentMsgs)): ?>
      <p class="text-muted">No messages yet.</p>
    <?php else: ?>
      <div class="flex-col gap-3">
        <?php foreach ($recentMsgs as $msg): ?>
          <div class="neu-card neu-card--inset p-3 <?= $msg['is_read'] ? 'opacity-75' : '' ?>">
            <div class="flex-between">
              <div class="font-semibold"><?= e($msg['name']) ?></div>
              <div class="text-xs text-muted"><?= date('M j, Y', strtotime($msg['created_at'])) ?></div>
            </div>
            <div class="text-sm text-muted mt-1 text-truncate" style="max-width: 90%;">
              <?= e($msg['subject']) ?: 'No Subject' ?>
            </div>
            <?php if (!$msg['is_read']): ?>
              <span class="badge bg-danger rounded-pill mt-2">New</span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Recent Activity -->
  <div class="neu-card">
    <div class="flex-between mb-4">
      <h3 style="font-size:var(--text-lg); font-weight:var(--weight-bold);">Recent Activity</h3>
    </div>
    
    <?php if (empty($audits)): ?>
      <p class="text-muted">No recent activity.</p>
    <?php else: ?>
      <div class="timeline" style="padding-left:var(--space-6);">
        <?php foreach ($audits as $audit): ?>
          <div class="timeline-item mb-4" style="border:none; padding:0;">
            <div class="timeline-year" style="font-size:10px;"><?= date('M j, Y H:i', strtotime($audit['created_at'])) ?></div>
            <div class="timeline-event text-sm"><?= e($audit['action']) ?> (<?= e($audit['entity_type']) ?>)</div>
            <div class="timeline-desc" style="font-size:11px;">By <?= e($audit['email'] ?? 'System') ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>

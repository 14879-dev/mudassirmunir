<?php
/**
 * Portfolio OS — Admin Dashboard
 */
$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
require_once __DIR__ . '/includes/admin-header.php';

// Fetch stats
$stats = [
    'projects' => Database::selectOne("SELECT COUNT(*) as c FROM projects")['c'] ?? 0,
    'skills'   => Database::selectOne("SELECT COUNT(*) as c FROM skills")['c'] ?? 0,
    'unread'   => Database::selectOne("SELECT COUNT(*) as c FROM messages WHERE is_read=0")['c'] ?? 0,
];

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

<div class="grid-3 mb-8">
  <div class="neu-card flex align-items-center gap-4">
    <div style="font-size:32px; color:var(--color-accent);"><i class="bi bi-envelope"></i></div>
    <div>
      <div class="text-xs text-muted font-bold text-uppercase">Unread Messages</div>
      <div style="font-size:var(--text-3xl); font-weight:var(--weight-bold);"><?= $stats['unread'] ?></div>
    </div>
  </div>
  
  <div class="neu-card flex align-items-center gap-4">
    <div style="font-size:32px; color:var(--color-peach);"><i class="bi bi-box"></i></div>
    <div>
      <div class="text-xs text-muted font-bold text-uppercase">Total Projects</div>
      <div style="font-size:var(--text-3xl); font-weight:var(--weight-bold);"><?= $stats['projects'] ?></div>
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

<?php
/**
 * Portfolio OS — Admin Messages Inbox
 */
$pageTitle = 'Messages Inbox';
$activeMenu = 'messages';
require_once dirname(__DIR__) . '/includes/admin-header.php';

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verifyCsrf();
    $id = (int)$_POST['delete_id'];
    Database::execute("DELETE FROM messages WHERE id = ?", [$id]);
    logAuditEvent($_SESSION['admin_user_id'], 'message.delete', 'messages', $id);
    $msgSuccess = "Message deleted.";
}

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_id'])) {
    verifyCsrf();
    $id = (int)$_POST['mark_read_id'];
    Database::execute("UPDATE messages SET is_read = 1 WHERE id = ?", [$id]);
    $msgSuccess = "Message marked as read.";
}

$messages = Database::select("SELECT * FROM messages ORDER BY created_at DESC");
?>

<div class="top-bar">
  <div>
    <h1 style="font-size:var(--text-2xl); font-weight:var(--weight-bold);">Inbox</h1>
    <p class="text-muted">Manage contact form submissions.</p>
  </div>
</div>

<?php if (isset($msgSuccess)): ?>
  <div class="neu-alert neu-alert--success mb-4"><?= e($msgSuccess) ?></div>
<?php endif; ?>

<div class="neu-card" style="padding: 0; overflow: hidden;">
  <div class="table-responsive">
    <table class="table table-hover table-borderless mb-0" style="color:var(--color-text-secondary);">
      <thead style="background:var(--color-surface-2); border-bottom:1px solid var(--color-border);">
        <tr>
          <th class="ps-4">Date</th>
          <th>From</th>
          <th>Subject</th>
          <th>Message</th>
          <th class="pe-4 text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($messages)): ?>
          <tr><td colspan="5" class="text-center py-4">No messages found.</td></tr>
        <?php else: ?>
          <?php foreach ($messages as $msg): ?>
            <tr class="<?= $msg['is_read'] ? 'opacity-75' : '' ?>" style="border-bottom:1px solid var(--color-border);">
              <td class="ps-4" style="white-space:nowrap; vertical-align:middle;">
                <?= date('M j, Y H:i', strtotime($msg['created_at'])) ?>
                <?php if (!$msg['is_read']): ?>
                  <span class="badge bg-danger ms-2">New</span>
                <?php endif; ?>
              </td>
              <td style="vertical-align:middle;">
                <strong><?= e($msg['name']) ?></strong><br>
                <small><a href="mailto:<?= e($msg['email']) ?>" class="text-accent"><?= e($msg['email']) ?></a></small>
              </td>
              <td style="vertical-align:middle;"><strong><?= e($msg['subject']) ?></strong></td>
              <td style="vertical-align:middle;">
                <!-- Truncated preview, full view could be a modal -->
                <div style="max-height: 3.5em; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                  <?= nl2br(e($msg['message'])) ?>
                </div>
              </td>
              <td class="pe-4 text-end" style="vertical-align:middle; white-space:nowrap;">
                <!-- View full message -->
                <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#msg-<?= $msg['id'] ?>" title="Read">
                  <i class="bi bi-eye"></i>
                </button>
                
                <?php if (!$msg['is_read']): ?>
                  <form method="POST" style="display:inline-block;">
                    <?= csrfField() ?>
                    <input type="hidden" name="mark_read_id" value="<?= $msg['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary me-1" title="Mark Read">
                      <i class="bi bi-check-lg"></i>
                    </button>
                  </form>
                <?php endif; ?>
                
                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                  <?= csrfField() ?>
                  <input type="hidden" name="delete_id" value="<?= $msg['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (!empty($messages)): foreach ($messages as $msg): ?>
<div class="modal fade" id="msg-<?= $msg['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-lg);">
      <div class="modal-header" style="border-bottom:1px solid var(--color-border);">
        <h5 class="modal-title" style="color:var(--color-text-primary);">
          <?= e($msg['subject'] ?: '(No subject)') ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="color:var(--color-text-secondary);">
        <div class="flex gap-4 mb-4" style="font-size:var(--text-sm);">
          <div><strong style="color:var(--color-text-primary);">From:</strong> <?= e($msg['name']) ?> &lt;<a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a>&gt;</div>
          <div><strong style="color:var(--color-text-primary);">Date:</strong> <?= date('M j, Y H:i', strtotime($msg['created_at'])) ?></div>
        </div>
        <div style="background:var(--color-surface-2);padding:var(--space-4);border-radius:var(--radius-md);white-space:pre-wrap;font-size:var(--text-sm);"><?= nl2br(e($msg['message'])) ?></div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--color-border);">
        <a href="mailto:<?= e($msg['email']) ?>?subject=Re: <?= rawurlencode($msg['subject'] ?? '') ?>" class="btn-neu btn-neu--primary">
          <i class="bi bi-reply"></i> Reply via Email
        </a>
        <button type="button" class="btn-neu btn-neu--ghost" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php endforeach; endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>


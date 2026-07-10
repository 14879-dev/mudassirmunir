<?php
/**
 * Portfolio OS — Admin Projects: Delete
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/security.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

$adminUser = requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/projects/');
    exit;
}

verifyCsrf();

$id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
if (!$id) {
    $_SESSION['flash_error'] = 'Invalid project ID.';
    header('Location: ' . APP_URL . '/admin/projects/');
    exit;
}

$project = Database::selectOne("SELECT thumbnail FROM projects WHERE id = ?", [$id]);
if ($project) {
    // Delete thumbnail file
    if (!empty($project['thumbnail'])) {
        $path = UPLOAD_PATH . '/projects/' . $project['thumbnail'];
        if (file_exists($path)) unlink($path);
    }
    Database::execute("DELETE FROM projects WHERE id = ?", [$id]);
    logAuditEvent($adminUser['id'], 'project.delete', 'projects', $id);
    $_SESSION['flash_success'] = 'Project deleted.';
} else {
    $_SESSION['flash_error'] = 'Project not found.';
}

header('Location: ' . APP_URL . '/admin/projects/');
exit;

<?php
/**
 * Portfolio OS — Admin Skills: Delete (skill OR language)
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/security.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

$adminUser = requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/skills/');
    exit;
}
verifyCsrf();

$type = $_POST['type'] ?? '';
$id   = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;

if (!$id) {
    $_SESSION['flash_error'] = 'Invalid ID.';
    header('Location: ' . APP_URL . '/admin/skills/');
    exit;
}

if ($type === 'skill') {
    Database::execute("DELETE FROM skills WHERE id = ?", [$id]);
    logAuditEvent($adminUser['id'], 'skill.delete', 'skills', $id);
    $_SESSION['flash_success'] = 'Skill deleted.';
} elseif ($type === 'language') {
    Database::execute("DELETE FROM languages WHERE id = ?", [$id]);
    logAuditEvent($adminUser['id'], 'language.delete', 'languages', $id);
    $_SESSION['flash_success'] = 'Language deleted.';
} else {
    $_SESSION['flash_error'] = 'Invalid type.';
}

header('Location: ' . APP_URL . '/admin/skills/');
exit;

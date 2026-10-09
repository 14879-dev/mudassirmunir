<?php
/**
 * Portfolio OS — Admin Certificates: Delete Controller
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/security.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

Database::ensureCertificatesTable();

$adminUser = requireAdmin();
verifyCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/certificates/');
    exit;
}

$id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;

if (!$id) {
    $_SESSION['flash_error'] = 'Invalid certificate ID.';
    header('Location: ' . APP_URL . '/admin/certificates/');
    exit;
}

$cert = Database::selectOne("SELECT title, image FROM certificates WHERE id = ?", [$id]);

if ($cert) {
    if (!empty($cert['image'])) {
        $imgPath = UPLOAD_PATH . '/certificates/' . $cert['image'];
        if (file_exists($imgPath)) @unlink($imgPath);
    }

    Database::execute("DELETE FROM certificates WHERE id = ?", [$id]);
    logAuditEvent($adminUser['id'], 'certificate.delete', 'certificates', $id);
    $_SESSION['flash_success'] = 'Certificate "' . ($cert['title'] ?? '') . '" deleted successfully.';
} else {
    $_SESSION['flash_error'] = 'Certificate not found.';
}

header('Location: ' . APP_URL . '/admin/certificates/');
exit;

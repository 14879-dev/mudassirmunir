<?php
/**
 * Portfolio OS — Admin Certificates: Save (Create + Update)
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/security.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/upload.php';

Database::ensureCertificatesTable();

$adminUser = requireAdmin();
verifyCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/certificates/');
    exit;
}

$id     = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
$isEdit = $id !== null;

$title        = trim($_POST['title'] ?? '');
$issuer       = trim($_POST['issuer'] ?? '');
$issueDate    = trim($_POST['issue_date'] ?? '');
$credentialId = trim($_POST['credential_id'] ?? '');
$credentialUrl= trim($_POST['credential_url'] ?? '');
$category     = in_array($_POST['category'] ?? '', ['award','badge','certificate']) ? $_POST['category'] : 'certificate';
$description  = trim($_POST['description'] ?? '');
$sortOrder    = (int)($_POST['sort_order'] ?? 0);
$isPublished  = isset($_POST['is_published']) ? 1 : 0;

if ($title === '' || $issuer === '') {
    $_SESSION['flash_error'] = 'Title and Issuing Organization are required.';
    $dest = $isEdit ? "?id=$id" : '';
    header("Location: " . APP_URL . "/admin/certificates/form.php$dest");
    exit;
}

// Handle image upload / removal
$imageFilename = $isEdit
    ? (Database::selectOne("SELECT image FROM certificates WHERE id = ?", [$id])['image'] ?? null)
    : null;

if ($isEdit && !empty($_POST['remove_image']) && $imageFilename) {
    $oldPath = UPLOAD_PATH . '/certificates/' . $imageFilename;
    if (file_exists($oldPath)) @unlink($oldPath);
    $imageFilename = null;
}

if (!empty($_FILES['image']['tmp_name'])) {
    $certUploadDir = UPLOAD_PATH . '/certificates';
    if (!is_dir($certUploadDir)) {
        mkdir($certUploadDir, 0755, true);
    }
    $upload = processUpload(
        $_FILES['image'],
        $certUploadDir,
        ALLOWED_IMG_TYPES,
        3 * 1024 * 1024
    );
    if ($upload['success']) {
        if ($imageFilename) {
            $old = $certUploadDir . '/' . $imageFilename;
            if (file_exists($old)) @unlink($old);
        }
        $imageFilename = $upload['filename'];
    } else {
        $_SESSION['flash_error'] = 'Image upload error: ' . $upload['error'];
        $dest = $isEdit ? "?id=$id" : '';
        header("Location: " . APP_URL . "/admin/certificates/form.php$dest");
        exit;
    }
}

if ($isEdit) {
    Database::execute(
        "UPDATE certificates 
         SET title=?, issuer=?, issue_date=?, credential_id=?, credential_url=?, category=?, description=?, image=?, sort_order=?, is_published=? 
         WHERE id=?",
        [$title, $issuer, $issueDate, $credentialId, $credentialUrl, $category, $description, $imageFilename, $sortOrder, $isPublished, $id]
    );
    logAuditEvent($adminUser['id'], 'certificate.update', 'certificates', $id);
    $_SESSION['flash_success'] = "Certificate \"$title\" updated successfully.";
} else {
    $newId = Database::insert(
        "INSERT INTO certificates 
         (title, issuer, issue_date, credential_id, credential_url, category, description, image, sort_order, is_published) 
         VALUES (?,?,?,?,?,?,?,?,?,?)",
        [$title, $issuer, $issueDate, $credentialId, $credentialUrl, $category, $description, $imageFilename, $sortOrder, $isPublished]
    );
    logAuditEvent($adminUser['id'], 'certificate.create', 'certificates', (int)$newId);
    $_SESSION['flash_success'] = "Certificate \"$title\" added successfully.";
}

header('Location: ' . APP_URL . '/admin/certificates/');
exit;

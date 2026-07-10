<?php
/**
 * Portfolio OS — Admin Projects: Save (Create + Update)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/security.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/upload.php';

$adminUser = requireAdmin();
verifyCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/projects/');
    exit;
}

$id    = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
$isEdit = $id !== null;

// Validate required fields
$title  = trim($_POST['title'] ?? '');
$slug   = trim($_POST['slug'] ?? '');
if ($title === '' || $slug === '') {
    $_SESSION['flash_error'] = 'Title and slug are required.';
    $dest = $isEdit ? "?id=$id" : '';
    header("Location: " . APP_URL . "/admin/projects/form.php$dest");
    exit;
}

// Sanitize slug
$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));

// Check slug uniqueness (exclude self on edit)
$slugExists = Database::selectOne(
    "SELECT id FROM projects WHERE slug = ? AND id != ?",
    [$slug, $id ?? 0]
);
if ($slugExists) {
    $_SESSION['flash_error'] = "Slug '$slug' already exists. Choose a different one.";
    $dest = $isEdit ? "?id=$id" : '';
    header("Location: " . APP_URL . "/admin/projects/form.php$dest");
    exit;
}

// Tags: convert comma-separated to JSON
$tagsInput = trim($_POST['tags'] ?? '');
$tagsArr   = array_filter(array_map('trim', explode(',', $tagsInput)));
$tagsJson  = json_encode(array_values($tagsArr));

// Handle thumbnail upload
$thumbnailFilename = $isEdit
    ? (Database::selectOne("SELECT thumbnail FROM projects WHERE id = ?", [$id])['thumbnail'] ?? null)
    : null;

// Remove thumbnail if checked
if (!empty($_POST['remove_thumbnail']) && $thumbnailFilename) {
    $oldPath = UPLOAD_PATH . '/projects/' . $thumbnailFilename;
    if (file_exists($oldPath)) unlink($oldPath);
    $thumbnailFilename = null;
}

if (!empty($_FILES['thumbnail']['tmp_name'])) {
    $upload = processUpload(
        $_FILES['thumbnail'],
        UPLOAD_PATH . '/projects',
        ALLOWED_IMG_TYPES,
        2 * 1024 * 1024
    );
    if ($upload['success']) {
        // Delete old thumbnail
        if ($thumbnailFilename) {
            $oldPath = UPLOAD_PATH . '/projects/' . $thumbnailFilename;
            if (file_exists($oldPath)) unlink($oldPath);
        }
        $thumbnailFilename = $upload['filename'];
    } else {
        $_SESSION['flash_error'] = 'Upload failed: ' . $upload['error'];
        $dest = $isEdit ? "?id=$id" : '';
        header("Location: " . APP_URL . "/admin/projects/form.php$dest");
        exit;
    }
}

$fields = [
    'title'             => $title,
    'slug'              => $slug,
    'short_description' => trim($_POST['short_description'] ?? ''),
    'full_description'  => trim($_POST['full_description'] ?? ''),
    'github_url'        => trim($_POST['github_url'] ?? ''),
    'demo_url'          => trim($_POST['demo_url'] ?? ''),
    'store_url'         => trim($_POST['store_url'] ?? ''),
    'role'              => trim($_POST['role'] ?? ''),
    'tech_stack'        => !empty($_POST['tech_stack']) ? json_encode(array_values(array_filter(array_map('trim', explode(',', $_POST['tech_stack']))))) : null,
    'tags'              => $tagsJson,
    'is_published'      => isset($_POST['is_published']) ? (int)$_POST['is_published'] : 1,
    'sort_order'        => max(0, (int)($_POST['sort_order'] ?? 0)),
    'thumbnail'         => $thumbnailFilename,
];

if ($isEdit) {
    $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
    Database::execute(
        "UPDATE projects SET $sets WHERE id = ?",
        [...array_values($fields), $id]
    );
    logAuditEvent($adminUser['id'], 'project.update', 'projects', $id);
    $_SESSION['flash_success'] = 'Project updated successfully.';
} else {
    $cols = implode(', ', array_keys($fields));
    $ph   = implode(', ', array_fill(0, count($fields), '?'));
    $newId = Database::insert("INSERT INTO projects ($cols) VALUES ($ph)", array_values($fields));
    logAuditEvent($adminUser['id'], 'project.create', 'projects', (int)$newId);
    $_SESSION['flash_success'] = 'Project created successfully.';
}

header('Location: ' . APP_URL . '/admin/projects/');
exit;

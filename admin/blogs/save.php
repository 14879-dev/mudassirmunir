<?php
/**
 * Portfolio OS — Admin Blogs Save
 */
require_once dirname(__DIR__) . '/../config/config.php';
require_once dirname(__DIR__) . '/../includes/db.php';
require_once dirname(__DIR__) . '/../includes/security.php';
require_once dirname(__DIR__) . '/../includes/auth.php';
require_once dirname(__DIR__) . '/../includes/upload.php';

requireAdmin();
verifyCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/blogs/');
}

$id = (int)($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$excerpt = trim($_POST['excerpt'] ?? '');
$content = trim($_POST['content'] ?? '');
$is_published = isset($_POST['is_published']) ? 1 : 0;

if (empty($title) || empty($slug) || empty($content)) {
    $_SESSION['flash_error'] = "Title, slug, and content are required.";
    redirect('/admin/blogs/form.php?id=' . $id);
}

// Slug validation format
$slug = strtolower(preg_replace('/[^a-z0-9\-]/', '-', $slug));

// Check unique slug
$existing = Database::selectOne("SELECT id FROM blogs WHERE slug = ? AND id != ?", [$slug, $id]);
if ($existing) {
    $_SESSION['flash_error'] = "Slug is already in use.";
    redirect('/admin/blogs/form.php?id=' . $id);
}

// File upload
$coverImage = '';
if ($id > 0) {
    $current = Database::selectOne("SELECT cover_image FROM blogs WHERE id = ?", [$id]);
    $coverImage = $current['cover_image'] ?? '';
}

if (!empty($_FILES['cover_image']['name'])) {
    $uploaded = handleUpload($_FILES['cover_image'], 'blogs');
    if ($uploaded) {
        $coverImage = $uploaded;
    } else {
        $_SESSION['flash_error'] = "Failed to upload image. Max size 2MB, allowed types: JPG, PNG, WEBP.";
        redirect('/admin/blogs/form.php?id=' . $id);
    }
}

if ($id > 0) {
    Database::execute(
        "UPDATE blogs SET title = ?, slug = ?, excerpt = ?, content = ?, cover_image = ?, is_published = ? WHERE id = ?",
        [$title, $slug, $excerpt, $content, $coverImage, $is_published, $id]
    );
    $_SESSION['flash_success'] = "Blog post updated.";
} else {
    Database::execute(
        "INSERT INTO blogs (title, slug, excerpt, content, cover_image, is_published) VALUES (?, ?, ?, ?, ?, ?)",
        [$title, $slug, $excerpt, $content, $coverImage, $is_published]
    );
    $_SESSION['flash_success'] = "Blog post created.";
}

redirect('/admin/blogs/');

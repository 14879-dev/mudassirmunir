<?php
/**
 * Portfolio OS — Admin Blogs Delete
 */
require_once dirname(__DIR__) . '/../config/config.php';
require_once dirname(__DIR__) . '/../includes/db.php';
require_once dirname(__DIR__) . '/../includes/security.php';
require_once dirname(__DIR__) . '/../includes/auth.php';

requireAdmin();
verifyCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/blogs/');
}

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    Database::execute("DELETE FROM blogs WHERE id = ?", [$id]);
    $_SESSION['flash_success'] = "Blog post deleted.";
}

redirect('/admin/blogs/');

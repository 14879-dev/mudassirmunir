<?php
/**
 * Portfolio OS — Admin Header
 */

require_once dirname(__DIR__) . '/../config/config.php';
require_once dirname(__DIR__) . '/../includes/db.php';
require_once dirname(__DIR__) . '/../includes/security.php';
require_once dirname(__DIR__) . '/../includes/auth.php';

// Protect all admin pages that include this header
$adminUser = requireAdmin();

$pageTitle = $pageTitle ?? "Admin Dashboard";
$activeMenu = $activeMenu ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= e($pageTitle) ?> — Portfolio OS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/favicon.svg">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/design-tokens.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/neumorphic.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
  <style>
    body { background-color: var(--color-base); min-height: 100vh; display: flex; }
    
    .admin-sidebar {
      width: 260px;
      background: var(--color-surface);
      border-right: 1px solid var(--color-border);
      height: 100vh;
      position: fixed;
      top: 0; left: 0;
      display: flex; flex-direction: column;
      padding: var(--space-6) var(--space-4);
      z-index: 100;
    }
    
    .admin-content {
      flex: 1;
      margin-left: 260px;
      padding: var(--space-8);
      min-height: 100vh;
    }

    .admin-nav { display: flex; flex-direction: column; gap: var(--space-2); margin-top: var(--space-8); flex:1; }
    
    .admin-nav-item {
      display: flex; align-items: center; gap: var(--space-3);
      padding: var(--space-3) var(--space-4);
      border-radius: var(--radius-md);
      color: var(--color-text-secondary);
      font-weight: var(--weight-medium);
      text-decoration: none;
      transition: all var(--duration-fast);
    }
    .admin-nav-item:hover {
      background: var(--color-surface-2);
      color: var(--color-text-primary);
    }
    .admin-nav-item.active {
      background: rgba(167, 139, 250, 0.15);
      color: var(--color-accent);
      border-left: 3px solid var(--color-accent);
    }

    .top-bar {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: var(--space-8);
    }

    @media (max-width: 992px) {
      .admin-sidebar { transform: translateX(-100%); transition: transform 0.3s; }
      .admin-sidebar.open { transform: translateX(0); }
      .admin-content { margin-left: 0; padding: var(--space-4); }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<aside class="admin-sidebar" id="sidebar">
  <div class="flex items-center gap-3 px-2">
    <div style="font-size:24px;">⚙️</div>
    <div style="font-weight:var(--weight-bold); font-size:var(--text-lg);">Portfolio OS</div>
  </div>

  <nav class="admin-nav">
    <a href="<?= APP_URL ?>/admin/dashboard.php" class="admin-nav-item <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
      <i class="bi bi-grid-1x2"></i> Dashboard
    </a>
    <a href="<?= APP_URL ?>/admin/projects/" class="admin-nav-item <?= $activeMenu === 'projects' ? 'active' : '' ?>">
      <i class="bi bi-briefcase"></i> Projects
    </a>
    
    <a href="<?= APP_URL ?>/admin/blogs/" class="admin-nav-item <?= $activeMenu === 'blogs' ? 'active' : '' ?>">
      <i class="bi bi-journal-text"></i> Blogs
    </a>
    
    <a href="<?= APP_URL ?>/admin/skills/" class="admin-nav-item <?= $activeMenu === 'skills' ? 'active' : '' ?>">
      <i class="bi bi-code-slash"></i> Skills &amp; Tools
    </a>
    <a href="<?= APP_URL ?>/admin/about/" class="admin-nav-item <?= $activeMenu === 'about' ? 'active' : '' ?>">
      <i class="bi bi-person"></i> Hero & Bio
    </a>
    <a href="<?= APP_URL ?>/admin/cv/" class="admin-nav-item <?= $activeMenu === 'cv' ? 'active' : '' ?>">
      <i class="bi bi-file-earmark-pdf"></i> Resume (CV)
    </a>
    
    <div class="neu-divider" style="margin: var(--space-4) 0;"></div>
    
    <a href="<?= APP_URL ?>/admin/messages/" class="admin-nav-item <?= $activeMenu === 'messages' ? 'active' : '' ?>">
      <i class="bi bi-envelope"></i> Inbox
      <?php 
        $unread = Database::selectOne("SELECT COUNT(*) as c FROM messages WHERE is_read=0")['c'] ?? 0;
        if ($unread > 0): 
      ?>
        <span class="badge bg-danger rounded-pill ms-auto"><?= $unread ?></span>
      <?php endif; ?>
    </a>
    
    <a href="<?= APP_URL ?>/admin/settings/" class="admin-nav-item <?= $activeMenu === 'settings' ? 'active' : '' ?>">
      <i class="bi bi-gear"></i> Settings
    </a>
  </nav>

  <div class="mt-auto">
    <div class="text-xs text-muted mb-3 px-2">Logged in as:<br><?= e($adminUser['email']) ?></div>
    <form action="<?= APP_URL ?>/api/auth/logout.php" method="POST">
      <?= csrfField() ?>
      <button type="submit" class="btn-neu btn-neu--ghost w-full text-start">
        <i class="bi bi-box-arrow-left"></i> Sign Out
      </button>
    </form>
  </div>
</aside>

<main class="admin-content">
  <!-- Mobile toggle -->
  <div class="d-lg-none mb-4">
    <button class="btn-neu btn-neu--secondary" onclick="document.getElementById('sidebar').classList.toggle('open')">
      <i class="bi bi-list"></i> Menu
    </button>
  </div>

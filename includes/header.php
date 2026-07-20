<?php
/**
 * Portfolio OS — Shared HTML Head
 */
$pageTitle  = $pageTitle  ?? "Mudassir — Portfolio";
$pageDesc   = $pageDesc   ?? "Software Engineering student, game developer, web developer & co-founder of Hexspire Solutions.";
$activePage = $activePage ?? 'home';
$canonicalUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#dde6f5">
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/favicon.svg">

  <!-- SEO -->
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="author" content="Mudassir">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">

  <!-- Open Graph -->
  <meta property="og:title"       content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
  <?php if (isset($ogImage)): ?>
  <meta property="og:image"       content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>

  <!-- Twitter Card -->
  <meta name="twitter:card"       content="<?= isset($ogImage) ? 'summary_large_image' : 'summary' ?>">
  <meta name="twitter:title"      content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">
  <?php if (isset($ogImage)): ?>
  <meta name="twitter:image"      content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>

  <!-- Preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://cdn.jsdelivr.net">

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <!-- Devicons (cdnjs) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/devicon/2.15.1/devicon.min.css">

  <!-- Bootstrap Icons (cdnjs) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

  <!-- Design System -->
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/design-tokens.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/neumorphic.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dock.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/animations.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/images/favicon.svg">

  <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>

<!-- Page Loader -->
<div id="page-loader" class="page-loader" role="status" aria-label="Loading">
  <span class="page-loader__logo">M.</span>
</div>

<!-- ── TOP NAVIGATION BAR ── -->
<header class="top-nav" role="banner">
  <a href="<?= APP_URL ?>/index.php#home" class="top-nav__brand">
    <div class="top-nav__logo">M</div>
    MUDASSIR
  </a>
  <div class="top-nav__badge">
    <span class="dot"></span>
    AVAILABLE FOR FREELANCE
  </div>
</header>

<!-- Lightbox -->
<div id="lightbox" class="lightbox-overlay" role="dialog" aria-modal="true" aria-label="Image viewer">
  <img id="lightbox-img" class="lightbox-img" src="" alt="">
  <button id="lightbox-close" class="lightbox-close" aria-label="Close">
    <i class="bi bi-x-lg"></i>
  </button>
</div>

<!-- Main content wrapper -->
<main id="main-content" class="dock-clearance" tabindex="-1">

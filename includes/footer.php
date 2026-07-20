<?php
/**
 * Portfolio OS — Footer (Icon-only dock, Nova Glass theme)
 */

$navItems = [
  'home'     => ['label'=>'Home',     'icon'=>'bi-house-fill',              'href'=> APP_URL . '/index.php#home'],
  'about'    => ['label'=>'About Me', 'icon'=>'bi-person-fill',             'href'=> APP_URL . '/index.php#about'],
  'projects' => ['label'=>'Projects', 'icon'=>'bi-layers-fill',             'href'=> APP_URL . '/index.php#projects'],
  'blog'     => ['label'=>'Blog',     'icon'=>'bi-journal-text',            'href'=> APP_URL . '/index.php#blog'],
  'contact'  => ['label'=>'Contact',  'icon'=>'bi-envelope-fill',           'href'=> APP_URL . '/index.php#contact'],
  'cv'       => ['label'=>'Resume',   'icon'=>'bi-file-earmark-arrow-down', 'href'=> APP_URL . '/cv-download.php'],
];
?>

</main><!-- /#main-content -->

<!-- Footer bottom bar -->
<footer class="site-footer">
  <span>© <?= date('Y') ?> Mudassir. All rights reserved.</span>
  <span>DESIGNING TOMORROW, TODAY.</span>
</footer>

<!-- Scroll to top -->
<button id="scroll-top" class="scroll-top" aria-label="Scroll to top" title="Back to top">
  <i class="bi bi-chevron-up"></i>
</button>

<!-- ── Icon-Only Dock Nav ─────────────────────────────── -->
<nav id="dock-wrapper" class="dock-wrapper" role="navigation" aria-label="Main navigation">
  <div id="main-dock" class="dock" role="menubar">
    <?php foreach ($navItems as $key => $item):
      $isActive = ($activePage === $key) ? 'active' : '';
    ?>
      <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
         class="dock-item <?= $isActive ?>"
         role="menuitem"
         aria-label="<?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>"
         title="<?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="dock-icon">
          <i class="bi <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
        </div>
        <span class="dock-dot" aria-hidden="true"></span>
      </a>
      <?php if ($key === 'services'): ?>
        <div class="dock-separator" role="separator" aria-hidden="true"></div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</nav>

<!-- ── Scripts ────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="<?= APP_URL ?>/assets/js/dock.js" defer></script>

<?php if (isset($extraScripts)) echo $extraScripts; ?>

</body>
</html>

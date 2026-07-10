<?php
/**
 * Portfolio OS — Admin Footer
 */
?>
</main><!-- /.admin-content -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<!-- Main Admin JS (if any) -->
<script>
  // Simple toast notification function for admin actions
  function showAdminToast(message, type = 'info') {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.style.zIndex = '9999';
    toast.style.bottom = '20px';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }
</script>

<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>

<?php
/**
 * Portfolio OS — Admin Logout API
 */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    logSecurityEvent('logout', ['user_id' => $_SESSION['admin_user_id'] ?? null]);
    destroyAdminSession();
}

header('Location: ' . APP_URL . '/admin/login.php');
exit;

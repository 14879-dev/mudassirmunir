<?php
/**
 * Portfolio OS — Admin Settings Save
 */
require_once dirname(__DIR__) . '/../config/config.php';
require_once dirname(__DIR__) . '/../includes/db.php';
require_once dirname(__DIR__) . '/../includes/security.php';
require_once dirname(__DIR__) . '/../includes/auth.php';

$adminUser = requireAdmin();
verifyCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/settings/');
}

$action = $_POST['action_type'] ?? '';

if ($action === 'update_email') {
    $newEmail = trim($_POST['email'] ?? '');
    
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = "Invalid email format.";
        redirect('/admin/settings/');
    }
    
    if ($newEmail === $adminUser['email']) {
        $_SESSION['flash_success'] = "Email updated successfully.";
        redirect('/admin/settings/');
    }
    
    // Check for unique email (except current user)
    $existing = Database::selectOne("SELECT id FROM users WHERE email = ? AND id != ?", [$newEmail, $adminUser['id']]);
    if ($existing) {
        $_SESSION['flash_error'] = "That email is already in use.";
        redirect('/admin/settings/');
    }
    
    Database::execute("UPDATE users SET email = ? WHERE id = ?", [$newEmail, $adminUser['id']]);
    
    // Update session
    $_SESSION['admin_email'] = $newEmail;
    
    $_SESSION['flash_success'] = "Email updated successfully.";
    redirect('/admin/settings/');

} elseif ($action === 'update_password') {
    
    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
        $_SESSION['flash_error'] = "All password fields are required.";
        redirect('/admin/settings/');
    }
    
    if (strlen($newPass) < 8) {
        $_SESSION['flash_error'] = "New password must be at least 8 characters.";
        redirect('/admin/settings/');
    }
    
    if ($newPass !== $confirmPass) {
        $_SESSION['flash_error'] = "New passwords do not match.";
        redirect('/admin/settings/');
    }
    
    // Verify current password
    $userRecord = Database::selectOne("SELECT password_hash FROM users WHERE id = ?", [$adminUser['id']]);
    if (!$userRecord || !password_verify($currentPass, $userRecord['password_hash'])) {
        $_SESSION['flash_error'] = "Incorrect current password.";
        redirect('/admin/settings/');
    }
    
    // Hash new password
    $newHash = password_hash($newPass, PASSWORD_DEFAULT);
    Database::execute("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $adminUser['id']]);
    
    // Log the user out for security
    session_unset();
    session_destroy();
    
    startSecureSession();
    $_SESSION['flash_success'] = "Password updated successfully. Please log in again.";
    redirect('/admin/login.php');
}

// Fallback
redirect('/admin/settings/');

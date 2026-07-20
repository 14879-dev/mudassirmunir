<?php
/**
 * Portfolio OS - Admin Debug Page (DELETE AFTER USE)
 * Visit this page to see what error is happening
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre style='font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;font-size:13px;'>";
echo "=== PORTFOLIO OS DEBUG ===\n\n";

// 1. Check PHP version
echo "PHP Version: " . phpversion() . "\n\n";

// 2. Check config loads
echo "--- Loading config ---\n";
try {
    require_once __DIR__ . '/config/config.php';
    echo "APP_URL: " . APP_URL . "\n";
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    echo "Config OK\n\n";
} catch (Throwable $e) {
    echo "CONFIG ERROR: " . $e->getMessage() . "\n\n";
}

// 3. Check DB
echo "--- DB Connection ---\n";
try {
    require_once __DIR__ . '/includes/db.php';
    $users = Database::select("SELECT id, email, role FROM users");
    echo "Users in DB: " . count($users) . "\n";
    foreach($users as $u) echo " - {$u['email']} ({$u['role']})\n";
    
    $blogs = Database::select("SELECT COUNT(*) as c FROM blogs");
    echo "Blogs count: " . ($blogs[0]['c'] ?? 0) . "\n";
    echo "DB OK\n\n";
} catch (Throwable $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n\n";
}

// 4. Check session / auth
echo "--- Session ---\n";
try {
    require_once __DIR__ . '/includes/security.php';
    startSecureSession();
    echo "Session name: " . session_name() . "\n";
    echo "Session ID: " . session_id() . "\n";
    echo "admin_user_id in session: " . (isset($_SESSION['admin_user_id']) ? $_SESSION['admin_user_id'] : 'NOT SET') . "\n\n";
} catch (Throwable $e) {
    echo "SESSION ERROR: " . $e->getMessage() . "\n\n";
}

// 5. Check file paths
echo "--- File Paths ---\n";
$files = [
    'admin-header.php' => __DIR__ . '/admin/includes/admin-header.php',
    'admin-footer.php' => __DIR__ . '/admin/includes/admin-footer.php',
    'blogs/form.php'   => __DIR__ . '/admin/blogs/form.php',
    'blogs/index.php'  => __DIR__ . '/admin/blogs/index.php',
    'blogs/save.php'   => __DIR__ . '/admin/blogs/save.php',
];
foreach ($files as $name => $path) {
    echo ($path && file_exists($path) ? "✓ " : "✗ MISSING ") . $name . "\n";
}

echo "\n=== END DEBUG ===\n";
echo "</pre>";

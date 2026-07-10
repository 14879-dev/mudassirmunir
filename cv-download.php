<?php
/**
 * Portfolio OS — Secure CV Download Endpoint (FR-5.1)
 * Serves the CV file without revealing its actual path or allowing execution.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

// Rate limit downloads (e.g. max 10 per hour per IP) to prevent bandwidth scraping
checkRateLimit('cv_download', 10, 3600);

// Get the current active CV
$cv = Database::selectOne("SELECT * FROM cv_files WHERE is_current = 1 LIMIT 1");

if (!$cv) {
    http_response_code(404);
    $pageTitle = "CV Not Found";
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container page-section text-center">
            <h1 class="mb-4">Resume Not Available</h1>
            <p class="text-muted">The resume is currently being updated. Please check back later.</p>
            <a href="' . APP_URL . '/index.php" class="btn-neu btn-neu--primary mt-4">Back to Home</a>
          </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$filePath = rtrim(CV_PATH, '/') . '/' . $cv['filename'];

if (!file_exists($filePath)) {
    // DB thinks it exists, but file is missing
    error_log('[DOWNLOAD] CV file missing on disk: ' . $filePath);
    http_response_code(404);
    $pageTitle = "CV Not Found";
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container page-section text-center"><h1 class="mb-4">File Not Found</h1></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Clear output buffers to ensure clean file delivery
while (ob_get_level()) {
    ob_end_clean();
}

// Extract extension to set correct MIME type (though SRS states PDF)
$ext = strtolower(pathinfo($cv['original_name'], PATHINFO_EXTENSION));
$mime = match($ext) {
    'pdf' => 'application/pdf',
    default => 'application/octet-stream'
};

// Log the download event (optional, but good for tracking engagement)
// Using an info level security_log, or a dedicated analytics table.
try {
    Database::execute(
        "INSERT INTO security_log (event_type, ip_address, user_agent, details) VALUES (?,?,?,?)",
        ['cv_download', getClientIp(), $_SERVER['HTTP_USER_AGENT'] ?? '', json_encode(['cv_id' => $cv['id']])]
    );
} catch (Throwable $e) {}

// Serve file
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="Mudassir_CV.' . $ext . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');

readfile($filePath);
exit;

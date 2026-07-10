<?php
/**
 * Portfolio OS — Secure File Upload Handler
 * Validates MIME type via magic bytes + extension allow-list.
 * Files stored outside web-executable path with random names.
 */

declare(strict_types=1);

require_once __DIR__ . '/security.php';

// ============================================================
// ALLOWED FILE TYPES
// ============================================================

const ALLOWED_CV_TYPES = [
    'application/pdf' => ['pdf'],
];

// Magic bytes: first N bytes of file mapped to MIME
const MAGIC_BYTES = [
    'application/pdf' => "\x25\x50\x44\x46",  // %PDF
    'image/jpeg'      => "\xFF\xD8\xFF",
    'image/png'       => "\x89PNG",
    'image/gif'       => 'GIF8',
    'image/webp'      => 'RIFF',
    'image/svg+xml'   => null,  // Handled separately (XML-based)
];

const ALLOWED_IMG_TYPES = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png'  => ['png'],
    'image/gif'  => ['gif'],
    'image/webp' => ['webp'],
];

// ============================================================
// CORE UPLOAD FUNCTION
// ============================================================

/**
 * Validate and store an uploaded file securely.
 *
 * @param array  $file          $_FILES['field'] entry
 * @param string $destination   Absolute destination directory
 * @param array  $allowedTypes  Map of MIME => [extensions]
 * @param int    $maxBytes      Maximum file size in bytes
 * @return array ['success'=>bool, 'filename'=>string|null, 'error'=>string|null]
 */
function processUpload(
    array  $file,
    string $destination,
    array  $allowedTypes,
    int    $maxBytes
): array {
    // 1. Basic PHP upload error check
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => uploadErrorMessage($file['error'])];
    }

    // 2. Size check
    if ($file['size'] > $maxBytes) {
        $mb = round($maxBytes / 1048576, 1);
        return ['success' => false, 'error' => "File exceeds maximum size of {$mb} MB."];
    }

    // 3. Verify it's an actual upload (security)
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'Invalid upload.'];
    }

    // 4. MIME type via finfo (magic bytes) — NOT trusting client-provided MIME
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mimeType, $allowedTypes)) {
        logSecurityEvent('file_rejected', [
            'claimed_name' => $file['name'],
            'detected_mime' => $mimeType,
        ]);
        return ['success' => false, 'error' => 'File type not allowed.'];
    }

    // 5. Magic byte verification
    if (isset(MAGIC_BYTES[$mimeType])) {
        $signature = MAGIC_BYTES[$mimeType];
        $handle    = fopen($file['tmp_name'], 'rb');
        $header    = fread($handle, strlen($signature));
        fclose($handle);

        if ($header !== $signature) {
            logSecurityEvent('file_rejected', [
                'reason' => 'magic_byte_mismatch',
                'mime'   => $mimeType,
            ]);
            return ['success' => false, 'error' => 'File content does not match its type.'];
        }
    }

    // 6. Extension check (allow-list)
    $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($originalExt, $allowedTypes[$mimeType], true)) {
        return ['success' => false, 'error' => 'File extension not allowed.'];
    }

    // 7. Generate randomized filename (prevents directory traversal + guessing)
    $randomName = bin2hex(random_bytes(16)) . '.' . $originalExt;

    // 8. Ensure destination exists
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $targetPath = rtrim($destination, '/') . '/' . $randomName;

    // 9. Move file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        error_log('[UPLOAD] Failed to move uploaded file to: ' . $targetPath);
        return ['success' => false, 'error' => 'Failed to save file. Please try again.'];
    }

    // 10. For images: strip EXIF metadata
    if (str_starts_with($mimeType, 'image/') && function_exists('imagecreatefromstring')) {
        stripExif($targetPath, $mimeType);
    }

    return ['success' => true, 'filename' => $randomName, 'error' => null];
}

// ============================================================
// CV UPLOAD (specialized)
// ============================================================

function uploadCv(array $file): array
{
    return processUpload($file, CV_PATH, ALLOWED_CV_TYPES, UPLOAD_MAX_CV_SIZE);
}

// ============================================================
// IMAGE UPLOAD (specialized)
// ============================================================

function uploadProjectImage(array $file): array
{
    return processUpload($file, IMG_PATH, ALLOWED_IMG_TYPES, UPLOAD_MAX_IMG_SIZE);
}

// ============================================================
// EXIF STRIPPING (re-encode image to remove embedded data)
// ============================================================

function stripExif(string $path, string $mimeType): void
{
    try {
        $image = null;
        switch ($mimeType) {
            case 'image/jpeg': $image = imagecreatefromjpeg($path); break;
            case 'image/png':  $image = imagecreatefrompng($path);  break;
            case 'image/gif':  $image = imagecreatefromgif($path);  break;
            case 'image/webp': $image = imagecreatefromwebp($path); break;
        }
        if ($image) {
            switch ($mimeType) {
                case 'image/jpeg': imagejpeg($image, $path, 90); break;
                case 'image/png':  imagepng($image, $path, 8);   break;
                case 'image/gif':  imagegif($image, $path);      break;
                case 'image/webp': imagewebp($image, $path, 90); break;
            }
            imagedestroy($image);
        }
    } catch (Throwable $e) {
        error_log('[UPLOAD] EXIF strip failed: ' . $e->getMessage());
        // Non-fatal — file still saved
    }
}

// ============================================================
// HELPER
// ============================================================

function uploadErrorMessage(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large.',
        UPLOAD_ERR_PARTIAL    => 'Upload was interrupted.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Temporary directory missing.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload blocked by server extension.',
        default               => 'Unknown upload error.',
    };
}

/**
 * Serve a file for download via controlled endpoint.
 * Never reveals the real path to the client.
 */
function serveFile(string $absolutePath, string $publicName, string $mimeType): never
{
    if (!file_exists($absolutePath) || !is_readable($absolutePath)) {
        http_response_code(404);
        exit('File not found.');
    }

    // Clear any output buffer
    while (ob_get_level()) ob_end_clean();

    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . addslashes($publicName) . '"');
    header('Content-Length: ' . filesize($absolutePath));
    header('Cache-Control: private, no-store, no-cache');
    header('X-Content-Type-Options: nosniff');

    readfile($absolutePath);
    exit;
}

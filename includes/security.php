<?php
/**
 * Portfolio OS — Security Helpers
 * CSRF protection, rate limiting, input validation, sanitization.
 * All functions are pure — no side-effects outside their scope.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

// ============================================================
// SESSION BOOTSTRAP
// ============================================================

function startSecureSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

// ============================================================
// CSRF PROTECTION
// ============================================================

function csrfToken(): string
{
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH / 2));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return sprintf(
        '<input type="hidden" name="csrf_token" value="%s">',
        htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8')
    );
}

function verifyCsrf(): void
{
    startSecureSession();
    $token     = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected  = $_SESSION['csrf_token'] ?? '';

    if (empty($expected) || !hash_equals($expected, $token)) {
        logSecurityEvent('csrf_fail', ['endpoint' => $_SERVER['REQUEST_URI'] ?? '']);
        http_response_code(403);
        exit(json_encode(['error' => 'Invalid request token']));
    }
}

// ============================================================
// RATE LIMITING (DB-backed, per IP per endpoint)
// ============================================================

function getClientIp(): string
{
    // Prefer real IP behind trusted proxies — adjust to your infra
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($headers as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

function checkRateLimit(string $endpoint, int $maxAttempts, int $windowSeconds): void
{
    $ip  = getClientIp();
    $now = new DateTime();
    $windowStart = (clone $now)->modify("-{$windowSeconds} seconds")->format('Y-m-d H:i:s');

    // Clean up expired windows
    Database::execute(
        "DELETE FROM rate_limits WHERE window_start < ? AND endpoint = ?",
        [$windowStart, $endpoint]
    );

    $row = Database::selectOne(
        "SELECT id, attempts FROM rate_limits WHERE ip_address=? AND endpoint=?",
        [$ip, $endpoint]
    );

    if ($row) {
        if ((int)$row['attempts'] >= $maxAttempts) {
            logSecurityEvent('rate_limit', ['endpoint' => $endpoint, 'ip' => $ip]);
            http_response_code(429);
            header('Retry-After: ' . $windowSeconds);
            exit(json_encode(['error' => 'Too many requests. Please try again later.']));
        }
        Database::execute(
            "UPDATE rate_limits SET attempts = attempts + 1 WHERE id = ?",
            [$row['id']]
        );
    } else {
        Database::execute(
            "INSERT INTO rate_limits (ip_address, endpoint, attempts, window_start) VALUES (?,?,1,NOW())",
            [$ip, $endpoint]
        );
    }
}

// ============================================================
// INPUT VALIDATION & SANITIZATION
// ============================================================

/** Strip tags, trim, and encode for HTML output */
function sanitizeString(string $value, int $maxLength = 255): string
{
    $value = strip_tags(trim($value));
    return mb_substr($value, 0, $maxLength);
}

/** Validate email format */
function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        && strlen($email) <= 255;
}

/** Validate URL */
function validateUrl(string $url): bool
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false
        && strlen($url) <= 1000;
}

/** Encode for safe HTML output (XSS prevention) */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Encode for JSON output */
function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    // Never include stack traces or DB internals
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Check honeypot field — must be empty */
function checkHoneypot(string $fieldName = 'website'): void
{
    $value = $_POST[$fieldName] ?? '';
    if ($value !== '') {
        // Silent rejection — bot thinks it succeeded
        http_response_code(200);
        exit(json_encode(['success' => true]));
    }
}

// ============================================================
// SECURITY EVENT LOGGING
// ============================================================

function logSecurityEvent(string $eventType, array $details = []): void
{
    $ip        = getClientIp();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    try {
        Database::execute(
            "INSERT INTO security_log (event_type, ip_address, user_agent, details)
             VALUES (?, ?, ?, ?)",
            [$eventType, $ip, mb_substr($userAgent, 0, 500), json_encode($details)]
        );
    } catch (Throwable $e) {
        // Fallback to file log if DB unavailable
        error_log("[SECURITY] $eventType | IP:$ip | " . json_encode($details));
    }
}

// ============================================================
// AUDIT LOGGING
// ============================================================

function logAuditEvent(
    int $userId,
    string $action,
    string $entityType,
    ?int $entityId = null,
    array $diffSummary = []
): void {
    $ip = getClientIp();

    try {
        Database::execute(
            "INSERT INTO audit_log (user_id, action, entity_type, entity_id, diff_summary, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $action, $entityType, $entityId, json_encode($diffSummary), $ip]
        );
    } catch (Throwable $e) {
        error_log("[AUDIT] $action | User:$userId | $entityType:$entityId | " . json_encode($diffSummary));
    }
}

// ============================================================
// SECURITY HEADERS (call on every response)
// ============================================================

function sendSecurityHeaders(): void
{
    // Belt-and-suspenders in addition to .htaccess
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-XSS-Protection: 0'); // Modern browsers ignore; CSP is better
    }
}

<?php
/**
 * Portfolio OS — Configuration Loader
 * Loads .env file and defines application constants
 */

// ---- Load .env ----
function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

loadEnv(dirname(__DIR__) . '/.env');

// ---- Helper ----
function env(string $key, mixed $default = null): mixed {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ---- Application ----
define('APP_NAME',  env('APP_NAME',  "Mudassir's Portfolio"));
define('APP_URL',   env('APP_URL',   'http://localhost/portfolio'));
define('APP_ENV',   env('APP_ENV',   'production'));
define('APP_DEBUG', filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN));

// ---- Paths ----
define('ROOT_PATH',    dirname(__DIR__));
define('UPLOAD_PATH',  ROOT_PATH . '/uploads');
define('CV_PATH',      UPLOAD_PATH . '/cv');
define('IMG_PATH',     UPLOAD_PATH . '/projects');
define('LOG_PATH',     ROOT_PATH . '/logs');

// ---- Database ----
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'portfolio_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// ---- Session ----
define('SESSION_NAME',     env('SESSION_NAME',     'portfolio_sess'));
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 86400));

// ---- Auth ----
define('JWT_SECRET',       env('JWT_SECRET', 'change_me'));
define('ADMIN_EMAIL',      env('ADMIN_EMAIL', ''));

// ---- Mail ----
define('MAIL_HOST',       env('MAIL_HOST',       'smtp.gmail.com'));
define('MAIL_PORT',       (int) env('MAIL_PORT', 587));
define('MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls'));
define('MAIL_USERNAME',   env('MAIL_USERNAME',   ''));
define('MAIL_PASSWORD',   env('MAIL_PASSWORD',   ''));
define('MAIL_FROM',       env('MAIL_FROM',       ''));
define('MAIL_FROM_NAME',  env('MAIL_FROM_NAME',  APP_NAME));

// ---- Upload Limits ----
define('UPLOAD_MAX_CV_SIZE',  (int) env('UPLOAD_MAX_CV_SIZE',  5242880));  // 5 MB
define('UPLOAD_MAX_IMG_SIZE', (int) env('UPLOAD_MAX_IMG_SIZE', 3145728));  // 3 MB
define('CV_RETENTION_DAYS',   (int) env('CV_RETENTION_DAYS',  30));

// ---- Rate Limiting ----
define('RATE_LIMIT_LOGIN',          (int) env('RATE_LIMIT_LOGIN',          5));
define('RATE_LIMIT_LOGIN_WINDOW',   (int) env('RATE_LIMIT_LOGIN_WINDOW',   900));
define('RATE_LIMIT_CONTACT',        (int) env('RATE_LIMIT_CONTACT',        5));
define('RATE_LIMIT_CONTACT_WINDOW', (int) env('RATE_LIMIT_CONTACT_WINDOW', 3600));

// ---- Security ----
define('CSRF_TOKEN_LENGTH', (int) env('CSRF_TOKEN_LENGTH', 64));
define('LOCKOUT_THRESHOLD', (int) env('LOCKOUT_THRESHOLD', 5));
define('LOCKOUT_DURATION',  (int) env('LOCKOUT_DURATION',  900));  // 15 min

// ---- Error Handling ----
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Ensure required directories exist
foreach ([UPLOAD_PATH, CV_PATH, IMG_PATH, LOG_PATH] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

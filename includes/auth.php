<?php
/**
 * Portfolio OS — Authentication & Session Management
 * Handles login, session validation, lockout, and token management.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

// ============================================================
// SESSION INIT
// ============================================================

startSecureSession();

// ============================================================
// ADMIN AUTH CHECK — call at the top of every admin page
// ============================================================

function requireAdmin(): array
{
    if (empty($_SESSION['admin_user_id'])) {
        header('Location: ' . APP_URL . '/admin/login.php');
        exit;
    }

    // Re-validate session against DB (detects revoked sessions)
    $user = Database::selectOne(
        "SELECT id, email, role, locked_until FROM users WHERE id = ? AND role IN ('owner','editor')",
        [$_SESSION['admin_user_id']]
    );

    if (!$user) {
        destroyAdminSession();
        header('Location: ' . APP_URL . '/admin/login.php?err=session');
        exit;
    }

    // Check lockout
    if ($user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {
        destroyAdminSession();
        header('Location: ' . APP_URL . '/admin/login.php?err=locked');
        exit;
    }

    return $user;
}

/** Same as requireAdmin() but returns false instead of redirecting — for API endpoints */
function getAdminUser(): ?array
{
    if (empty($_SESSION['admin_user_id'])) return null;

    return Database::selectOne(
        "SELECT id, email, role FROM users WHERE id = ? AND role IN ('owner','editor')",
        [$_SESSION['admin_user_id']]
    );
}

// ============================================================
// LOGIN
// ============================================================

/**
 * Attempt to authenticate an admin user.
 * @return array ['success'=>bool, 'error'=>string|null, 'user'=>array|null]
 */
function attemptLogin(string $email, string $password): array
{
    $ip = getClientIp();

    // Rate limit login endpoint
    checkRateLimit('login', RATE_LIMIT_LOGIN, RATE_LIMIT_LOGIN_WINDOW);

    $user = Database::selectOne(
        "SELECT * FROM users WHERE email = ?",
        [trim($email)]
    );

    if (!$user) {
        logSecurityEvent('failed_login', ['email' => $email, 'ip' => $ip]);
        return ['success' => false, 'error' => 'Invalid credentials'];
    }

    // Check lockout
    if ($user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {
        logSecurityEvent('lockout', ['user_id' => $user['id']]);
        return ['success' => false, 'error' => 'Account temporarily locked. Try again later.'];
    }

    if (!password_verify($password, $user['password_hash'])) {
        $attempts = (int)$user['failed_attempts'] + 1;

        if ($attempts >= LOCKOUT_THRESHOLD) {
            $lockedUntil = (new DateTime())->modify('+' . LOCKOUT_DURATION . ' seconds')
                                           ->format('Y-m-d H:i:s');
            Database::execute(
                "UPDATE users SET failed_attempts=?, locked_until=? WHERE id=?",
                [$attempts, $lockedUntil, $user['id']]
            );
            logSecurityEvent('lockout', ['user_id' => $user['id'], 'attempts' => $attempts]);
            return ['success' => false, 'error' => 'Account locked for 15 minutes.'];
        }

        Database::execute(
            "UPDATE users SET failed_attempts=? WHERE id=?",
            [$attempts, $user['id']]
        );
        logSecurityEvent('failed_login', ['user_id' => $user['id'], 'attempts' => $attempts]);
        return ['success' => false, 'error' => 'Invalid credentials'];
    }

    // Success — reset counters, update last_login
    Database::execute(
        "UPDATE users SET failed_attempts=0, locked_until=NULL, last_login=NOW() WHERE id=?",
        [$user['id']]
    );

    // Regenerate session to prevent session fixation
    session_regenerate_id(true);

    $_SESSION['admin_user_id'] = $user['id'];
    $_SESSION['admin_role']    = $user['role'];
    $_SESSION['admin_email']   = $user['email'];
    $_SESSION['login_time']    = time();

    return ['success' => true, 'user' => $user];
}

// ============================================================
// LOGOUT
// ============================================================

function destroyAdminSession(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

// ============================================================
// PASSWORD OPERATIONS
// ============================================================

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function validatePasswordStrength(string $password): ?string
{
    if (strlen($password) < 8)                          return 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Z]/', $password))              return 'Password must contain an uppercase letter.';
    if (!preg_match('/[a-z]/', $password))              return 'Password must contain a lowercase letter.';
    if (!preg_match('/[0-9]/', $password))              return 'Password must contain a digit.';
    if (!preg_match('/[^A-Za-z0-9]/', $password))      return 'Password must contain a special character.';
    return null;
}

// ============================================================
// PASSWORD RESET
// ============================================================

function createPasswordResetToken(string $email): ?string
{
    $user = Database::selectOne("SELECT id FROM users WHERE email=?", [$email]);
    if (!$user) return null; // Silent — don't reveal email existence

    $token     = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiry    = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');

    // Invalidate old tokens
    Database::execute("DELETE FROM password_resets WHERE user_id=?", [$user['id']]);

    Database::execute(
        "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?,?,?)",
        [$user['id'], $tokenHash, $expiry]
    );

    return $token; // Return raw token to send via email
}

function validateResetToken(string $token): ?array
{
    $tokenHash = hash('sha256', $token);
    return Database::selectOne(
        "SELECT pr.*, u.email FROM password_resets pr
         JOIN users u ON u.id = pr.user_id
         WHERE pr.token_hash=? AND pr.used=0 AND pr.expires_at > NOW()",
        [$tokenHash]
    );
}

function consumeResetToken(string $token, string $newPassword): bool
{
    $record = validateResetToken($token);
    if (!$record) return false;

    $hash = hashPassword($newPassword);
    Database::beginTransaction();
    try {
        Database::execute("UPDATE users SET password_hash=?, failed_attempts=0, locked_until=NULL WHERE id=?",
            [$hash, $record['user_id']]);
        Database::execute("UPDATE password_resets SET used=1 WHERE token_hash=?",
            [hash('sha256', $token)]);
        Database::commit();
        return true;
    } catch (Throwable $e) {
        Database::rollback();
        error_log('[AUTH] Reset failed: ' . $e->getMessage());
        return false;
    }
}

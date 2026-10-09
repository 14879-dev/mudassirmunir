<?php
/**
 * Portfolio OS — PDO Database Connection (Singleton)
 * All queries MUST use prepared statements — no string concatenation.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): ?PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST, DB_PORT, DB_NAME
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,  // true parameterized queries
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Log DB error
                error_log('[DB] Connection failed: ' . $e->getMessage());
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    return null;
                }
                http_response_code(500);
                exit(json_encode(['error' => 'Service temporarily unavailable']));
            }
        }

        return self::$instance;
    }

    /** Run a SELECT and return all rows */
    public static function select(string $sql, array $params = []): array
    {
        $pdo = self::getInstance();
        if (!$pdo) return [];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Run a SELECT and return first row only */
    public static function selectOne(string $sql, array $params = []): ?array
    {
        $pdo = self::getInstance();
        if (!$pdo) return null;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** Run INSERT/UPDATE/DELETE and return affected rows */
    public static function execute(string $sql, array $params = []): int
    {
        $pdo = self::getInstance();
        if (!$pdo) return 0;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Run INSERT and return last inserted ID */
    public static function insert(string $sql, array $params = []): int|string
    {
        $pdo = self::getInstance();
        if (!$pdo) return 0;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $pdo->lastInsertId();
    }

    public static function beginTransaction(): void { if (self::getInstance()) self::getInstance()->beginTransaction(); }
    public static function commit(): void           { if (self::getInstance()) self::getInstance()->commit(); }
    public static function rollback(): void         { if (self::getInstance()) self::getInstance()->rollBack(); }

    /** Ensures the certificates table exists automatically */
    public static function ensureCertificatesTable(): void
    {
        try {
            $pdo = self::getInstance();
            if (!$pdo) return;
            $pdo->exec("CREATE TABLE IF NOT EXISTS `certificates` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `issuer` varchar(255) NOT NULL,
                `issue_date` varchar(50) DEFAULT NULL,
                `credential_id` varchar(100) DEFAULT NULL,
                `credential_url` varchar(500) DEFAULT NULL,
                `image` varchar(255) DEFAULT NULL,
                `category` enum('certificate','award','badge') NOT NULL DEFAULT 'certificate',
                `description` text DEFAULT NULL,
                `sort_order` int(11) NOT NULL DEFAULT 0,
                `is_published` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $e) {
            // Ignore if already exists or permissions
        }
    }
}

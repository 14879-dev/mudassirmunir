-- ============================================================
-- Portfolio OS — MySQL Database Schema
-- Run: mysql -u root portfolio_db < database/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS portfolio_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE portfolio_db;

-- ---- Users (single-admin v1, RBAC-ready) ----
CREATE TABLE IF NOT EXISTS users (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email            VARCHAR(255) NOT NULL UNIQUE,
    password_hash    VARCHAR(255) NOT NULL,
    role             ENUM('owner','editor') NOT NULL DEFAULT 'owner',
    totp_secret      VARCHAR(64)  NULL,
    totp_enabled     TINYINT(1)   NOT NULL DEFAULT 0,
    failed_attempts  INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until     DATETIME     NULL,
    last_login       DATETIME     NULL,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Password Reset Tokens ----
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME     NOT NULL,
    used       TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Hero / Landing Content ----
CREATE TABLE IF NOT EXISTS hero_content (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)  NOT NULL DEFAULT 'Mudassir',
    title         VARCHAR(255)  NOT NULL DEFAULT 'Software Engineering Student & Developer',
    taglines      JSON          NULL,
    profile_photo VARCHAR(500)  NULL,
    cta_primary   VARCHAR(100)  NOT NULL DEFAULT 'View Projects',
    cta_secondary VARCHAR(100)  NOT NULL DEFAULT 'Contact Me',
    updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- About / Bio Content ----
CREATE TABLE IF NOT EXISTS about_content (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bio                   TEXT         NULL,
    education_institution VARCHAR(255) NULL,
    education_degree      VARCHAR(255) NULL,
    education_years       VARCHAR(50)  NULL,
    timeline_items        JSON         NULL COMMENT 'Array of {year, event, description}',
    profile_photo         VARCHAR(500) NULL,
    updated_at            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Skills ----
CREATE TABLE IF NOT EXISTS skills (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    category     VARCHAR(100) NOT NULL DEFAULT 'General',
    proficiency  TINYINT UNSIGNED NOT NULL DEFAULT 50 COMMENT '0-100',
    icon         VARCHAR(255) NULL COMMENT 'DevIcon class or emoji',
    sort_order   INT UNSIGNED NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Languages (spoken + programming) ----
CREATE TABLE IF NOT EXISTS languages (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(100)  NOT NULL,
    lang_type         ENUM('spoken','programming') NOT NULL DEFAULT 'spoken',
    proficiency_level ENUM('Beginner','Elementary','Intermediate','Upper-Intermediate','Advanced','Proficient','Native') NOT NULL DEFAULT 'Intermediate',
    sort_order        INT UNSIGNED NOT NULL DEFAULT 0,
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Projects ----
CREATE TABLE IF NOT EXISTS projects (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title             VARCHAR(255)  NOT NULL,
    slug              VARCHAR(255)  NOT NULL UNIQUE,
    short_description TEXT          NULL,
    full_description  LONGTEXT      NULL,
    thumbnail         VARCHAR(500)  NULL,
    tech_stack        JSON          NULL COMMENT 'Array of tech names',
    tags              JSON          NULL COMMENT 'Array of tag strings for filtering',
    role              VARCHAR(255)  NULL,
    github_url        VARCHAR(1000) NULL,
    demo_url          VARCHAR(1000) NULL,
    store_url         VARCHAR(1000) NULL,
    is_published      TINYINT(1)   NOT NULL DEFAULT 1,
    sort_order        INT UNSIGNED NOT NULL DEFAULT 0,
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Project Images ----
CREATE TABLE IF NOT EXISTS project_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    filename   VARCHAR(500) NOT NULL,
    alt_text   VARCHAR(255) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- CV Files ----
CREATE TABLE IF NOT EXISTS cv_files (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename      VARCHAR(500) NOT NULL COMMENT 'Randomized server filename',
    original_name VARCHAR(500) NOT NULL COMMENT 'Original uploaded name',
    file_size     INT UNSIGNED NOT NULL DEFAULT 0,
    is_current    TINYINT(1)   NOT NULL DEFAULT 0,
    uploaded_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    uploaded_by   INT UNSIGNED NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Contact Messages ----
CREATE TABLE IF NOT EXISTS messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    subject    VARCHAR(500) NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    ip_address VARCHAR(45)  NULL,
    user_agent VARCHAR(500) NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Audit Log ----
CREATE TABLE IF NOT EXISTS audit_log (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NULL,
    action       VARCHAR(100) NOT NULL COMMENT 'e.g. project.create, skill.delete',
    entity_type  VARCHAR(100) NULL,
    entity_id    INT UNSIGNED NULL,
    diff_summary JSON         NULL COMMENT 'Before/after snapshot',
    ip_address   VARCHAR(45)  NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_action    (action),
    INDEX idx_created   (created_at),
    INDEX idx_user      (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Security Event Log ----
CREATE TABLE IF NOT EXISTS security_log (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type ENUM(
        'failed_login','lockout','logout',
        'file_rejected','csrf_fail','rate_limit',
        'unauthorized','totp_fail','reset_request'
    ) NOT NULL,
    ip_address VARCHAR(45)  NULL,
    user_agent VARCHAR(500) NULL,
    details    JSON         NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event   (event_type),
    INDEX idx_ip      (ip_address),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Rate Limit Tracking ----
CREATE TABLE IF NOT EXISTS rate_limits (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address   VARCHAR(45)  NOT NULL,
    endpoint     VARCHAR(100) NOT NULL,
    attempts     INT UNSIGNED NOT NULL DEFAULT 1,
    window_start DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ip_endpoint (ip_address, endpoint),
    INDEX idx_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Blogs ----
CREATE TABLE IF NOT EXISTS blogs (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    slug         VARCHAR(255) NOT NULL UNIQUE,
    excerpt      TEXT         NULL,
    content      LONGTEXT     NULL,
    cover_image  VARCHAR(500) NULL,
    is_published TINYINT(1)   NOT NULL DEFAULT 1,
    views        INT UNSIGNED NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_published (is_published),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

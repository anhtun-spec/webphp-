-- ============================================================
-- TMU Document Sharing Website - Database Schema
-- MySQL 8.0 / MariaDB 10.6+
-- Encoding: utf8mb4
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS download_history;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS document_types;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS faculties;
DROP TABLE IF EXISTS universities;
DROP TABLE IF EXISTS users;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE users (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username            VARCHAR(50) NOT NULL UNIQUE,
    email               VARCHAR(255) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    full_name           VARCHAR(150) NOT NULL,
    avatar              VARCHAR(500) DEFAULT NULL,
    student_id          VARCHAR(20) DEFAULT NULL,
    bio                 TEXT DEFAULT NULL,
    status              ENUM('active','locked','pending') NOT NULL DEFAULT 'active',
    role                ENUM('admin', 'moderator', 'member') NOT NULL DEFAULT 'member',
    email_verified_at   DATETIME DEFAULT NULL,
    reset_token         VARCHAR(100) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    remember_token      VARCHAR(255) DEFAULT NULL,
    last_login          DATETIME DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_student_id (student_id),
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- UNIVERSITIES
-- ============================================================
CREATE TABLE universities (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    short_name  VARCHAR(50) DEFAULT NULL,
    slug        VARCHAR(255) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FACULTIES (Khoa/Viện)
-- ============================================================
CREATE TABLE faculties (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    university_id INT UNSIGNED NOT NULL,
    name          VARCHAR(255) NOT NULL,
    short_name    VARCHAR(50) DEFAULT NULL,
    slug          VARCHAR(255) NOT NULL,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    sort_order    INT NOT NULL DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_faculty_slug (university_id, slug),
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE RESTRICT,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SUBJECTS (Môn học)
-- ============================================================
CREATE TABLE subjects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faculty_id  INT UNSIGNED NOT NULL,
    name        VARCHAR(255) NOT NULL,
    code        VARCHAR(20) DEFAULT NULL COMMENT 'Mã học phần',
    slug        VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_subject_slug (faculty_id, slug),
    FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE RESTRICT,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DOCUMENT_TYPES (Loại tài liệu)
-- ============================================================
CREATE TABLE document_types (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    slug       VARCHAR(100) NOT NULL UNIQUE,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DOCUMENTS
-- ============================================================
CREATE TABLE documents (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    subject_id       INT UNSIGNED NOT NULL,
    document_type_id INT UNSIGNED NOT NULL,
    title            VARCHAR(500) NOT NULL,
    slug             VARCHAR(600) NOT NULL,
    description      TEXT DEFAULT NULL,

    -- File info
    file_path        VARCHAR(600) NOT NULL COMMENT 'Path relative to storage/',
    original_filename VARCHAR(500) NOT NULL,
    mime_type        VARCHAR(100) NOT NULL,
    file_size        BIGINT UNSIGNED NOT NULL DEFAULT 0,
    checksum         VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 for duplicate detection',

    -- Metadata
    language         VARCHAR(10) NOT NULL DEFAULT 'vi',

    -- Status
    status           ENUM('draft','pending','approved','rejected','hidden','archived') NOT NULL DEFAULT 'pending',
    rejection_reason TEXT DEFAULT NULL,
    approved_by      INT UNSIGNED DEFAULT NULL,
    approved_at      DATETIME DEFAULT NULL,

    -- Stats
    view_count       INT UNSIGNED NOT NULL DEFAULT 0,
    download_count   INT UNSIGNED NOT NULL DEFAULT 0,

    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at       DATETIME DEFAULT NULL,

    FOREIGN KEY (user_id)          REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (subject_id)       REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (document_type_id) REFERENCES document_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by)      REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_subject (subject_id),
    INDEX idx_type (document_type_id),
    INDEX idx_created (created_at),
    INDEX idx_downloads (download_count),
    INDEX idx_deleted (deleted_at),
    FULLTEXT INDEX ft_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- COMMENTS
-- ============================================================
CREATE TABLE comments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    parent_id   INT UNSIGNED DEFAULT NULL COMMENT 'For threaded comments',
    content     TEXT NOT NULL,
    is_deleted   TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id)   REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_document (document_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FAVORITES
-- ============================================================
CREATE TABLE favorites (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    document_id INT UNSIGNED NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_doc (user_id, document_id),
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DOWNLOAD_HISTORY
-- ============================================================
CREATE TABLE download_history (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED DEFAULT NULL,
    document_id   INT UNSIGNED NOT NULL,
    ip_address    VARCHAR(45) DEFAULT NULL,
    user_agent    VARCHAR(500) DEFAULT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_document (document_id),
    INDEX idx_time (downloaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    type       VARCHAR(50) NOT NULL COMMENT 'approved|rejected|comment',
    title      VARCHAR(255) NOT NULL,
    message    TEXT NOT NULL,
    link       VARCHAR(500) DEFAULT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ============================================================
-- LOGIN_ATTEMPTS (Chống dò mật khẩu theo IP + Email)
-- ============================================================
CREATE TABLE login_attempts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(255) NOT NULL,
    ip_address   VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_ip_time (email, ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 1;

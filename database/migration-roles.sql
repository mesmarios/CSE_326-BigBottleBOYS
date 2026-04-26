-- Migration: Expand roles system and add LMS access table
-- Run this against the `bigbrothers` database once.

USE bigbrothers;

-- 1. Expand the role ENUM (old 'user' rows become 'candidate')
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin','hr','evaluator','candidate','ee_hired') NOT NULL DEFAULT 'candidate';

UPDATE users SET role = 'candidate' WHERE role = 'user';

-- 2. LMS access tracking table
CREATE TABLE IF NOT EXISTS lms_access (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    course_id     INT NOT NULL,
    status        ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
    moodle_user_id INT NULL,
    granted_at    TIMESTAMP NULL,
    revoked_at    TIMESTAMP NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_course (user_id, course_id),
    CONSTRAINT fk_lms_access_user   FOREIGN KEY (user_id)   REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_lms_access_course FOREIGN KEY (course_id) REFERENCES courses(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Sync settings for the enrollment module
INSERT IGNORE INTO system_settings (setting_key, setting_value, description)
VALUES
    ('lms_auto_sync_enabled', '0',  'Αυτόματος συγχρονισμός Moodle (0=off, 1=on)'),
    ('lms_last_sync_at',      '',   'Τελευταίος συγχρονισμός Moodle (ISO timestamp)'),
    ('lms_last_sync_log',     '',   'Log τελευταίου συγχρονισμού Moodle');

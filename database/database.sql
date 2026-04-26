-- ============================================================================
-- SPECIALIST MANAGEMENT SYSTEM — DATABASE
-- Special Scientists Management System (ΕΕ) — TEPAK
-- Roles: admin | hr | evaluator | candidate | ee_hired
--
-- HOW TO USE:
--   1. Open phpMyAdmin → drop bigbrothers if it exists
--   2. Run this file  → creates DB + all tables
--   3. Run seed.sql   → inserts test data
-- ============================================================================

CREATE DATABASE IF NOT EXISTS bigbrothers
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE bigbrothers;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS enrollment_logs;
DROP TABLE IF EXISTS sync_schedules;
DROP TABLE IF EXISTS lms_access;
DROP TABLE IF EXISTS specialist_enrollments;
DROP TABLE IF EXISTS lms_users;
DROP TABLE IF EXISTS lms_connections;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS application_responses;
DROP TABLE IF EXISTS application_form_fields;
DROP TABLE IF EXISTS candidate_applications;
DROP TABLE IF EXISTS application_evaluators;
DROP TABLE IF EXISTS job_announcements;
DROP TABLE IF EXISTS recruitment_periods;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS schools;
DROP TABLE IF EXISTS themes;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. USERS
--   admin     → Admin Module + Enrollment Module
--   hr        → Recruitment Module + Enrollment Module
--   evaluator → Recruitment Module (αξιολόγηση μόνο)
--   candidate → Recruitment Module (υποβολή αιτήσεων)
--   ee_hired  → Enrollment Module (δική τους LMS πρόσβαση μόνο)
-- ============================================================================
CREATE TABLE users (
    id               INT           PRIMARY KEY AUTO_INCREMENT,
    username         VARCHAR(100)  NOT NULL UNIQUE,
    email            VARCHAR(255)  NOT NULL UNIQUE,
    password_hash    VARCHAR(255)  NOT NULL,
    first_name       VARCHAR(100)  NOT NULL,
    last_name        VARCHAR(100)  NOT NULL,
    phone            VARCHAR(20)   NULL,
    address          TEXT          NULL,
    dob              DATE          NULL,
    degree           VARCHAR(100)  NULL,
    institution      VARCHAR(200)  NULL,
    specialization   VARCHAR(200)  NULL,
    experience       INT           NULL,
    summary          TEXT          NULL,
    profilepic       MEDIUMBLOB    NULL,
    profilepic_mime  VARCHAR(100)  NULL,
    role             ENUM('admin','hr','evaluator','candidate','ee_hired') NOT NULL DEFAULT 'candidate',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. SCHOOLS
-- ============================================================================
CREATE TABLE schools (
    id          INT           PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255)  NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. DEPARTMENTS
-- ============================================================================
CREATE TABLE departments (
    id          INT           PRIMARY KEY AUTO_INCREMENT,
    school_id   INT           NOT NULL,
    name        VARCHAR(255)  NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. COURSES
-- ============================================================================
CREATE TABLE courses (
    id            INT          PRIMARY KEY AUTO_INCREMENT,
    department_id INT          NOT NULL,
    code          VARCHAR(50)  NOT NULL UNIQUE,
    name          VARCHAR(255) NOT NULL,
    description   TEXT,
    credits       INT,
    semester      INT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. RECRUITMENT PERIODS
-- ============================================================================
CREATE TABLE recruitment_periods (
    id          INT           PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255)  NOT NULL,
    start_date  DATE          NOT NULL,
    end_date    DATE          NOT NULL,
    status      ENUM('planning','active','closed','archived') DEFAULT 'planning',
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. JOB ANNOUNCEMENTS
-- ============================================================================
CREATE TABLE job_announcements (
    id                  INT           PRIMARY KEY AUTO_INCREMENT,
    period_id           INT           NOT NULL,
    school_id           INT           NOT NULL,
    department_id       INT           NOT NULL,
    course_id           INT           NOT NULL,
    title               VARCHAR(255)  NOT NULL,
    description         TEXT,
    requirements        TEXT,
    number_of_positions INT           DEFAULT 1,
    status              ENUM('draft','published','closed','cancelled') DEFAULT 'draft',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (period_id)     REFERENCES recruitment_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id)     REFERENCES schools(id)             ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id)         ON DELETE CASCADE,
    FOREIGN KEY (course_id)     REFERENCES courses(id)             ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. APPLICATION EVALUATORS
-- ============================================================================
CREATE TABLE application_evaluators (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    announcement_id INT NOT NULL,
    evaluator_id    INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (announcement_id) REFERENCES job_announcements(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id)    REFERENCES users(id)             ON DELETE CASCADE,
    UNIQUE KEY uq_evaluator_announcement (announcement_id, evaluator_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. CANDIDATE APPLICATIONS
-- ============================================================================
CREATE TABLE candidate_applications (
    id                  INT  PRIMARY KEY AUTO_INCREMENT,
    announcement_id     INT  NOT NULL,
    candidate_id        INT  NOT NULL,
    status              ENUM('draft','submitted','under_review','accepted','rejected','withdrawn') DEFAULT 'draft',
    progress            INT  DEFAULT 0,
    submitted_at        DATETIME NULL,
    reviewed_at         DATETIME NULL,
    reviewed_by         INT  NULL,
    feedback            TEXT,
    app_phone           VARCHAR(20)  NULL,
    app_degree          VARCHAR(100) NULL,
    app_institution     VARCHAR(200) NULL,
    app_specialization  VARCHAR(200) NULL,
    app_experience      INT          NULL,
    app_summary         TEXT         NULL,
    app_declared        TINYINT(1)   DEFAULT 0,
    app_cv_path         VARCHAR(500) NULL,
    app_cl_path         VARCHAR(500) NULL,
    app_sup_path_1      VARCHAR(500) NULL,
    app_sup_path_2      VARCHAR(500) NULL,
    app_sup_path_3      VARCHAR(500) NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (announcement_id) REFERENCES job_announcements(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id)    REFERENCES users(id)             ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by)     REFERENCES users(id)             ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. APPLICATION FORM FIELDS
-- ============================================================================
CREATE TABLE application_form_fields (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    announcement_id INT NOT NULL,
    field_name      VARCHAR(255) NOT NULL,
    field_label     VARCHAR(255) NOT NULL,
    field_type      ENUM('text','email','phone','textarea','date','file','select','checkbox') DEFAULT 'text',
    field_options   JSON,
    is_required     TINYINT(1) DEFAULT 1,
    order_number    INT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (announcement_id) REFERENCES job_announcements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. APPLICATION RESPONSES
-- ============================================================================
CREATE TABLE application_responses (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    application_id  INT NOT NULL,
    field_id        INT NOT NULL,
    response_value  LONGTEXT,
    file_path       VARCHAR(500),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES candidate_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id)       REFERENCES application_form_fields(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. SYSTEM SETTINGS
-- ============================================================================
CREATE TABLE system_settings (
    id            INT           PRIMARY KEY AUTO_INCREMENT,
    setting_key   VARCHAR(255)  NOT NULL UNIQUE,
    setting_value LONGTEXT,
    description   TEXT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. THEMES
-- ============================================================================
CREATE TABLE themes (
    id              INT          PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(100) NOT NULL UNIQUE,
    logo_url        VARCHAR(500),
    primary_color   VARCHAR(7),
    secondary_color VARCHAR(7),
    description     TEXT,
    is_active       TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. LMS CONNECTIONS  (Moodle server configuration)
-- ============================================================================
CREATE TABLE lms_connections (
    id          INT          PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    api_url     VARCHAR(500) NOT NULL,
    api_key     VARCHAR(500) NOT NULL,
    api_secret  VARCHAR(500),
    status      ENUM('active','inactive','testing') DEFAULT 'inactive',
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 14. LMS ACCESS  (per-user, per-course Moodle access — Enrollment Module)
-- ============================================================================
CREATE TABLE lms_access (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    user_id        INT NOT NULL,
    course_id      INT NOT NULL,
    status         ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
    moodle_user_id INT NULL,
    granted_at     TIMESTAMP NULL,
    revoked_at     TIMESTAMP NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_course (user_id, course_id),
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 15. SPECIALIST ENROLLMENTS  (detailed Moodle enrollment with LMS course ID)
-- ============================================================================
CREATE TABLE specialist_enrollments (
    id             INT          PRIMARY KEY AUTO_INCREMENT,
    user_id        INT          NOT NULL,
    course_id      INT          NOT NULL,
    lms_course_id  VARCHAR(255) NULL,
    access_status  ENUM('active','inactive','suspended','pending') DEFAULT 'pending',
    enrolled_at    DATETIME     NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 16. SYNC SCHEDULES
-- ============================================================================
CREATE TABLE sync_schedules (
    id                INT          PRIMARY KEY AUTO_INCREMENT,
    name              VARCHAR(100) NOT NULL,
    sync_type         ENUM('auto_sync','full_sync') DEFAULT 'auto_sync',
    is_enabled        TINYINT(1)   DEFAULT 0,
    frequency_minutes INT          DEFAULT 60,
    last_sync_at      DATETIME     NULL,
    next_sync_at      DATETIME     NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 17. ENROLLMENT LOGS
-- ============================================================================
CREATE TABLE enrollment_logs (
    id            INT          PRIMARY KEY AUTO_INCREMENT,
    user_id       INT          NOT NULL,
    action        VARCHAR(100) NOT NULL,
    action_type   ENUM('manual_enroll','manual_unenroll','auto_sync','full_sync','status_check','error') DEFAULT 'manual_enroll',
    target_id     INT          NULL,
    target_type   VARCHAR(50)  NULL,
    details       JSON         NULL,
    status        ENUM('success','failed','pending','partial') DEFAULT 'pending',
    error_message TEXT         NULL,
    performed_by  INT          NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)      REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 18. NOTIFICATIONS
-- ============================================================================
CREATE TABLE notifications (
    id                  INT          PRIMARY KEY AUTO_INCREMENT,
    user_id             INT          NOT NULL,
    title               VARCHAR(255) NOT NULL,
    message             TEXT,
    notification_type   VARCHAR(50),
    related_entity_id   INT          NULL,
    related_entity_type VARCHAR(50)  NULL,
    is_read             TINYINT(1)   DEFAULT 0,
    read_at             DATETIME     NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 19. AUDIT LOGS
-- ============================================================================
CREATE TABLE audit_logs (
    id          INT          PRIMARY KEY AUTO_INCREMENT,
    user_id     INT          NULL,
    action      VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id   INT          NULL,
    changes     JSON         NULL,
    ip_address  VARCHAR(45)  NULL,
    user_agent  TEXT         NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INDEXES
-- ============================================================================
CREATE INDEX idx_users_email       ON users(email);
CREATE INDEX idx_users_role        ON users(role);

CREATE INDEX idx_job_ann_period    ON job_announcements(period_id);
CREATE INDEX idx_job_ann_status    ON job_announcements(status);

CREATE INDEX idx_apps_announcement ON candidate_applications(announcement_id);
CREATE INDEX idx_apps_candidate    ON candidate_applications(candidate_id);
CREATE INDEX idx_apps_status       ON candidate_applications(status);

CREATE INDEX idx_lms_access_user   ON lms_access(user_id);
CREATE INDEX idx_lms_access_status ON lms_access(status);

CREATE INDEX idx_se_user           ON specialist_enrollments(user_id);
CREATE INDEX idx_se_course         ON specialist_enrollments(course_id);

CREATE INDEX idx_notif_user        ON notifications(user_id);
CREATE INDEX idx_notif_read        ON notifications(is_read);

CREATE INDEX idx_audit_user        ON audit_logs(user_id);
CREATE INDEX idx_audit_created     ON audit_logs(created_at);

CREATE INDEX idx_enroll_log_user   ON enrollment_logs(user_id);

-- ============================================================================
-- END OF DATABASE
-- ============================================================================

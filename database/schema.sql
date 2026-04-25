CREATE DATABASE IF NOT EXISTS bigbrothers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bigbrothers;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS sync_schedules;
DROP TABLE IF EXISTS enrollment_logs;
DROP TABLE IF EXISTS specialist_enrollments;
DROP TABLE IF EXISTS lms_users;
DROP TABLE IF EXISTS lms_connections;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS themes;
DROP TABLE IF EXISTS application_responses;
DROP TABLE IF EXISTS application_form_fields;
DROP TABLE IF EXISTS candidate_applications;
DROP TABLE IF EXISTS application_evaluators;
DROP TABLE IF EXISTS job_announcements;
DROP TABLE IF EXISTS recruitment_periods;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS schools;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    dob DATE NULL,
    degree VARCHAR(100) NULL,
    institution VARCHAR(200) NULL,
    specialization VARCHAR(200) NULL,
    experience INT NULL,
    summary TEXT NULL,
    profilepic MEDIUMBLOB NULL,
    profilepic_mime VARCHAR(100) NULL,
    role ENUM('admin', 'hr_manager', 'evaluator', 'candidate', 'specialist') NOT NULL DEFAULT 'candidate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_departments_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    credits INT NULL,
    semester INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_courses_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE recruitment_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('planning', 'active', 'closed', 'archived') NOT NULL DEFAULT 'planning',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_id INT NOT NULL,
    school_id INT NOT NULL,
    department_id INT NOT NULL,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    requirements TEXT NULL,
    number_of_positions INT NOT NULL DEFAULT 1,
    status ENUM('draft', 'published', 'closed', 'cancelled') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_job_announcements_period FOREIGN KEY (period_id) REFERENCES recruitment_periods(id) ON DELETE CASCADE,
    CONSTRAINT fk_job_announcements_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    CONSTRAINT fk_job_announcements_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    CONSTRAINT fk_job_announcements_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE application_evaluators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    announcement_id INT NOT NULL,
    evaluator_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_application_evaluators_announcement FOREIGN KEY (announcement_id) REFERENCES job_announcements(id) ON DELETE CASCADE,
    CONSTRAINT fk_application_evaluators_user FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_evaluator_per_announcement (announcement_id, evaluator_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE candidate_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    announcement_id INT NOT NULL,
    candidate_id INT NOT NULL,
    status ENUM('draft', 'submitted', 'under_review', 'accepted', 'rejected', 'withdrawn') NOT NULL DEFAULT 'draft',
    progress INT NOT NULL DEFAULT 0,
    submitted_at DATETIME NULL,
    reviewed_at DATETIME NULL,
    reviewed_by INT NULL,
    feedback TEXT NULL,
    app_phone VARCHAR(20) NULL,
    app_degree VARCHAR(100) NULL,
    app_institution VARCHAR(200) NULL,
    app_specialization VARCHAR(200) NULL,
    app_experience INT NULL,
    app_summary TEXT NULL,
    app_declared TINYINT(1) NOT NULL DEFAULT 0,
    app_cv_path VARCHAR(500) NULL,
    app_cl_path VARCHAR(500) NULL,
    app_sup_path_1 VARCHAR(500) NULL,
    app_sup_path_2 VARCHAR(500) NULL,
    app_sup_path_3 VARCHAR(500) NULL,
    app_sup_path_4 VARCHAR(500) NULL,
    app_sup_path_5 VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_candidate_applications_announcement FOREIGN KEY (announcement_id) REFERENCES job_announcements(id) ON DELETE CASCADE,
    CONSTRAINT fk_candidate_applications_candidate FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_candidate_applications_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE application_form_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    announcement_id INT NOT NULL,
    field_name VARCHAR(255) NOT NULL,
    field_label VARCHAR(255) NOT NULL,
    field_type ENUM('text', 'email', 'phone', 'textarea', 'date', 'file', 'select', 'checkbox') NOT NULL DEFAULT 'text',
    field_options JSON NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    order_number INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_application_form_fields_announcement FOREIGN KEY (announcement_id) REFERENCES job_announcements(id) ON DELETE CASCADE,
    UNIQUE KEY unique_field_per_announcement (announcement_id, field_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE application_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    field_id INT NOT NULL,
    response_value LONGTEXT NULL,
    file_path VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_application_responses_application FOREIGN KEY (application_id) REFERENCES candidate_applications(id) ON DELETE CASCADE,
    CONSTRAINT fk_application_responses_field FOREIGN KEY (field_id) REFERENCES application_form_fields(id) ON DELETE CASCADE,
    UNIQUE KEY unique_response_per_field (application_id, field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    logo_url VARCHAR(500) NULL,
    primary_color VARCHAR(7) NULL,
    secondary_color VARCHAR(7) NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value LONGTEXT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lms_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    api_url VARCHAR(500) NOT NULL,
    api_key VARCHAR(500) NOT NULL,
    api_secret VARCHAR(500) NULL,
    status ENUM('active', 'inactive', 'testing') NOT NULL DEFAULT 'inactive',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lms_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lms_connection_id INT NOT NULL,
    lms_user_id VARCHAR(255) NOT NULL,
    lms_username VARCHAR(255) NULL,
    access_status ENUM('active', 'inactive', 'pending', 'disabled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_lms_users_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_lms_users_connection FOREIGN KEY (lms_connection_id) REFERENCES lms_connections(id) ON DELETE CASCADE,
    UNIQUE KEY unique_lms_user_connection (user_id, lms_connection_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE specialist_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    lms_course_id VARCHAR(255) NULL,
    access_status ENUM('active', 'inactive', 'suspended', 'pending') NOT NULL DEFAULT 'pending',
    enrolled_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_specialist_enrollments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_specialist_enrollments_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_specialist_course (user_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enrollment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    action_type ENUM('manual_enroll', 'manual_unenroll', 'auto_sync', 'full_sync', 'status_check', 'error') NOT NULL DEFAULT 'manual_enroll',
    target_id INT NULL,
    target_type VARCHAR(50) NULL,
    details JSON NULL,
    status ENUM('success', 'failed', 'pending', 'partial') NOT NULL DEFAULT 'pending',
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    performed_by INT NULL,
    CONSTRAINT fk_enrollment_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollment_logs_performed_by FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sync_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sync_type ENUM('auto_sync', 'full_sync') NOT NULL DEFAULT 'auto_sync',
    is_enabled TINYINT(1) NOT NULL DEFAULT 0,
    frequency_minutes INT NOT NULL DEFAULT 60,
    last_sync_at DATETIME NULL,
    next_sync_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_sync_type (sync_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NULL,
    notification_type VARCHAR(50) NULL,
    related_entity_id INT NULL,
    related_entity_type VARCHAR(50) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id INT NULL,
    changes JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_job_announcements_period ON job_announcements(period_id);
CREATE INDEX idx_job_announcements_status ON job_announcements(status);
CREATE INDEX idx_job_announcements_department ON job_announcements(department_id);
CREATE INDEX idx_candidate_applications_announcement ON candidate_applications(announcement_id);
CREATE INDEX idx_candidate_applications_candidate ON candidate_applications(candidate_id);
CREATE INDEX idx_candidate_applications_status ON candidate_applications(status);
CREATE INDEX idx_specialist_enrollments_user ON specialist_enrollments(user_id);
CREATE INDEX idx_specialist_enrollments_course ON specialist_enrollments(course_id);
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_read ON notifications(is_read);
CREATE INDEX idx_audit_logs_user ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_created ON audit_logs(created_at);


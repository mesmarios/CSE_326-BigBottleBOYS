<?php
/**
 * Database Configuration File
 * Specialist Management System - TEPAK
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP default is empty password
define('DB_NAME', 'bigbrothers');

// Establish database connection
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Application settings
define('APP_NAME', 'Σύστημα Διαχείρισης Ειδικών Επιστημόνων');
define('APP_VERSION', '1.0.0');
define('INSTITUTION_NAME', 'ΤΕΠΑΚ');

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('REMEMBER_ME_DURATION', 2592000); // 30 days in seconds

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('UPLOAD_DIR', __DIR__ . '/../../uploads/');
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Email settings (configure based on your email provider)
define('MAIL_FROM', 'noreply@tepak.cy');
define('MAIL_FROM_NAME', 'ΤΕΠΑΚ - Σύστημα Διαχείρισης');
define('SMTP_HOST', 'localhost'); // Change to your mail server
define('SMTP_PORT', 465);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// LMS/Moodle API settings
define('MOODLE_API_URL', 'http://localhost:8080/moodle/webservice/rest/server.php');
define('MOODLE_API_KEY', 'your_moodle_api_key_here');

// Security settings
define('HASH_ALGORITHM', 'bcrypt');
define('BCRYPT_COST', 10);

// User roles (must match the database)
const USER_ROLES = [
    'admin' => 1,
    'hr_manager' => 2,
    'evaluator' => 3,
    'candidate' => 4,
    'specialist' => 5,
];

// Application status constants
const APPLICATION_STATUS = [
    'draft' => 'draft',
    'submitted' => 'submitted',
    'under_review' => 'under_review',
    'accepted' => 'accepted',
    'rejected' => 'rejected',
    'withdrawn' => 'withdrawn',
];

// Recruitment status constants
const RECRUITMENT_STATUS = [
    'planning' => 'planning',
    'active' => 'active',
    'closed' => 'closed',
    'archived' => 'archived',
];

/**
 * Get database connection
 * @return PDO
 */
function getDBConnection() {
    global $pdo;
    return $pdo;
}

/**
 * Escape HTML output
 * @param string $text
 * @return string
 */
function escape($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Hash password
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
}

/**
 * Verify password
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

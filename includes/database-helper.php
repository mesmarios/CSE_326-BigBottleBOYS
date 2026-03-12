<?php
/**
 * Database Helper Library
 * Common database operations for Specialist Management System
 */

require_once __DIR__ . '/config.php';

class DatabaseHelper {
    
    /**
     * Get all users with their roles
     * @param array $filters - Filter options (status, role, etc.)
     * @param int $limit - Records limit
     * @param int $offset - Pagination offset
     * @return array
     */
    public static function getAllUsers($filters = [], $limit = 20, $offset = 0) {
        $pdo = getDBConnection();
        
        $query = "SELECT u.*, GROUP_CONCAT(r.name) as roles 
                  FROM users u 
                  LEFT JOIN user_roles ur ON u.id = ur.user_id 
                  LEFT JOIN roles r ON ur.role_id = r.id";
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "u.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['role'])) {
            $whereConditions[] = "r.name = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $query .= " GROUP BY u.id ORDER BY u.last_name ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get job announcements with related data
     * @param array $filters - Filter options
     * @param int $limit - Records limit
     * @param int $offset - Pagination offset
     * @return array
     */
    public static function getJobAnnouncements($filters = [], $limit = 20, $offset = 0) {
        $pdo = getDBConnection();
        
        $query = "SELECT 
                    ja.id, ja.title, ja.status, ja.number_of_positions, ja.created_at,
                    s.name as school, d.name as department, c.name as course,
                    COUNT(ca.id) as application_count
                  FROM job_announcements ja
                  LEFT JOIN schools s ON ja.school_id = s.id
                  LEFT JOIN departments d ON ja.department_id = d.id
                  LEFT JOIN courses c ON ja.course_id = c.id
                  LEFT JOIN candidate_applications ca ON ja.id = ca.announcement_id";
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "ja.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['period_id'])) {
            $whereConditions[] = "ja.period_id = ?";
            $params[] = $filters['period_id'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "ja.title LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $query .= " GROUP BY ja.id ORDER BY ja.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get candidate applications with evaluator feedback
     * @param int $announcement_id - Job announcement ID
     * @param array $filters - Filter options
     * @return array
     */
    public static function getCandidateApplications($announcement_id, $filters = []) {
        $pdo = getDBConnection();
        
        $query = "SELECT 
                    ca.id, ca.status, ca.progress, ca.submitted_at, ca.reviewed_at, ca.feedback,
                    u.first_name, u.last_name, u.email,
                    reviewer.first_name as reviewer_first, reviewer.last_name as reviewer_last
                  FROM candidate_applications ca
                  JOIN users u ON ca.candidate_id = u.id
                  LEFT JOIN users reviewer ON ca.reviewed_by = reviewer.id
                  WHERE ca.announcement_id = ?";
        
        $params = [$announcement_id];
        
        if (!empty($filters['status'])) {
            $query .= " AND ca.status = ?";
            $params[] = $filters['status'];
        }
        
        $query .= " ORDER BY ca.submitted_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get specialist enrollment status
     * @param int $user_id - Specialist user ID
     * @return array
     */
    public static function getSpecialistEnrollments($user_id) {
        $pdo = getDBConnection();
        
        $query = "SELECT 
                    se.id, se.access_status, se.enrolled_at,
                    c.code, c.name as course_name,
                    d.name as department_name,
                    s.name as school_name
                  FROM specialist_enrollments se
                  JOIN courses c ON se.course_id = c.id
                  JOIN departments d ON c.department_id = d.id
                  JOIN schools s ON d.school_id = s.id
                  WHERE se.user_id = ?
                  ORDER BY s.name, d.name, c.name";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get recruitment statistics
     * @return array
     */
    public static function getRecruitmentStats() {
        $pdo = getDBConnection();
        
        $stats = [];
        
        // Total announcements
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM job_announcements WHERE status = 'published'");
        $stats['total_announcements'] = $stmt->fetch()['count'];
        
        // Total applications
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM candidate_applications");
        $stats['total_applications'] = $stmt->fetch()['count'];
        
        // Applications by status
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM candidate_applications GROUP BY status");
        $stats['applications_by_status'] = $stmt->fetchAll();
        
        // Applications per announcement
        $stmt = $pdo->query("
            SELECT ja.title, COUNT(ca.id) as count 
            FROM job_announcements ja 
            LEFT JOIN candidate_applications ca ON ja.id = ca.announcement_id 
            WHERE ja.status = 'published'
            GROUP BY ja.id 
            ORDER BY count DESC
        ");
        $stats['applications_per_announcement'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Get enrollment statistics
     * @return array
     */
    public static function getEnrollmentStats() {
        $pdo = getDBConnection();
        
        $stats = [];
        
        // Total specialists
        $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as count FROM specialist_enrollments");
        $stats['total_specialists'] = $stmt->fetch()['count'];
        
        // Active enrollments
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM specialist_enrollments WHERE access_status = 'active'");
        $stats['active_enrollments'] = $stmt->fetch()['count'];
        
        // Pending enrollments
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM specialist_enrollments WHERE access_status = 'pending'");
        $stats['pending_enrollments'] = $stmt->fetch()['count'];
        
        // Enrollments by status
        $stmt = $pdo->query("SELECT access_status, COUNT(*) as count FROM specialist_enrollments GROUP BY access_status");
        $stats['enrollments_by_status'] = $stmt->fetchAll();
        
        // Recent sync logs
        $stmt = $pdo->query("
            SELECT el.*, u.first_name, u.last_name 
            FROM enrollment_logs el 
            JOIN users u ON el.user_id = u.id 
            ORDER BY el.created_at DESC 
            LIMIT 10
        ");
        $stats['recent_syncs'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Create a new application
     * @param int $announcement_id - Job announcement ID
     * @param int $candidate_id - Candidate user ID
     * @return int - Application ID
     */
    public static function createApplication($announcement_id, $candidate_id) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO candidate_applications (announcement_id, candidate_id, status, progress)
            VALUES (?, ?, 'draft', 0)
        ");
        $stmt->execute([$announcement_id, $candidate_id]);
        return $pdo->lastInsertId();
    }
    
    /**
     * Update application response
     * @param int $application_id - Application ID
     * @param int $field_id - Form field ID
     * @param string $response_value - Response value
     * @param string $file_path - Optional file path
     * @return bool
     */
    public static function updateApplicationResponse($application_id, $field_id, $response_value, $file_path = null) {
        $pdo = getDBConnection();
        
        // Check if response exists
        $stmt = $pdo->prepare("
            SELECT id FROM application_responses 
            WHERE application_id = ? AND field_id = ?
        ");
        $stmt->execute([$application_id, $field_id]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Update existing response
            $stmt = $pdo->prepare("
                UPDATE application_responses 
                SET response_value = ?, file_path = ?, updated_at = NOW()
                WHERE application_id = ? AND field_id = ?
            ");
            return $stmt->execute([$response_value, $file_path, $application_id, $field_id]);
        } else {
            // Insert new response
            $stmt = $pdo->prepare("
                INSERT INTO application_responses (application_id, field_id, response_value, file_path)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$application_id, $field_id, $response_value, $file_path]);
        }
    }
    
    /**
     * Submit application
     * @param int $application_id - Application ID
     * @return bool
     */
    public static function submitApplication($application_id) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            UPDATE candidate_applications 
            SET status = 'submitted', progress = 100, submitted_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$application_id]);
    }
    
    /**
     * Add enrollment log
     * @param int $user_id - User ID
     * @param string $action - Action description
     * @param string $action_type - Action type
     * @param array $details - Additional details
     * @param string $status - Log status
     * @param int $performed_by - Performed by user ID
     * @return bool
     */
    public static function addEnrollmentLog($user_id, $action, $action_type, $details = [], $status = 'pending', $performed_by = null) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO enrollment_logs 
            (user_id, action, action_type, details, status, performed_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $detailsJson = !empty($details) ? json_encode($details) : null;
        return $stmt->execute([$user_id, $action, $action_type, $detailsJson, $status, $performed_by]);
    }
    
    /**
     * Create notification
     * @param int $user_id - User ID
     * @param string $title - Notification title
     * @param string $message - Notification message
     * @param string $type - Notification type
     * @param int $related_id - Related entity ID
     * @param string $related_type - Related entity type
     * @return bool
     */
    public static function createNotification($user_id, $title, $message, $type = null, $related_id = null, $related_type = null) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO notifications 
            (user_id, title, message, notification_type, related_entity_id, related_entity_type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$user_id, $title, $message, $type, $related_id, $related_type]);
    }
    
    /**
     * Get unread notifications for user
     * @param int $user_id - User ID
     * @param int $limit - Records limit
     * @return array
     */
    public static function getUnreadNotifications($user_id, $limit = 10) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? AND is_read = 0
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Mark notification as read
     * @param int $notification_id - Notification ID
     * @return bool
     */
    public static function markNotificationAsRead($notification_id) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = 1, read_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$notification_id]);
    }
    
    /**
     * Add audit log
     * @param int $user_id - User ID
     * @param string $action - Action performed
     * @param string $entity_type - Entity type
     * @param int $entity_id - Entity ID
     * @param array $changes - Changes made
     * @param string $ip_address - User IP
     * @param string $user_agent - User agent
     * @return bool
     */
    public static function addAuditLog($user_id, $action, $entity_type, $entity_id = null, $changes = [], $ip_address = null, $user_agent = null) {
        $pdo = getDBConnection();
        
        if (empty($ip_address)) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        if (empty($user_agent)) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs 
            (user_id, action, entity_type, entity_id, changes, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $changesJson = !empty($changes) ? json_encode($changes) : null;
        return $stmt->execute([$user_id, $action, $entity_type, $entity_id, $changesJson, $ip_address, $user_agent]);
    }
    
    /**
     * Get system setting
     * @param string $setting_key - Setting key
     * @param string $default - Default value if not found
     * @return string
     */
    public static function getSystemSetting($setting_key, $default = null) {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$setting_key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : $default;
    }
    
    /**
     * Update system setting
     * @param string $setting_key - Setting key
     * @param string $setting_value - Setting value
     * @param string $description - Setting description
     * @return bool
     */
    public static function updateSystemSetting($setting_key, $setting_value, $description = null) {
        $pdo = getDBConnection();
        
        // Check if setting exists
        $stmt = $pdo->prepare("SELECT id FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$setting_key]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Update existing
            $stmt = $pdo->prepare("
                UPDATE system_settings 
                SET setting_value = ?, description = ?, updated_at = NOW()
                WHERE setting_key = ?
            ");
            return $stmt->execute([$setting_value, $description, $setting_key]);
        } else {
            // Insert new
            $stmt = $pdo->prepare("
                INSERT INTO system_settings (setting_key, setting_value, description)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$setting_key, $setting_value, $description]);
        }
    }
}
?>

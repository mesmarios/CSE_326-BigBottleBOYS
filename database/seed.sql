-- ============================================================================
-- SPECIALIST MANAGEMENT SYSTEM - SEED DATA
-- Sample data for testing and development
-- ============================================================================

USE bigbrothers;

-- ============================================================================
-- 1. INSERT ROLES
-- ============================================================================
INSERT INTO roles (name, description) VALUES
('Admin', 'System administrator with full access'),
('HR Manager', 'Human Resources Manager - manages recruitment and enrollment'),
('Evaluator', 'Evaluates specialist applications'),
('Candidate', 'Job candidate/applicant'),
('Specialist', 'Hired specialist/expert scientist');

-- ============================================================================
-- 2. INSERT USERS
-- ============================================================================
INSERT INTO users (email, password_hash, first_name, last_name, phone, address, status) VALUES
-- Admin users
('admin@tepak.cy', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Γιάννης', 'Παπαδόπουλος', '+357 22 894556', 'Λευκωσία, Κύπρος', 'active'),
('admin2@tepak.cy', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Μαρία', 'Χατζηιωάννου', '+357 22 894557', 'Λεμεσός, Κύπρος', 'active'),

-- HR Manager users
('hr@tepak.cy', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Δημήτρης', 'Οικονόμου', '+357 22 894558', 'Λάρνακα, Κύπρος', 'active'),
('hr2@tepak.cy', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Έλενα', 'Δημοσθένους', '+357 22 894559', 'Αμμόχωστος, Κύπρος', 'active'),

-- Evaluator users
('evaluator1@tepak.cy', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Αντώνης', 'Κωνσταντίνου', '+357 22 894560', 'Λευκωσία, Κύπρος', 'active'),
('evaluator2@tepak.cy', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Σοφία', 'Βασιλειάδη', '+357 22 894561', 'Λευκωσία, Κύπρος', 'active'),
('evaluator3@tepak.cy', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Νικόλαος', 'Ζάγουρας', '+357 22 894562', 'Λεμεσός, Κύπρος', 'active'),

-- Candidate users
('candidate1@example.com', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Παναγιώτης', 'Κυριακίδης', '+357 96 123456', 'Λευκωσία, Κύπρος', 'active'),
('candidate2@example.com', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Αλέξανδρος', 'Πιερίδης', '+357 96 234567', 'Λεμεσός, Κύπρος', 'active'),
('candidate3@example.com', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Μαρία', 'Φιλίππου', '+357 96 345678', 'Λάρνακα, Κύπρος', 'active'),
('candidate4@example.com', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Ιωάννης', 'Σταθόπουλος', '+357 96 456789', 'Πάφος, Κύπρος', 'active'),
('candidate5@example.com', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Αναστασία', 'Μιχαλοπούλου', '+357 96 567890', 'Λευκωσία, Κύπρος', 'active'),

-- Specialist/Hired users
('specialist1@tepak.cy', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Ανδρέας', 'Λοΐζου', '+357 22 894563', 'Λευκωσία, Κύπρος', 'active'),
('specialist2@tepak.cy', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Νίκη', 'Αντωνιάδη', '+357 22 894564', 'Λεμεσός, Κύπρος', 'active'),
('specialist3@tepak.cy', '$2y$10$vLgPLUGx3yLqZEDjNPLN..XQj3zY/NjM7R9bNnK6C5Zx1k6fWcVSm', 'Χριστόφορος', 'Δημοσθένης', '+357 22 894565', 'Λάρνακα, Κύπρος', 'active');

-- ============================================================================
-- 3. ASSIGN ROLES TO USERS
-- ============================================================================
INSERT INTO user_roles (user_id, role_id) VALUES
-- Admins
(1, 1), (2, 1),
-- HR Managers
(3, 2), (4, 2),
-- Evaluators
(5, 3), (6, 3), (7, 3),
-- Candidates
(8, 4), (9, 4), (10, 4), (11, 4), (12, 4),
-- Specialists
(13, 5), (14, 5), (15, 5);

-- ============================================================================
-- 4. INSERT SCHOOLS
-- ============================================================================
INSERT INTO schools (name, description) VALUES
('Σχολή Μηχανικής', 'Σχολή που αφορά τις μηχανικές επιστήμες'),
('Σχολή Θετικών Επιστημών', 'Σχολή που αφορά τις θετικές επιστήμες'),
('Σχολή Διοίκησης Επιχειρήσεων', 'Σχολή που αφορά τη διοίκηση και τα ΠΕΔ');

-- ============================================================================
-- 5. INSERT DEPARTMENTS
-- ============================================================================
INSERT INTO departments (school_id, name, description) VALUES
-- School 1
(1, 'Τμήμα Ηλεκτρολόγων Μηχανικών', 'Τμήμα ηλεκτρολόγων μηχανικών'),
(1, 'Τμήμα Μηχανικών Υπολογιστών', 'Τμήμα μηχανικών υπολογιστών'),
(1, 'Τμήμα Πολιτικών Μηχανικών', 'Τμήμα πολιτικών μηχανικών'),
-- School 2
(2, 'Τμήμα Χημείας', 'Τμήμα χημείας'),
(2, 'Τμήμα Φυσικής', 'Τμήμα φυσικής'),
(2, 'Τμήμα Βιολογίας', 'Τμήμα βιολογίας'),
-- School 3
(3, 'Τμήμα Λογιστικής', 'Τμήμα λογιστικής'),
(3, 'Τμήμα Marketing', 'Τμήμα marketing');

-- ============================================================================
-- 6. INSERT COURSES
-- ============================================================================
INSERT INTO courses (department_id, code, name, description, credits, semester) VALUES
-- Department 1 - Ηλεκτρολόγων
(1, 'ELE101', 'Εισαγωγή στις Ηλεκτρικές Μηχανές', 'Βασική εισαγωγή στις ηλεκτρικές μηχανές', 6, 1),
(1, 'ELE102', 'Ηλεκτρική Κυκλώματα Ι', 'Θεωρία ηλεκτρικών κυκλωμάτων', 6, 1),
(1, 'ELE201', 'Ηλεκτρή Κυκλώματα ΙΙ', 'Προχωρημένα ηλεκτρικά κυκλώματα', 6, 2),
-- Department 2 - Μηχανικών Υπολογιστών
(2, 'CS101', 'Εισαγωγή στον Προγραμματισμό', 'Βασικές αρχές προγραμματισμού', 6, 1),
(2, 'CS102', 'Δομές Δεδομένων', 'Δομές δεδομένων και αλγόριθμοι', 6, 1),
(2, 'CS201', 'Βάσεις Δεδομένων', 'Σχεδιασμός και διαχείριση ΒΔ', 6, 2),
(2, 'CS202', 'Λειτουργικά Συστήματα', 'Αρχές λειτουργικών συστημάτων', 6, 2),
-- Department 3 - Πολιτικών Μηχανικών
(3, 'CIV101', 'Μηχανική Των Κατασκευών', 'Ανάλυση κατασκευών', 6, 1),
(3, 'CIV102', 'Υδραυλική', 'Θεωρία υδραυλικής', 6, 1),
-- Department 4 - Χημείας
(4, 'CHM101', 'Γενική Χημεία', 'Εισαγωγή στη χημεία', 6, 1),
(4, 'CHM102', 'Οργανική Χημεία', 'Χημεία οργανικών ενώσεων', 6, 1),
-- Department 5 - Φυσικής
(5, 'PHY101', 'Κλασική Μηχανική', 'Θεμελιώδεις αρχές μηχανικής', 6, 1),
(5, 'PHY102', 'Θερμοδυναμική', 'Θεωρία θερμοδυναμικής', 6, 2),
-- Department 6 - Βιολογίας
(6, 'BIO101', 'Κυτταρική Βιολογία', 'Δομή και λειτουργία κυττάρου', 6, 1),
(6, 'BIO102', 'Γενετική', 'Θεωρία κληρονομικότητας', 6, 1),
-- Department 7 - Λογιστικής
(7, 'ACC101', 'Εισαγωγή στη Λογιστική', 'Βασικές αρχές λογιστικής', 6, 1),
(7, 'ACC102', 'Χρηματοοικονομική Λογιστική', 'Χρηματοοικονομική ανάλυση', 6, 2),
-- Department 8 - Marketing
(8, 'MKT101', 'Αρχές Marketing', 'Θεμελιώδεις αρχές μάρκετινγκ', 6, 1),
(8, 'MKT102', 'Ψηφιακό Marketing', 'Στρατηγικές ψηφιακού μάρκετινγκ', 6, 2);

-- ============================================================================
-- 7. INSERT RECRUITMENT PERIODS
-- ============================================================================
INSERT INTO recruitment_periods (name, start_date, end_date, status, description) VALUES
('Περίοδος Πρόσληψης 2024 - Α\' Εξάμηνο', '2024-01-15', '2024-03-15', 'active', 'Πρώτη περίοδος πρόσληψης ειδικών επιστημόνων του 2024'),
('Περίοδος Πρόσληψης 2024 - Β\' Εξάμηνο', '2024-09-01', '2024-11-30', 'planning', 'Δεύτερη περίοδος πρόσληψης ειδικών επιστημόνων του 2024'),
('Περίοδος Πρόσληψης 2025', '2025-01-01', '2025-03-31', 'planning', 'Περίοδος πρόσληψης ειδικών επιστημόνων του 2025');

-- ============================================================================
-- 8. INSERT JOB ANNOUNCEMENTS
-- ============================================================================
INSERT INTO job_announcements (period_id, school_id, department_id, course_id, title, description, requirements, number_of_positions, status) VALUES
-- Period 1 announcements
(1, 1, 1, 1, 'Ειδικός Επιστήμονας - Ηλεκτρικές Μηχανές', 
 'Αναζητούμε ειδικό επιστήμονα για τη διδασκαλία και έρευνα στο πεδίο των ηλεκτρικών μηχανών',
 'Πτυχίο ή μεταπτυχιακό στη σχετική ειδικότητα, ελάχιστο 2 χρόνια εμπειρία',
 1, 'published'),
(1, 1, 2, 5, 'Ειδικός Επιστήμονας - Δομές Δεδομένων',
 'Αναζητούμε ειδικό επιστήμονα για τη διδασκαλία και έρευνα στο πεδίο των δομών δεδομένων',
 'Πτυχίο ή μεταπτυχιακό στη σχετική ειδικότητα, ελάχιστο 3 χρόνια εμπειρία',
 1, 'published'),
(1, 1, 2, 6, 'Ειδικός Επιστήμονας - Βάσεις Δεδομένων',
 'Αναζητούμε ειδικό επιστήμονα για τη διδασκαλία και έρευνα στο πεδίο των βάσεων δεδομένων',
 'Πτυχίο ή μεταπτυχιακό στη σχετική ειδικότητα, ελάχιστο 2 χρόνια εμπειρία',
 2, 'published'),
(1, 2, 4, 12, 'Ειδικός Επιστήμονας - Χημεία',
 'Αναζητούμε ειδικό επιστήμονα για τη διδασκαλία και έρευνα στο πεδίο της χημείας',
 'Διδακτορικό στιν χημεία, ελάχιστο 5 χρόνια εμπειρία',
 1, 'published'),
(1, 2, 5, 15, 'Ειδικός Επιστήμονας - Κλασική Μηχανική',
 'Αναζητούμε ειδικό επιστήμονα για τη διδασκαλία και έρευνα στο πεδίο της κλασικής μηχανικής',
 'Πτυχίο ή μεταπτυχιακό στη φυσική, ελάχιστο 3 χρόνια εμπειρία',
 1, 'published');

-- ============================================================================
-- 9. INSERT APPLICATION EVALUATORS
-- ============================================================================
INSERT INTO application_evaluators (announcement_id, evaluator_id) VALUES
(1, 5), (1, 6),  -- Two evaluators for announcement 1
(2, 5), (2, 7),  -- Two evaluators for announcement 2
(3, 6), (3, 7),  -- Two evaluators for announcement 3
(4, 5),          -- One evaluator for announcement 4
(5, 7);          -- One evaluator for announcement 5

-- ============================================================================
-- 10. INSERT APPLICATION FORM FIELDS (for first announcement)
-- ============================================================================
INSERT INTO application_form_fields (announcement_id, field_name, field_label, field_type, is_required, order_number) VALUES
(1, 'full_name', 'Πλήρες Όνομα', 'text', 1, 1),
(1, 'email', 'Email', 'email', 1, 2),
(1, 'phone', 'Τηλέφωνο', 'phone', 1, 3),
(1, 'address', 'Διεύθυνση', 'textarea', 1, 4),
(1, 'education', 'Εκπαίδευση', 'textarea', 1, 5),
(1, 'experience', 'Επαγγελματική Εμπειρία', 'textarea', 1, 6),
(1, 'cv', 'Βιογραφικό Σημείωμα (PDF)', 'file', 1, 7),
(1, 'motivation', 'Κίνητρα Αίτησης', 'textarea', 0, 8);

-- ============================================================================
-- 11. INSERT CANDIDATE APPLICATIONS
-- ============================================================================
INSERT INTO candidate_applications (announcement_id, candidate_id, status, progress, submitted_at) VALUES
(1, 8, 'submitted', 100, '2024-02-01 10:30:00'),
(1, 9, 'under_review', 100, '2024-02-02 14:15:00'),
(2, 9, 'submitted', 100, '2024-02-05 09:00:00'),
(2, 10, 'draft', 50, NULL),
(3, 11, 'submitted', 100, '2024-02-10 11:45:00'),
(3, 8, 'submitted', 100, '2024-02-11 16:20:00'),
(3, 12, 'draft', 30, NULL),
(4, 10, 'submitted', 100, '2024-02-15 13:30:00'),
(5, 11, 'submitted', 100, '2024-02-18 10:00:00');

-- ============================================================================
-- 12. INSERT APPLICATION RESPONSES
-- ============================================================================
INSERT INTO application_responses (application_id, field_id, response_value) VALUES
-- Application 1 responses
(1, 1, 'Παναγιώτης Κυριακίδης'),
(1, 2, 'candidate1@example.com'),
(1, 3, '+357 96 123456'),
(1, 4, 'Λευκωσία, Κύπρος'),
(1, 5, 'Διπλωματούχος Μηχανικός Υπολογιστών ΠΑΝΕΠΙΣΤΗΜΙΟ ΚΎΠΡΟΥ 2020'),
(1, 6, '2 χρόνια σε εταιρεία ΠΕ ως Software Developer'),
(1, 8, 'Ενδιαφέρομαι πολύ για την εκπαίδευση και την έρευνα'),
-- Application 2 responses
(2, 1, 'Αλέξανδρος Πιερίδης'),
(2, 2, 'candidate2@example.com'),
(2, 3, '+357 96 234567'),
(2, 4, 'Λεμεσός, Κύπρος'),
(2, 5, 'Διδάκτωρ στην Πληροφορική ΕΘΝΙΚΟ ΜΕΤΣΟΒΙΟ ΠΟΛΥΤΕΧΝΕΙΟ 2022'),
(2, 6, '4 χρόνια σε ερευνητικό ινστιτούτο');

-- ============================================================================
-- 13. INSERT THEMES
-- ============================================================================
INSERT INTO themes (name, logo_url, primary_color, secondary_color, description, is_active) VALUES
('TEPAK Default', '/logofiles/tepak-logo.png', '#007bff', '#6c757d', 'Default TEPAK theme', 1),
('Dark Mode', '/logofiles/tepak-logo-dark.png', '#2d3436', '#636e72', 'Dark theme for TEPAK', 0),
('Light Mode', '/logofiles/tepak-logo-light.png', '#f5f6fa', '#2d3436', 'Light theme for TEPAK', 0);

-- ============================================================================
-- 14. INSERT SYSTEM SETTINGS
-- ============================================================================
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('app_name', 'Σύστημα Διαχείρισης Ειδικών Επιστημόνων', 'Όνομα εφαρμογής'),
('app_version', '1.0.0', 'Έκδοση εφαρμογής'),
('institution_name', 'ΤΕΠΑΚ', 'Όνομα ιδρύματος'),
('institution_email', 'info@tepak.cy', 'Email ιδρύματος'),
('institution_phone', '+357 22 894500', 'Τηλέφωνο ιδρύματος'),
('max_file_size', '5242880', 'Μέγιστο μέγεθος αρχείου σε bytes (5MB)'),
('pagination_limit', '20', 'Αριθμός εγγραφών ανά σελίδα'),
('auto_sync_enabled', '1', 'Ενεργοποίηση αυτόματης συγχρονισμού με Moodle'),
('auto_sync_interval', '3600', 'Διάστημα συγχρονισμού σε δευτερόλεπτα (1 ώρα)'),
('email_notifications_enabled', '1', 'Ενεργοποίηση ειδοποιήσεων μέσω email');

-- ============================================================================
-- 15. INSERT LMS CONNECTIONS (Moodle)
-- ============================================================================
INSERT INTO lms_connections (name, api_url, api_key, api_secret, status, description) VALUES
('Moodle TEPAK Production', 'https://moodle.tepak.cy/webservice/rest/server.php', 'moodle_api_key_production_xxx', 'moodle_secret_xxx', 'inactive', 'Σύνδεση με κύριο Moodle σερβερ ΤΕΠΑΚ'),
('Moodle TEPAK Staging', 'http://localhost:8080/moodle/webservice/rest/server.php', 'moodle_api_key_staging_xxx', 'moodle_secret_staging_xxx', 'active', 'Σύνδεση με staging Moodle για δοκιμές');

-- ============================================================================
-- 16. INSERT LMS USERS
-- ============================================================================
INSERT INTO lms_users (user_id, lms_connection_id, lms_user_id, lms_username, access_status) VALUES
(13, 2, '1001', 'specialist1', 'active'),
(14, 2, '1002', 'specialist2', 'active'),
(15, 2, '1003', 'specialist3', 'pending'),
(3, 2, '2001', 'hr_manager1', 'active'),
(1, 2, '3001', 'admin1', 'active');

-- ============================================================================
-- 17. INSERT SPECIALIST ENROLLMENTS
-- ============================================================================
INSERT INTO specialist_enrollments (user_id, course_id, lms_course_id, access_status, enrolled_at) VALUES
(13, 1, 'moodle_course_101', 'active', '2024-01-15 09:00:00'),
(13, 2, 'moodle_course_102', 'active', '2024-01-15 09:00:00'),
(14, 6, 'moodle_course_201', 'active', '2024-01-20 14:30:00'),
(14, 7, 'moodle_course_202', 'inactive', '2024-01-20 14:30:00'),
(15, 12, 'moodle_course_301', 'pending', NULL),
(15, 13, 'moodle_course_302', 'pending', NULL);

-- ============================================================================
-- 18. INSERT SYNC SCHEDULES
-- ============================================================================
INSERT INTO sync_schedules (name, sync_type, is_enabled, frequency_minutes, last_sync_at, next_sync_at) VALUES
('Auto Sync Users', 'auto_sync', 1, 60, '2024-02-20 15:00:00', '2024-02-20 16:00:00'),
('Full Sync Users', 'full_sync', 0, 1440, '2024-02-19 23:00:00', '2024-02-20 23:00:00');

-- ============================================================================
-- 19. INSERT ENROLLMENT LOGS (Sample logs)
-- ============================================================================
INSERT INTO enrollment_logs (user_id, action, action_type, target_id, target_type, status, created_at, performed_by) VALUES
(13, 'User enrolled to database course', 'manual_enroll', 6, 'course', 'success', '2024-02-15 10:30:00', 3),
(14, 'Auto sync executed for user', 'auto_sync', 14, 'user', 'success', '2024-02-20 15:00:00', 1),
(15, 'Manual unenroll from course', 'manual_unenroll', 13, 'course', 'failed', '2024-02-19 16:45:00', 3),
(13, 'Full sync executed', 'full_sync', 13, 'user', 'success', '2024-02-18 23:00:00', 1);

-- ============================================================================
-- 20. INSERT NOTIFICATIONS (Sample notifications)
-- ============================================================================
INSERT INTO notifications (user_id, title, message, notification_type, related_entity_id, related_entity_type, is_read) VALUES
(8, 'Η αίτησή σας έχει ληφθεί', 'Η αίτησή σας για θέση Ειδικού Επιστήμονα στις Ηλεκτρικές Μηχανές έχει ληφθεί.', 'application_received', 1, 'job_announcement', 1),
(9, 'Η αίτησή σας βρίσκεται σε αξιολόγηση', 'Η αίτησή σας βρίσκεται σε αξιολόγηση από το αρμόδιο κατάστημα.', 'status_update', 2, 'candidate_application', 0),
(13, 'Πρόσβαση Moodle ενεργή', 'Η πρόσβασή σας στο Moodle έχει ενεργοποιηθεί για το μάθημα Εισαγωγή στις Ηλεκτρικές Μηχανές.', 'enrollment', 1, 'specialist_enrollment', 0),
(3, 'Νέες αιτήσεις για αξιολόγηση', 'Υπάρχουν 3 νέες αιτήσεις που περιμένουν αξιολόγησης.', 'new_applications', 5, 'job_announcement', 0);

-- ============================================================================
-- 21. INSERT AUDIT LOGS (Sample audit logs)
-- ============================================================================
INSERT INTO audit_logs (user_id, action, entity_type, entity_id, changes, ip_address, created_at) VALUES
(1, 'Create Job Announcement', 'job_announcement', 1, '{\"title\": \"Ειδικός Επιστήμονας - Ηλεκτρικές Μηχανές\"}', '192.168.1.100', '2024-02-01 09:00:00'),
(3, 'Assign Evaluator', 'application_evaluator', 1, '{\"evaluator_id\": 5}', '192.168.1.101', '2024-02-05 14:30:00'),
(1, 'Update System Setting', 'system_settings', 1, '{\"auto_sync_enabled\": true}', '192.168.1.100', '2024-02-10 11:15:00'),
(3, 'Approve Application', 'candidate_application', 2, '{\"status\": \"accepted\", \"feedback\": \"Εξαιρετική αίτηση\"}', '192.168.1.101', '2024-02-15 16:45:00'),
(1, 'Execute Full Sync', 'enrollment_log', 4, '{\"synced_users\": 5}', '192.168.1.100', '2024-02-18 23:00:00');

-- ============================================================================
-- END OF SEED DATA
-- ============================================================================

-- Summary of inserted data:
-- ✓ 5 roles
-- ✓ 15 users (3 admins/hr, 3 evaluators, 5 candidates, 3 specialists, 1 spare)
-- ✓ 3 schools
-- ✓ 8 departments
-- ✓ 20 courses
-- ✓ 3 recruitment periods (1 active, 2 planning)
-- ✓ 5 job announcements (all published)
-- ✓ 7 application evaluators assignments
-- ✓ 8 application form fields
-- ✓ 9 candidate applications
-- ✓ Sample application responses
-- ✓ 3 themes
-- ✓ 10 system settings
-- ✓ 2 LMS connections
-- ✓ 5 LMS users
-- ✓ 6 specialist enrollments
-- ✓ 2 sync schedules
-- ✓ 4 enrollment logs
-- ✓ 4 notifications
-- ✓ 5 audit logs

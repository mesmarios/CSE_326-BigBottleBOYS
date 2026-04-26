-- ============================================================================
-- SEED DATA — bigbrothers
-- All test-user passwords hash to: Demo1234!
-- Hash: $2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.
-- ============================================================================

USE bigbrothers;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE audit_logs;
TRUNCATE TABLE enrollment_logs;
TRUNCATE TABLE sync_schedules;
TRUNCATE TABLE specialist_enrollments;
TRUNCATE TABLE lms_access;
TRUNCATE TABLE notifications;
TRUNCATE TABLE application_responses;
TRUNCATE TABLE application_form_fields;
TRUNCATE TABLE candidate_applications;
TRUNCATE TABLE application_evaluators;
TRUNCATE TABLE job_announcements;
TRUNCATE TABLE recruitment_periods;
TRUNCATE TABLE courses;
TRUNCATE TABLE departments;
TRUNCATE TABLE schools;
TRUNCATE TABLE themes;
TRUNCATE TABLE system_settings;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- USERS  (all passwords = Demo1234!)
-- ============================================================================
INSERT INTO users
    (id, username, email, password_hash, first_name, last_name, phone, role)
VALUES
-- admin
(1, 'admin_marios',   'admin@tepak.cy',
 '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.',
 'Μάριος', 'Μεσαρίτης', '+35799111001', 'admin'),

-- hr
(2, 'hr_eleni',       'hr@tepak.cy',
 '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.',
 'Ελένη', 'Παπαδοπούλου', '+35799111002', 'hr'),

-- evaluators
(3, 'eval_giorgos',   'eval.giorgos@tepak.cy',
 '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.',
 'Γιώργος', 'Κωνσταντίνου', '+35799111003', 'evaluator'),

(4, 'eval_christos',  'eval.christos@tepak.cy',
 '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.',
 'Χρήστος', 'Σταύρου', '+35799111004', 'evaluator'),

-- candidates
(5, 'cand_nikos',     'nikos.andreou@student.tepak.cy',
 '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.',
 'Νίκος', 'Αντρέου', '+35799222001', 'candidate'),

(6, 'cand_maria',     'maria.christou@student.tepak.cy',
 '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.',
 'Μαρία', 'Χρίστου', '+35799222002', 'candidate'),

(7, 'cand_panagiotis','panagiotis.ioannou@student.tepak.cy',
 '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.',
 'Παναγιώτης', 'Ιωάννου', '+35799222003', 'candidate'),

-- ee_hired (hired Special Scientists — have Enrollment Module access)
(8, 'ee_sofia',       'sofia.mihail@tepak.cy',
 '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.',
 'Σοφία', 'Μιχαήλ', '+35799333001', 'ee_hired'),

(9, 'ee_andreas',     'andreas.petrou@tepak.cy',
 '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.',
 'Ανδρέας', 'Πέτρου', '+35799333002', 'ee_hired');

-- ============================================================================
-- SCHOOLS
-- ============================================================================
INSERT INTO schools (id, name, description) VALUES
(1, 'Σχολή Μηχανικής και Τεχνολογίας',
    'Περιλαμβάνει τμήματα Πληροφορικής, Ηλεκτρολόγων Μηχανικών και Πολιτικών Μηχανικών.'),
(2, 'Σχολή Επικοινωνίας και Πολιτισμικών Σπουδών',
    'Περιλαμβάνει τμήματα Επικοινωνίας, Ψυχολογίας και Κοινωνιολογίας.');

-- ============================================================================
-- DEPARTMENTS
-- ============================================================================
INSERT INTO departments (id, school_id, name, description) VALUES
(1, 1, 'Τμήμα Επιστήμης και Τεχνολογίας Ηλεκτρονικών Υπολογιστών',
    'Προπτυχιακές και μεταπτυχιακές σπουδές στην Πληροφορική και Μηχανική Λογισμικού.'),
(2, 1, 'Τμήμα Ηλεκτρολόγων Μηχανικών και Μηχανικών Υπολογιστών',
    'Ηλεκτρονικά κυκλώματα, ενσωματωμένα συστήματα και αρχιτεκτονική υπολογιστών.'),
(3, 2, 'Τμήμα Επικοινωνίας και Σπουδών Διαδικτύου',
    'Ψηφιακά μέσα, δημοσιογραφία και ψηφιακή επικοινωνία.');

-- ============================================================================
-- COURSES
-- ============================================================================
INSERT INTO courses (id, department_id, code, name, description, credits, semester) VALUES
(1, 1, 'CSE326', 'Μηχανική Ιστού',
    'Ανάπτυξη web εφαρμογών: PHP, HTML, CSS, JavaScript και ασφάλεια backend.', 6, 6),
(2, 1, 'CSE201', 'Δομές Δεδομένων',
    'Λίστες, δέντρα, γράφοι και αλγόριθμοι αναζήτησης/ταξινόμησης.', 6, 3),
(3, 1, 'CSE315', 'Συστήματα Βάσεων Δεδομένων',
    'Σχεσιακό μοντέλο, SQL, κανονικοποίηση και συναλλαγές.', 6, 5),
(4, 2, 'EE201',  'Ανάλυση Κυκλωμάτων',
    'Θεωρία κυκλωμάτων DC/AC, νόμοι Kirchhoff και θεωρήματα Thevenin/Norton.', 6, 2),
(5, 2, 'EE310',  'Ψηφιακά Συστήματα',
    'Λογικές πύλες, αλγεβρική λογική, flip-flops και μνήμη.', 6, 4),
(6, 3, 'COM101', 'Εισαγωγή στην Επικοινωνία',
    'Βασικές αρχές επικοινωνίας, ΜΜΕ και ψηφιακά μέσα.', 4, 1);

-- ============================================================================
-- RECRUITMENT PERIODS
-- ============================================================================
INSERT INTO recruitment_periods (id, name, start_date, end_date, status, description) VALUES
(1, 'Περίοδος Αιτήσεων Εαρινού Εξαμήνου 2026',
    '2026-01-15', '2026-04-30', 'active',
    'Ανοιχτή περίοδος αιτήσεων για Ειδικούς Επιστήμονες εαρινού εξαμήνου 2025-2026.'),
(2, 'Περίοδος Αιτήσεων Χειμερινού Εξαμήνου 2025',
    '2025-09-01', '2025-12-15', 'closed',
    'Ολοκληρωμένη περίοδος αιτήσεων χειμερινού εξαμήνου 2025-2026.');

-- ============================================================================
-- JOB ANNOUNCEMENTS
-- ============================================================================
INSERT INTO job_announcements
    (id, period_id, school_id, department_id, course_id, title, description, requirements, number_of_positions, status)
VALUES
(1, 1, 1, 1, 1,
    'Ειδικός Επιστήμονας — Μηχανική Ιστού (CSE326)',
    'Διδακτική υποστήριξη του μαθήματος CSE326. Εργαστηριακές ασκήσεις PHP/HTML/CSS και εβδομαδιαίες διαλέξεις.',
    'Πτυχίο Πληροφορικής ή συναφές. Γνώση PHP, SQL, JavaScript. Εμπειρία τουλάχιστον 1 έτους.',
    2, 'published'),

(2, 1, 1, 1, 2,
    'Ειδικός Επιστήμονας — Δομές Δεδομένων (CSE201)',
    'Υποστήριξη εργαστηρίου Δομών Δεδομένων. Διόρθωση εργασιών και εβδομαδιαίες office hours.',
    'Πτυχίο Πληροφορικής. Γνώση C++/Java. Εμπειρία σε διδασκαλία.',
    1, 'published'),

(3, 1, 1, 1, 3,
    'Ειδικός Επιστήμονας — Βάσεις Δεδομένων (CSE315)',
    'Διδασκαλία SQL, σχεδιασμού σχήματος και κανονικοποίησης. Επίβλεψη εργασιών.',
    'Πτυχίο Πληροφορικής ή Μαθηματικών. Προχωρημένη γνώση SQL και MariaDB/PostgreSQL.',
    1, 'published'),

(4, 1, 1, 2, 4,
    'Ειδικός Επιστήμονας — Ανάλυση Κυκλωμάτων (EE201)',
    'Εργαστηριακές ασκήσεις ανάλυσης DC/AC κυκλωμάτων και χρήση οργάνων μέτρησης.',
    'Πτυχίο Ηλεκτρολόγου Μηχανικού. Εμπειρία με oscilloscope και multimeter.',
    1, 'published'),

(5, 1, 2, 3, 6,
    'Ειδικός Επιστήμονας — Εισαγωγή στην Επικοινωνία (COM101)',
    'Διδακτική βοήθεια στη θεωρία ΜΜΕ και ψηφιακών μέσων. Επίβλεψη ομαδικών εργασιών.',
    'Πτυχίο Επικοινωνίας ή συναφές. Εμπειρία σε ψηφιακά μέσα επικοινωνίας.',
    1, 'published'),

(6, 2, 1, 1, 2,
    'ΕΕ — Δομές Δεδομένων (Χειμ. 2025)',
    'Κλειστή θέση χειμερινού εξαμήνου για εργαστήριο CSE201.',
    'Πτυχίο Πληροφορικής.', 1, 'closed');

-- ============================================================================
-- APPLICATION EVALUATORS
-- ============================================================================
INSERT INTO application_evaluators (announcement_id, evaluator_id) VALUES
(1, 3),
(1, 4),
(2, 3),
(3, 4),
(4, 3),
(5, 4);

-- ============================================================================
-- CANDIDATE APPLICATIONS
-- ============================================================================
INSERT INTO candidate_applications
    (id, announcement_id, candidate_id, status, progress, submitted_at, reviewed_at, reviewed_by,
     app_degree, app_institution, app_specialization, app_experience, app_declared)
VALUES
-- Νίκος: submitted application for CSE326
(1, 1, 5, 'submitted', 100,
    '2026-02-10 14:30:00', NULL, NULL,
    'BSc Πληροφορικής', 'ΤΕΠΑΚ', 'Μηχανική Ιστού', 2, 1),

-- Μαρία: under_review for CSE315
(2, 3, 6, 'under_review', 100,
    '2026-02-05 10:00:00', '2026-02-20 09:00:00', 4,
    'BSc Πληροφορικής', 'Πανεπιστήμιο Κύπρου', 'Βάσεις Δεδομένων', 3, 1),

-- Μαρία: accepted for EE201 (from closed period via announcement 4)
(3, 4, 6, 'accepted', 100,
    '2025-10-01 11:00:00', '2025-10-20 14:00:00', 3,
    'BSc Ηλεκτρολόγου Μηχανικού', 'ΤΕΠΑΚ', 'Κυκλώματα', 1, 1),

-- Παναγιώτης: draft
(4, 2, 7, 'draft', 40,
    NULL, NULL, NULL,
    'BSc Πληροφορικής', 'ΤΕΠΑΚ', 'Αλγόριθμοι', 1, 0),

-- Νίκος: rejected for CSE201 (closed period 2 announcement)
(5, 6, 5, 'rejected', 100,
    '2025-09-20 08:00:00', '2025-10-10 16:00:00', 4,
    'BSc Πληροφορικής', 'ΤΕΠΑΚ', 'Δομές Δεδομένων', 1, 1);

-- ============================================================================
-- LMS ACCESS  (for ee_hired users)
-- ============================================================================
INSERT INTO lms_access (user_id, course_id, status, moodle_user_id, granted_at) VALUES
-- Σοφία Μιχαήλ: active access to CSE326
(8, 1, 'active', 1042, '2026-02-15 09:00:00'),
-- Ανδρέας Πέτρου: inactive (not yet enrolled)
(9, 3, 'inactive', NULL, NULL);

-- ============================================================================
-- SPECIALIST ENROLLMENTS  (mirrors lms_access with Moodle course IDs)
-- ============================================================================
INSERT INTO specialist_enrollments (user_id, course_id, lms_course_id, access_status, enrolled_at) VALUES
(8, 1, 'moodle-course-42', 'active',   '2026-02-15 09:00:00'),
(9, 3, 'moodle-course-17', 'inactive', NULL);

-- ============================================================================
-- SYNC SCHEDULES
-- ============================================================================
INSERT INTO sync_schedules (name, sync_type, is_enabled, frequency_minutes) VALUES
('Αυτόματος LMS Sync',    'auto_sync',  0, 60),
('Full Moodle Sync',      'full_sync',  0, 1440);

-- ============================================================================
-- SYSTEM SETTINGS
-- ============================================================================
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
-- branding
('app_name',              'CareerTrack',
 'Όνομα εφαρμογής'),
('app_slogan',            'Σύστημα Διαχείρισης ΕΕ',
 'Υπότιτλος εφαρμογής'),
('app_description',       'Σύστημα διαχείρισης αιτήσεων εκπαιδευτικού προσωπικού για ακαδημαϊκά ιδρύματα.',
 'Περιγραφή εφαρμογής'),
('admin_email',           'admin@tepak.cy',
 'Email διαχειριστή'),
('support_phone',         '+357 25 002500',
 'Τηλέφωνο υποστήριξης'),
('institution_name',      'ΤΕΠΑΚ',
 'Όνομα ιδρύματος'),
-- moodle
('moodle_url',            'http://moodle.tepak.cy',
 'Βασική URL Moodle'),
('moodle_token',          '',
 'Moodle REST API token'),
-- enrollment sync
('lms_auto_sync_enabled', '0',
 'Αυτόματος συγχρονισμός Moodle (0=off, 1=on)'),
('lms_last_sync_at',      '',
 'Τελευταίος συγχρονισμός Moodle (ISO timestamp)'),
('lms_last_sync_log',     '',
 'Log τελευταίου συγχρονισμού Moodle'),
-- maintenance
('maintenance_mode',      '0',
 'Λειτουργία συντήρησης (0=off, 1=on)');

-- ============================================================================
-- NOTIFICATIONS  (sample — for admin user)
-- ============================================================================
INSERT INTO notifications (user_id, title, message, notification_type, related_entity_type, is_read) VALUES
(1, 'Νέα αίτηση υποβλήθηκε',
    'Ο Νίκος Αντρέου υπέβαλε αίτηση για τη θέση CSE326.',
    'application_received', 'candidate_application', 0),
(1, 'Αίτηση σε αξιολόγηση',
    'Η αίτηση της Μαρίας Χρίστου για CSE315 βρίσκεται σε αξιολόγηση.',
    'status_update', 'candidate_application', 0),
(2, 'Νέα αίτηση για αξιολόγηση',
    'Υπάρχει νέα αίτηση που απαιτεί ανασκόπηση HR.',
    'new_applications', 'candidate_application', 1);

-- ============================================================================
-- END OF SEED
-- ============================================================================

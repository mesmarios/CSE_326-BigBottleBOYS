-- ============================================================================
-- SEED DATA — bigbrothers
-- All test-user passwords hash to: Demo1234!
-- Hash: $2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK
-- ============================================================================

USE bigbrothers;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM audit_logs;
DELETE FROM enrollment_logs;
DELETE FROM sync_schedules;
DELETE FROM specialist_enrollments;
DELETE FROM lms_access;
DELETE FROM notifications;
DELETE FROM application_responses;
DELETE FROM application_form_fields;
DELETE FROM candidate_applications;
DELETE FROM application_evaluators;
DELETE FROM job_announcements;
DELETE FROM recruitment_periods;
DELETE FROM courses;
DELETE FROM departments;
DELETE FROM schools;
DELETE FROM themes;
DELETE FROM system_settings;
DELETE FROM users;
ALTER TABLE audit_logs            AUTO_INCREMENT = 1;
ALTER TABLE enrollment_logs       AUTO_INCREMENT = 1;
ALTER TABLE sync_schedules        AUTO_INCREMENT = 1;
ALTER TABLE specialist_enrollments AUTO_INCREMENT = 1;
ALTER TABLE lms_access            AUTO_INCREMENT = 1;
ALTER TABLE notifications         AUTO_INCREMENT = 1;
ALTER TABLE application_responses AUTO_INCREMENT = 1;
ALTER TABLE application_form_fields AUTO_INCREMENT = 1;
ALTER TABLE candidate_applications AUTO_INCREMENT = 1;
ALTER TABLE application_evaluators AUTO_INCREMENT = 1;
ALTER TABLE job_announcements     AUTO_INCREMENT = 1;
ALTER TABLE recruitment_periods   AUTO_INCREMENT = 1;
ALTER TABLE courses               AUTO_INCREMENT = 1;
ALTER TABLE departments           AUTO_INCREMENT = 1;
ALTER TABLE schools               AUTO_INCREMENT = 1;
ALTER TABLE themes                AUTO_INCREMENT = 1;
ALTER TABLE system_settings       AUTO_INCREMENT = 1;
ALTER TABLE users                 AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- USERS  (all passwords = Demo1234!)
-- ============================================================================
INSERT INTO users
    (id, username, email, password_hash, first_name, last_name, phone, address, dob, role)
VALUES
-- admin
(1, 'admin_marios',   'admin@tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Μάριος', 'Μεσαρίτης', '+35799111001', 'Λεωφόρος Μακαρίου 12, Λεμεσός', '1988-03-14', 'admin'),

(2, 'admin_shittas',  'admin2@tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Μάριος', 'Σιήττας', '+35799696969', 'Οδός Ανεξαρτησίας 45, Λεμεσός', '1991-07-22', 'admin'),

(3, 'admin_tsadiotis','admin3@tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Μιχάλης', 'Τσαδιώτης', '+35799676767', '25ης Μαρτίου 8, Λευκωσία', '1987-11-05', 'admin'),

-- hr
(4, 'hr_eleni',       'hr@tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Ελένη', 'Παπαδοπούλου', '+35799111002', 'Αγίου Ανδρέου 102, Λεμεσός', '1990-02-18', 'hr'),

-- evaluators
(5, 'eval_giorgos',   'eval.giorgos@tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Γιώργος', 'Κωνσταντίνου', '+35799111003', 'Αρσινόης 17, Λευκωσία', '1985-09-09', 'evaluator'),

(6, 'eval_christos',  'eval.christos@tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Χρήστος', 'Σταύρου', '+35799111004', 'Κέννεντυ 33, Πάφος', '1986-12-27', 'evaluator'),

-- candidates
(7, 'cand_nikos',     'nikos.andreou@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Νίκος', 'Αντρέου', '+35799222001', 'Ερμού 4, Λάρνακα', '2001-01-30', 'candidate'),

(8, 'cand_maria',     'maria.christou@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Μαρία', 'Χρίστου', '+35799222002', 'Γρίβα Διγενή 61, Λευκωσία', '2000-06-12', 'candidate'),

(9, 'cand_panagiotis','panagiotis.ioannou@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Παναγιώτης', 'Ιωάννου', '+35799222003', 'Αρχ. Μακαρίου Γ 78, Πάφος', '1999-10-03', 'candidate'),

-- ee_hired (hired Special Scientists — have Enrollment Module access)
(10, 'ee_sofia',       'sofia.mihail@tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Σοφία', 'Μιχαήλ', '+35799333001', 'Τσερίου 210, Στρόβολος', '1984-04-16', 'ee_hired'),

(11, 'ee_andreas',     'andreas.petrou@tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Ανδρέας', 'Πέτρου', '+35799333002', 'Λάρνακος 55, Αγλαντζιά', '1983-08-24', 'ee_hired'),

(12, 'cand_elena',     'elena.georgiou@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Έλενα', 'Γεωργίου', '+35799222004', 'Κυριάκου Μάτση 9, Λευκωσία', '2002-05-08', 'candidate'),

(13, 'cand_andreas',   'andreas.nikolaou@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Ανδρέας', 'Νικολάου', '+35799222005', 'Φιλελλήνων 26, Λάρνακα', '2001-12-19', 'candidate'),

(14, 'cand_ioanna',    'ioanna.michael@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Ιωάννα', 'Μιχαήλ', '+35799222006', 'Θεμιστοκλή Δέρβη 14, Λευκωσία', '2003-03-27', 'candidate'),

(15, 'cand_petros',    'petros.savva@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Πέτρος', 'Σάββα', '+35799222007', 'Βασιλέως Παύλου 40, Λεμεσός', '2000-09-14', 'candidate'),

(16, 'cand_christina', 'christina.antoniou@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Χριστίνα', 'Αντωνίου', '+35799222008', 'Ομήρου 7, Πάφος', '2002-11-02', 'candidate'),

(17, 'cand_stavros',   'stavros.hadjis@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Σταύρος', 'Χατζής', '+35799222009', 'Ελευθερίας 88, Αραδίππου', '2001-04-21', 'candidate'),

(18, 'cand_demetra',   'demetra.loizou@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Δήμητρα', 'Λοΐζου', '+35799222010', 'Δημοκρατίας 19, Λακατάμια', '2000-07-29', 'candidate'),

(19, 'cand_kyriakos',  'kyriakos.pavlou@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Κυριάκος', 'Παύλου', '+35799222011', 'Αμμοχώστου 121, Λάρνακα', '1999-02-11', 'candidate'),

(20, 'cand_antri',     'antri.charalambous@student.tepak.cy',
 '$2y$12$R3VOLS6.bYZ8LPCxYWRty.t4uLCHfZEoSRRCcR/sly1zPqLC8zXZK',
 'Άντρη', 'Χαραλάμπους', '+35799222012', 'Σπύρου Κυπριανού 66, Γερμασόγεια', '2002-10-25', 'candidate');

-- Populate profile fields (degree/institution/specialization/experience/summary)
UPDATE users
SET degree = 'MSc Information Systems',
    institution = 'TEPAK',
    specialization = 'Platform Administration',
    experience = 10,
    summary = 'Administrator responsible for system operations, security, and governance.'
WHERE role = 'admin';

UPDATE users
SET degree = 'MBA Human Resource Management',
    institution = 'University of Cyprus',
    specialization = 'Recruitment and Talent Operations',
    experience = 8,
    summary = 'HR manager coordinating hiring workflows and candidate communication.'
WHERE role = 'hr';

UPDATE users
SET degree = 'MSc Engineering',
    institution = 'TEPAK',
    specialization = 'Academic Evaluation',
    experience = 9,
    summary = 'Evaluator reviewing applications, qualifications, and interview performance.'
WHERE role = 'evaluator';

UPDATE users
SET degree = 'MSc Applied Computing',
    institution = 'TEPAK',
    specialization = 'Learning Technologies',
    experience = 6,
    summary = 'Special scientist supporting course delivery and LMS-related operations.'
WHERE role = 'ee_hired';

UPDATE users
SET degree = 'BSc Candidate (In Progress)',
    institution = 'TEPAK',
    specialization = 'Computer Science',
    experience = 1,
    summary = 'Candidate with academic background and motivation for teaching support roles.'
WHERE role = 'candidate';
-- ============================================================================
-- SCHOOLS
-- ============================================================================
INSERT INTO schools (id, name, description) VALUES
(1, 'Σχολή Μηχανικής και Τεχνολογίας',
    'Περιλαμβάνει τμήματα Πληροφορικής, Ηλεκτρολόγων Μηχανικών και Πολιτικών Μηχανικών.'),
(2, 'Σχολή Επικοινωνίας και Πολιτισμικών Σπουδών',
    'Περιλαμβάνει τμήματα Επικοινωνίας, Ψυχολογίας και Κοινωνιολογίας.'),
(3, 'Σχολή Διοίκησης και Οικονομίας',
    'Περιλαμβάνει τμήματα διοίκησης, οικονομικών και αναλυτικής επιχειρήσεων.');

-- ============================================================================
-- DEPARTMENTS
-- ============================================================================
INSERT INTO departments (id, school_id, name, description) VALUES
(1, 1, 'Τμήμα Επιστήμης και Τεχνολογίας Ηλεκτρονικών Υπολογιστών',
    'Προπτυχιακές και μεταπτυχιακές σπουδές στην Πληροφορική και Μηχανική Λογισμικού.'),
(2, 1, 'Τμήμα Ηλεκτρολόγων Μηχανικών και Μηχανικών Υπολογιστών',
    'Ηλεκτρονικά κυκλώματα, ενσωματωμένα συστήματα και αρχιτεκτονική υπολογιστών.'),
(3, 2, 'Τμήμα Επικοινωνίας και Σπουδών Διαδικτύου',
    'Ψηφιακά μέσα, δημοσιογραφία και ψηφιακή επικοινωνία.'),
(4, 3, 'Τμήμα Διοίκησης Επιχειρήσεων',
    'Διοίκηση οργανισμών, επιχειρησιακή στρατηγική και επιχειρηματικότητα.'),
(5, 3, 'Τμήμα Χρηματοοικονομικής και Λογιστικής',
    'Χρηματοοικονομική ανάλυση, λογιστική και διαχείριση κινδύνου.');

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
    'Βασικές αρχές επικοινωνίας, ΜΜΕ και ψηφιακά μέσα.', 4, 1),
(7, 3, 'COM220', 'Ψηφιακό Μάρκετινγκ',
    'Στρατηγικές περιεχομένου, analytics και καμπάνιες κοινωνικών δικτύων.', 5, 4),
(8, 4, 'BUS210', 'Διοίκηση Έργων',
    'Μεθοδολογίες project management, προγραμματισμός πόρων και παρακολούθηση έργων.', 6, 3),
(9, 5, 'FIN301', 'Χρηματοοικονομική Ανάλυση',
    'Αξιολόγηση επενδύσεων, δείκτες απόδοσης και ανάλυση οικονομικών καταστάσεων.', 6, 5);

-- ============================================================================
-- RECRUITMENT PERIODS
-- ============================================================================
INSERT INTO recruitment_periods (id, name, start_date, end_date, status, description) VALUES
(1, 'Περίοδος Αιτήσεων Εαρινού Εξαμήνου 2026',
    '2026-01-15', '2026-04-30', 'active',
    'Ανοιχτή περίοδος αιτήσεων για Ειδικούς Επιστήμονες εαρινού εξαμήνου 2025-2026.'),
(2, 'Περίοδος Αιτήσεων Χειμερινού Εξαμήνου 2025',
    '2025-09-01', '2025-12-15', 'closed',
    'Ολοκληρωμένη περίοδος αιτήσεων χειμερινού εξαμήνου 2025-2026.'),
(3, 'Περίοδος Αιτήσεων Ακαδημαϊκού Έτους 2024-2025',
    '2024-09-01', '2025-05-31', 'archived',
    'Αρχειοθετημένη περίοδος με ιστορικά δεδομένα αιτήσεων για σύγκριση αναφορών.');

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
    'Πτυχίο Πληροφορικής.', 1, 'closed'),

(7, 2, 1, 2, 5,
    'ΕΕ — Ψηφιακά Συστήματα (Χειμ. 2025)',
    'Κλειστή θέση για εργαστήρια ψηφιακής λογικής και ενσωματωμένων συστημάτων.',
    'Πτυχίο Ηλεκτρολόγου Μηχανικού ή συναφές.', 1, 'closed'),

(8, 2, 2, 3, 7,
    'ΕΕ — Ψηφιακό Μάρκετινγκ (Χειμ. 2025)',
    'Υποστήριξη σε εργαστήρια analytics, social media και στρατηγικής περιεχομένου.',
    'Πτυχίο Επικοινωνίας, Μάρκετινγκ ή συναφές.', 1, 'closed'),

(9, 2, 3, 4, 8,
    'ΕΕ — Διοίκηση Έργων (Χειμ. 2025)',
    'Διδακτική υποστήριξη σε μεθοδολογίες project management και case studies.',
    'Πτυχίο Διοίκησης ή πιστοποίηση project management.', 1, 'closed'),

(10, 1, 1, 2, 5,
    'Ειδικός Επιστήμονας — Ψηφιακά Συστήματα (EE310)',
    'Εργαστηριακή διδασκαλία λογικών κυκλωμάτων, flip-flops και FPGA εισαγωγής.',
    'Πτυχίο Ηλεκτρολόγου Μηχανικού. Εμπειρία με ψηφιακή σχεδίαση.', 2, 'published'),

(11, 1, 2, 3, 7,
    'Ειδικός Επιστήμονας — Ψηφιακό Μάρκετινγκ (COM220)',
    'Υποστήριξη σε εργαστήρια καμπανιών, μετρήσεων και ψηφιακής στρατηγικής.',
    'Πτυχίο Επικοινωνίας/Μάρκετινγκ. Εμπειρία με analytics.', 1, 'published'),

(12, 1, 3, 4, 8,
    'Ειδικός Επιστήμονας — Διοίκηση Έργων (BUS210)',
    'Διδασκαλία πρακτικών project planning, risk tracking και agile case studies.',
    'Πτυχίο Διοίκησης. Εμπειρία σε πραγματικά έργα.', 1, 'published'),

(13, 1, 3, 5, 9,
    'Ειδικός Επιστήμονας — Χρηματοοικονομική Ανάλυση (FIN301)',
    'Υποστήριξη εργαστηρίων οικονομικών καταστάσεων και επενδυτικών δεικτών.',
    'Πτυχίο Χρηματοοικονομικής ή Λογιστικής.', 1, 'published'),

(14, 3, 1, 1, 3,
    'ΕΕ — Βάσεις Δεδομένων (Ακαδ. 2024-2025)',
    'Ιστορική θέση για εργαστήρια SQL και σχεδιασμού βάσεων.',
    'Πτυχίο Πληροφορικής ή συναφές.', 1, 'closed'),

(15, 3, 3, 5, 9,
    'ΕΕ — Χρηματοοικονομική Ανάλυση (Ακαδ. 2024-2025)',
    'Ιστορική θέση για εργαστήρια οικονομικής ανάλυσης.',
    'Πτυχίο Χρηματοοικονομικής ή Λογιστικής.', 1, 'closed');

-- ============================================================================
-- APPLICATION EVALUATORS
-- ============================================================================
INSERT INTO application_evaluators (announcement_id, evaluator_id) VALUES
(1, 5),
(1, 6),
(2, 5),
(3, 6),
(4, 5),
(5, 6),
(7, 5),
(8, 6),
(9, 5),
(10, 6),
(11, 5),
(12, 6),
(13, 5),
(14, 6),
(15, 5);

-- ============================================================================
-- CANDIDATE APPLICATIONS
-- ============================================================================
INSERT INTO candidate_applications
    (id, announcement_id, candidate_id, status, progress, submitted_at, reviewed_at, reviewed_by,
     app_degree, app_institution, app_specialization, app_experience, app_declared)
VALUES
-- Νίκος: submitted application for CSE326
(1, 1, 7, 'submitted', 100,
    '2026-02-10 14:30:00', NULL, NULL,
    'BSc Πληροφορικής', 'ΤΕΠΑΚ', 'Μηχανική Ιστού', 2, 1),

-- Μαρία: under_review for CSE315
(2, 3, 8, 'under_review', 100,
    '2026-02-05 10:00:00', '2026-02-20 09:00:00', 6,
    'BSc Πληροφορικής', 'Πανεπιστήμιο Κύπρου', 'Βάσεις Δεδομένων', 3, 1),

-- Μαρία: accepted for EE201
(3, 4, 8, 'accepted', 100,
    '2026-03-01 11:00:00', '2026-03-20 14:00:00', 5,
    'BSc Ηλεκτρολόγου Μηχανικού', 'ΤΕΠΑΚ', 'Κυκλώματα', 1, 1),

-- Παναγιώτης: draft
(4, 2, 9, 'draft', 40,
    NULL, NULL, NULL,
    'BSc Πληροφορικής', 'ΤΕΠΑΚ', 'Αλγόριθμοι', 1, 0),

-- Νίκος: rejected for CSE201 (closed period 2 announcement)
(5, 6, 7, 'rejected', 100,
    '2025-09-20 08:00:00', '2025-10-10 16:00:00', 6,
    'BSc Πληροφορικής', 'ΤΕΠΑΚ', 'Δομές Δεδομένων', 1, 1),

-- Περίοδος 1: Εαρινό 2026 — richer report data across Jan-Apr
(6, 1, 12, 'accepted', 100,
    '2026-01-22 09:15:00', '2026-02-08 12:20:00', 5,
    'MSc Πληροφορικής', 'Πανεπιστήμιο Κύπρου', 'Full Stack Development', 4, 1),
(7, 1, 13, 'under_review', 100,
    '2026-01-30 16:45:00', '2026-02-12 10:30:00', 6,
    'BSc Computer Science', 'University of Nicosia', 'Web Applications', 2, 1),
(8, 2, 14, 'submitted', 100,
    '2026-02-12 11:10:00', NULL, NULL,
    'BSc Πληροφορικής', 'ΤΕΠΑΚ', 'Αλγόριθμοι', 1, 1),
(9, 2, 15, 'rejected', 100,
    '2026-02-19 13:40:00', '2026-03-02 09:10:00', 5,
    'BSc Μαθηματικών', 'ΕΚΠΑ', 'Αλγοριθμική Σκέψη', 2, 1),
(10, 3, 16, 'accepted', 100,
    '2026-02-24 15:05:00', '2026-03-08 15:30:00', 6,
    'MSc Data Science', 'ΤΕΠΑΚ', 'SQL και Data Modeling', 5, 1),
(11, 3, 17, 'under_review', 100,
    '2026-03-04 10:25:00', '2026-03-18 11:00:00', 6,
    'BSc Πληροφορικής', 'Πανεπιστήμιο Αιγαίου', 'Βάσεις Δεδομένων', 3, 1),
(12, 4, 18, 'submitted', 100,
    '2026-03-11 12:00:00', NULL, NULL,
    'BSc Ηλεκτρολόγου Μηχανικού', 'ΤΕΠΑΚ', 'Ανάλυση Κυκλωμάτων', 2, 1),
(13, 5, 19, 'withdrawn', 100,
    '2026-03-16 09:50:00', NULL, NULL,
    'BA Επικοινωνίας', 'Frederick University', 'Ψηφιακά Μέσα', 1, 1),
(14, 10, 20, 'accepted', 100,
    '2026-03-22 17:30:00', '2026-04-03 13:15:00', 6,
    'MEng Ηλεκτρολόγου Μηχανικού', 'ΤΕΠΑΚ', 'Ψηφιακή Σχεδίαση', 4, 1),
(15, 10, 7, 'submitted', 100,
    '2026-04-02 08:35:00', NULL, NULL,
    'BSc Πληροφορικής', 'ΤΕΠΑΚ', 'Embedded Systems', 2, 1),
(16, 11, 8, 'under_review', 100,
    '2026-04-05 14:10:00', '2026-04-14 09:45:00', 5,
    'BA Επικοινωνίας', 'ΤΕΠΑΚ', 'Digital Strategy', 3, 1),
(17, 11, 9, 'accepted', 100,
    '2026-04-09 11:55:00', '2026-04-20 10:00:00', 5,
    'MSc Marketing', 'University of Manchester', 'Social Analytics', 5, 1),
(18, 12, 12, 'submitted', 100,
    '2026-04-12 16:25:00', NULL, NULL,
    'MBA', 'ΤΕΠΑΚ', 'Project Management', 6, 1),
(19, 13, 13, 'rejected', 100,
    '2026-04-18 10:15:00', '2026-04-25 12:00:00', 5,
    'BSc Finance', 'University of Cyprus', 'Financial Reporting', 2, 1),
(20, 13, 14, 'draft', 60,
    NULL, NULL, NULL,
    'BSc Accounting', 'ΤΕΠΑΚ', 'Investment Analysis', 1, 0),

-- Περίοδος 2: Χειμερινό 2025 — data across Sep-Dec
(21, 6, 15, 'accepted', 100,
    '2025-09-08 09:20:00', '2025-09-28 15:00:00', 6,
    'MSc Πληροφορικής', 'ΤΕΠΑΚ', 'Δομές Δεδομένων', 4, 1),
(22, 6, 16, 'under_review', 100,
    '2025-09-27 12:30:00', '2025-10-09 10:20:00', 5,
    'BSc Πληροφορικής', 'Πανεπιστήμιο Κρήτης', 'Algorithms', 2, 1),
(23, 7, 17, 'submitted', 100,
    '2025-10-15 16:15:00', NULL, NULL,
    'BSc Ηλεκτρολόγου Μηχανικού', 'ΤΕΠΑΚ', 'Digital Logic', 1, 1),
(24, 7, 18, 'rejected', 100,
    '2025-10-29 09:40:00', '2025-11-12 11:30:00', 6,
    'BEng Electrical Engineering', 'University of Patras', 'Computer Architecture', 2, 1),
(25, 8, 19, 'accepted', 100,
    '2025-11-07 13:05:00', '2025-11-24 14:10:00', 5,
    'BA Επικοινωνίας', 'ΤΕΠΑΚ', 'Digital Marketing', 3, 1),
(26, 8, 20, 'submitted', 100,
    '2025-11-20 08:55:00', NULL, NULL,
    'MSc Communication', 'University of Leeds', 'Content Strategy', 4, 1),
(27, 9, 7, 'under_review', 100,
    '2025-12-03 12:00:00', '2025-12-12 09:35:00', 5,
    'MBA', 'Open University Cyprus', 'Project Planning', 5, 1),
(28, 9, 8, 'withdrawn', 100,
    '2025-12-10 17:20:00', NULL, NULL,
    'BBA', 'ΤΕΠΑΚ', 'Operations Management', 1, 1),

-- Περίοδος 3: Ιστορικά δεδομένα 2024-2025 — older year in filter
(29, 14, 9, 'accepted', 100,
    '2024-09-18 10:10:00', '2024-10-03 12:10:00', 6,
    'MSc Data Engineering', 'ΤΕΠΑΚ', 'Database Systems', 4, 1),
(30, 14, 12, 'submitted', 100,
    '2024-10-22 14:45:00', NULL, NULL,
    'BSc Πληροφορικής', 'University of Cyprus', 'SQL', 2, 1),
(31, 15, 13, 'under_review', 100,
    '2025-01-16 09:30:00', '2025-02-01 10:15:00', 5,
    'BSc Finance', 'ΤΕΠΑΚ', 'Corporate Finance', 3, 1),
(32, 15, 14, 'rejected', 100,
    '2025-03-04 15:20:00', '2025-03-19 11:45:00', 5,
    'BSc Accounting', 'European University Cyprus', 'Financial Statements', 2, 1),
(33, 14, 15, 'accepted', 100,
    '2025-05-09 08:40:00', '2025-05-22 13:00:00', 6,
    'MSc Computer Science', 'University of Sheffield', 'Relational Databases', 5, 1);

-- ============================================================================
-- LMS ACCESS  (for ee_hired users)
-- ============================================================================
INSERT INTO lms_access (user_id, course_id, status, moodle_user_id, granted_at) VALUES
-- Σοφία Μιχαήλ: active access to CSE326
(10, 1, 'active', 1042, '2026-02-15 09:00:00'),
-- Ανδρέας Πέτρου: inactive (not yet enrolled)
(11, 3, 'inactive', NULL, NULL);

-- ============================================================================
-- SPECIALIST ENROLLMENTS  (mirrors lms_access with Moodle course IDs)
-- ============================================================================
INSERT INTO specialist_enrollments (user_id, course_id, lms_course_id, access_status, enrolled_at) VALUES
(10, 1, 'moodle-course-42', 'active',   '2026-02-15 09:00:00'),
(11, 3, 'moodle-course-17', 'inactive', NULL);

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
(4, 'Νέα αίτηση για αξιολόγηση',
    'Υπάρχει νέα αίτηση που απαιτεί ανασκόπηση HR.',
    'new_applications', 'candidate_application', 1);

-- ============================================================================
-- END OF SEED
-- ============================================================================


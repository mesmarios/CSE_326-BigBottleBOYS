USE bigbrothers;

SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO roles (id, name, description) VALUES
(1, 'admin', 'Πλήρης πρόσβαση στη διαχείριση του συστήματος'),
(2, 'hr_manager', 'Διαχείριση recruitment και enrollment ροών'),
(3, 'evaluator', 'Αξιολόγηση αιτήσεων υποψηφίων'),
(4, 'candidate', 'Υποψήφιος για θέση ειδικού επιστήμονα'),
(5, 'specialist', 'Προσληφθείς ειδικός επιστήμονας με πρόσβαση LMS');

INSERT INTO users (
    id, username, email, password_hash, first_name, last_name, phone, address, dob,
    degree, institution, specialization, experience, summary, role, created_at, updated_at
) VALUES
(1, 'admin_main', 'admin@tepak.cy', '$2y$12$HxAzXEXC/TXT98XIPmR2su11Jigpcby02hPgqI39e3C7LNSKFDLFO', 'Γιάννης', 'Παπαδόπουλος', '+35799111111', 'Λεμεσός, Κύπρος', '1985-02-14', 'PhD', 'University of Cyprus', 'Educational Technology', 12, 'Κεντρικός διαχειριστής του συστήματος και συντονιστής ακαδημαϊκών διαδικασιών.', 'admin', '2026-01-05 09:00:00', '2026-04-22 11:30:00'),
(2, 'hr_manager', 'hr@tepak.cy', '$2y$12$HxAzXEXC/TXT98XIPmR2su11Jigpcby02hPgqI39e3C7LNSKFDLFO', 'Μαρία', 'Αντωνίου', '+35799222222', 'Λευκωσία, Κύπρος', '1988-07-09', 'MBA', 'Open University of Cyprus', 'Human Resources', 9, 'Υπεύθυνη ανθρώπινου δυναμικού για προσλήψεις και παρακολούθηση onboarding.', 'hr_manager', '2026-01-10 10:15:00', '2026-04-23 10:45:00'),
(3, 'evaluator_one', 'evaluator1@tepak.cy', '$2y$12$HxAzXEXC/TXT98XIPmR2su11Jigpcby02hPgqI39e3C7LNSKFDLFO', 'Ανδρέας', 'Νικολάου', '+35799333333', 'Πάφος, Κύπρος', '1979-03-22', 'PhD', 'Aristotle University', 'Computer Science', 14, 'Ακαδημαϊκός αξιολογητής για τεχνικά μαθήματα και εργαστήρια.', 'evaluator', '2026-01-12 12:00:00', '2026-04-18 15:00:00'),
(4, 'evaluator_two', 'evaluator2@tepak.cy', '$2y$12$HxAzXEXC/TXT98XIPmR2su11Jigpcby02hPgqI39e3C7LNSKFDLFO', 'Ελένη', 'Γεωργίου', '+35799444444', 'Λάρνακα, Κύπρος', '1981-11-03', 'PhD', 'University of Patras', 'Digital Systems', 11, 'Αξιολογήτρια για ανακοινώσεις εργαστηρίων και πρακτικών μαθημάτων.', 'evaluator', '2026-01-15 09:40:00', '2026-04-19 16:10:00'),
(5, 'candidate_one', 'candidate1@example.com', '$2y$12$HxAzXEXC/TXT98XIPmR2su11Jigpcby02hPgqI39e3C7LNSKFDLFO', 'Μάριος', 'Σιήττας', '+35799555555', 'Λεμεσός, Κύπρος', '1998-06-01', 'MSc', 'Tsinghua University', 'Web Engineering', 4, 'Υποψήφιος με εμπειρία σε web εφαρμογές, PHP και ανάπτυξη διεπαφών.', 'candidate', '2026-02-01 08:20:00', '2026-04-24 14:00:00'),
(6, 'candidate_two', 'candidate2@example.com', '$2y$12$HxAzXEXC/TXT98XIPmR2su11Jigpcby02hPgqI39e3C7LNSKFDLFO', 'Χρίστος', 'Παύλου', '+35799666666', 'Λευκωσία, Κύπρος', '1996-09-18', 'MSc', 'University of Cyprus', 'Data Systems', 5, 'Εστιάζει σε συστήματα βάσεων, ποιότητα λογισμικού και διαχείριση δεδομένων.', 'candidate', '2026-02-03 10:10:00', '2026-04-24 14:05:00'),
(7, 'candidate_three', 'candidate3@example.com', '$2y$12$HxAzXEXC/TXT98XIPmR2su11Jigpcby02hPgqI39e3C7LNSKFDLFO', 'Άννα', 'Ιωάννου', '+35799777777', 'Λάρνακα, Κύπρος', '1997-12-12', 'MSc', 'University of Patras', 'Embedded Systems', 3, 'Υποψήφια με πρακτική εμπειρία σε εργαστηριακή υποστήριξη και embedded συστήματα.', 'candidate', '2026-02-05 11:25:00', '2026-04-24 14:10:00'),
(8, 'specialist_one', 'specialist1@tepak.cy', '$2y$12$HxAzXEXC/TXT98XIPmR2su11Jigpcby02hPgqI39e3C7LNSKFDLFO', 'Λουκάς', 'Δημητρίου', '+35799888888', 'Λεμεσός, Κύπρος', '1989-04-05', 'MSc', 'Cyprus University of Technology', 'Web Platforms', 8, 'Προσληφθείς ειδικός επιστήμονας με ενεργές αναθέσεις σε web και database courses.', 'specialist', '2026-01-20 09:10:00', '2026-04-24 13:30:00'),
(9, 'specialist_two', 'specialist2@tepak.cy', '$2y$12$HxAzXEXC/TXT98XIPmR2su11Jigpcby02hPgqI39e3C7LNSKFDLFO', 'Κατερίνα', 'Θεοδώρου', '+35799999999', 'Πάφος, Κύπρος', '1990-10-27', 'MSc', 'University of Nicosia', 'Digital Learning', 7, 'Προσληφθείσα ειδικός επιστήμονας με μεικτή κατάσταση πρόσβασης σε μαθήματα.', 'specialist', '2026-01-22 14:00:00', '2026-04-24 13:45:00');

INSERT INTO user_roles (user_id, role_id) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 3),
(5, 4),
(6, 4),
(7, 4),
(8, 5),
(9, 5);

INSERT INTO schools (id, name, description) VALUES
(1, 'Σχολή Μηχανικής και Τεχνολογίας', 'Προγράμματα μηχανικής, πληροφορικής και εφαρμοσμένων τεχνολογιών.'),
(2, 'Σχολή Ανθρωπιστικών και Κοινωνικών Επιστημών', 'Μαθήματα γλωσσών, επικοινωνίας και παιδαγωγικών προσεγγίσεων.'),
(3, 'Σχολή Επιστημών Υγείας', 'Κλινικά και εφαρμοσμένα μαθήματα στον χώρο της υγείας.');

INSERT INTO departments (id, school_id, name, description) VALUES
(1, 1, 'Τμήμα Επιστήμης Υπολογιστών', 'Υποστήριξη προγραμματισμού, συστημάτων και εφαρμογών ιστού.'),
(2, 1, 'Τμήμα Ηλεκτρολόγων Μηχανικών', 'Εργαστηριακές και θεωρητικές αναθέσεις ψηφιακών συστημάτων.'),
(3, 1, 'Τμήμα Μηχανολόγων Μηχανικών', 'Μαθήματα σχεδίασης και τεχνικών εφαρμογών.'),
(4, 2, 'Τμήμα Γλωσσών και Φιλολογίας', 'Ανάθεση μαθημάτων ακαδημαϊκής αγγλικής γλώσσας.'),
(5, 3, 'Τμήμα Νοσηλευτικής', 'Κλινική υποστήριξη και βοηθητική διδασκαλία.'),
(6, 3, 'Τμήμα Επιστημών Αποκατάστασης', 'Μαθήματα αποκατάστασης και πρακτικής εξάσκησης.');

INSERT INTO courses (id, department_id, code, name, description, credits, semester) VALUES
(1, 1, 'CSE326', 'Web Engineering', 'Σχεδίαση και ανάπτυξη σύγχρονων web εφαρμογών.', 6, 6),
(2, 1, 'CSE301', 'Database Systems', 'Σχεδιασμός, υλοποίηση και διαχείριση βάσεων δεδομένων.', 6, 5),
(3, 2, 'ECE210', 'Digital Systems Laboratory', 'Εργαστηριακή πρακτική σε ψηφιακά κυκλώματα και συστήματα.', 5, 4),
(4, 3, 'MEC120', 'Technical Drawing', 'Εισαγωγή στην τεχνική σχεδίαση και εργαλεία CAD.', 4, 2),
(5, 4, 'LAN101', 'Academic English', 'Αγγλική γλώσσα για πανεπιστημιακές σπουδές και ακαδημαϊκή γραφή.', 4, 1),
(6, 5, 'NUR201', 'Clinical Practice Support', 'Υποστήριξη κλινικών εργαστηρίων και προσομοιώσεων.', 5, 4),
(7, 6, 'PTH220', 'Rehabilitation Methods', 'Εισαγωγή σε μεθόδους αποκατάστασης και κλινική εξάσκηση.', 5, 5),
(8, 1, 'CSE420', 'Software Quality Assurance', 'Μεθοδολογίες ποιοτικού ελέγχου λογισμικού και testing.', 6, 7);

INSERT INTO recruitment_periods (id, name, start_date, end_date, status, description) VALUES
(1, 'Εαρινή Περίοδος 2026', '2026-04-01', '2026-05-31', 'active', 'Ανοιχτή περίοδος υποβολής αιτήσεων για το εαρινό εξάμηνο 2026.'),
(2, 'Χειμερινή Περίοδος 2025', '2025-10-01', '2025-12-31', 'closed', 'Ολοκληρωμένη περίοδος προσλήψεων για το χειμερινό εξάμηνο 2025.'),
(3, 'Φθινοπωρινός Προγραμματισμός 2026', '2026-09-01', '2026-10-31', 'planning', 'Προγραμματισμός επόμενου κύκλου ανακοινώσεων.');

INSERT INTO job_announcements (
    id, period_id, school_id, department_id, course_id, title, description, requirements, number_of_positions, status, created_at, updated_at
) VALUES
(1, 1, 1, 1, 1, 'Web Engineering Instructor', 'Διδασκαλία και εργαστηριακή υποστήριξη στο μάθημα CSE326.', 'Πτυχίο ή μεταπτυχιακό στην Πληροφορική και εμπειρία σε PHP/JavaScript.', 1, 'published', '2026-04-01 09:00:00', '2026-04-01 09:00:00'),
(2, 1, 1, 1, 2, 'Database Systems Specialist', 'Υποστήριξη διαλέξεων και εργαστηρίων βάσεων δεδομένων.', 'Γνώση SQL, MySQL/MariaDB και πρακτική εμπειρία σε data modeling.', 2, 'published', '2026-04-02 10:00:00', '2026-04-02 10:00:00'),
(3, 1, 1, 2, 3, 'Digital Systems Lab Tutor', 'Ενίσχυση εργαστηριακών ασκήσεων για το ECE210.', 'Εμπειρία σε εργαστηριακά setups και ψηφιακά συστήματα.', 1, 'published', '2026-04-03 11:00:00', '2026-04-03 11:00:00'),
(4, 2, 2, 4, 5, 'Academic English Support Lecturer', 'Υποστήριξη φοιτητών σε ακαδημαϊκή αγγλική γραφή.', 'Πτυχίο στην Αγγλική Φιλολογία ή σχετικό αντικείμενο.', 1, 'closed', '2025-10-02 09:15:00', '2025-12-15 16:00:00'),
(5, 3, 1, 1, 8, 'Software Quality Mentor', 'Προγραμματισμένη ανακοίνωση για υποστήριξη στο CSE420.', 'Εμπειρία σε testing, QA και automation.', 1, 'draft', '2026-04-10 12:00:00', '2026-04-10 12:00:00');

INSERT INTO application_evaluators (announcement_id, evaluator_id, created_at) VALUES
(1, 3, '2026-04-01 10:00:00'),
(1, 4, '2026-04-01 10:05:00'),
(2, 3, '2026-04-02 10:30:00'),
(3, 4, '2026-04-03 11:30:00'),
(4, 3, '2025-10-03 12:00:00');

INSERT INTO candidate_applications (
    id, announcement_id, candidate_id, status, progress, submitted_at, reviewed_at, reviewed_by, feedback,
    app_phone, app_degree, app_institution, app_specialization, app_experience, app_summary, app_declared,
    created_at, updated_at
) VALUES
(1, 1, 5, 'accepted', 100, '2026-04-05 09:30:00', '2026-04-14 15:00:00', 2, 'Η αίτηση εγκρίθηκε και ο υποψήφιος προχωρά σε onboarding.', '+35799555555', 'MSc', 'Tsinghua University', 'Web Engineering', 4, 'Εμπειρία σε PHP, frontend integration και πανεπιστημιακά projects.', 1, '2026-04-05 09:00:00', '2026-04-14 15:00:00'),
(2, 2, 6, 'submitted', 100, '2026-04-18 11:10:00', NULL, NULL, NULL, '+35799666666', 'MSc', 'University of Cyprus', 'Data Systems', 5, 'Ισχυρό υπόβαθρο σε SQL, data modeling και validation.', 1, '2026-04-18 10:45:00', '2026-04-18 11:10:00'),
(3, 3, 7, 'under_review', 100, '2026-04-20 14:20:00', '2026-04-22 09:00:00', 4, 'Η αίτηση βρίσκεται στο στάδιο επιτροπής.', '+35799777777', 'MSc', 'University of Patras', 'Embedded Systems', 3, 'Εργαστηριακή εμπειρία σε digital systems και tutoring.', 1, '2026-04-20 13:50:00', '2026-04-22 09:00:00'),
(4, 4, 5, 'draft', 35, NULL, NULL, NULL, NULL, '+35799555555', 'MSc', 'Tsinghua University', 'Academic Writing Support', 4, 'Πρόχειρη αίτηση για προηγούμενη περίοδο.', 0, '2025-11-20 10:00:00', '2025-11-20 10:30:00'),
(5, 1, 6, 'rejected', 100, '2026-04-10 16:00:00', '2026-04-16 12:30:00', 1, 'Υπήρχαν ισχυρότεροι υποψήφιοι για τη θέση.', '+35799666666', 'MSc', 'University of Cyprus', 'Database Systems', 5, 'Εφαρμοσμένη εμπειρία σε queries, normalization και ETL.', 1, '2026-04-10 15:20:00', '2026-04-16 12:30:00');

INSERT INTO application_form_fields (announcement_id, field_name, field_label, field_type, is_required, order_number) VALUES
(1, 'full_name', 'Ονοματεπώνυμο', 'text', 1, 1),
(1, 'email', 'Email', 'email', 1, 2),
(1, 'phone', 'Τηλέφωνο', 'phone', 1, 3),
(1, 'education', 'Τίτλος Σπουδών', 'text', 1, 4),
(1, 'experience', 'Εμπειρία', 'text', 1, 5),
(1, 'motivation', 'Σύντομη Περιγραφή', 'textarea', 1, 6),
(2, 'full_name', 'Ονοματεπώνυμο', 'text', 1, 1),
(2, 'email', 'Email', 'email', 1, 2),
(2, 'phone', 'Τηλέφωνο', 'phone', 1, 3),
(2, 'education', 'Τίτλος Σπουδών', 'text', 1, 4),
(2, 'experience', 'Εμπειρία', 'text', 1, 5),
(2, 'motivation', 'Σύντομη Περιγραφή', 'textarea', 1, 6),
(3, 'full_name', 'Ονοματεπώνυμο', 'text', 1, 1),
(3, 'email', 'Email', 'email', 1, 2),
(3, 'phone', 'Τηλέφωνο', 'phone', 1, 3),
(3, 'education', 'Τίτλος Σπουδών', 'text', 1, 4),
(3, 'experience', 'Εμπειρία', 'text', 1, 5),
(3, 'motivation', 'Σύντομη Περιγραφή', 'textarea', 1, 6);

INSERT INTO application_responses (application_id, field_id, response_value, file_path, created_at, updated_at) VALUES
(1, 1, 'Μάριος Σιήττας', NULL, '2026-04-05 09:05:00', '2026-04-05 09:05:00'),
(1, 2, 'candidate1@example.com', NULL, '2026-04-05 09:05:00', '2026-04-05 09:05:00'),
(1, 3, '+35799555555', NULL, '2026-04-05 09:05:00', '2026-04-05 09:05:00'),
(1, 4, 'MSc in Web Engineering', NULL, '2026-04-05 09:05:00', '2026-04-05 09:05:00'),
(1, 5, '4 years', NULL, '2026-04-05 09:05:00', '2026-04-05 09:05:00'),
(1, 6, 'Εμπειρία σε ανάπτυξη web εφαρμογών και υποστήριξη ομάδων φοιτητών.', NULL, '2026-04-05 09:05:00', '2026-04-05 09:05:00'),
(2, 7, 'Χρίστος Παύλου', NULL, '2026-04-18 11:00:00', '2026-04-18 11:00:00'),
(2, 8, 'candidate2@example.com', NULL, '2026-04-18 11:00:00', '2026-04-18 11:00:00'),
(2, 9, '+35799666666', NULL, '2026-04-18 11:00:00', '2026-04-18 11:00:00'),
(2, 10, 'MSc in Data Systems', NULL, '2026-04-18 11:00:00', '2026-04-18 11:00:00'),
(2, 11, '5 years', NULL, '2026-04-18 11:00:00', '2026-04-18 11:00:00'),
(2, 12, 'Εστιάζω στην ποιοτική διδασκαλία βάσεων δεδομένων και πρακτικών εργαστηρίων.', NULL, '2026-04-18 11:00:00', '2026-04-18 11:00:00');

INSERT INTO themes (id, name, logo_url, primary_color, secondary_color, description, is_active, created_at, updated_at) VALUES
(1, 'Default TEPAK', 'assets/images/site-logo.png', '#1d4ed8', '#0f172a', 'Προεπιλεγμένο theme με branding ΤΕΠΑΚ.', 1, '2026-01-01 08:00:00', '2026-04-01 08:00:00');

INSERT INTO system_settings (setting_key, setting_value, description, created_at, updated_at) VALUES
('app_name', 'CareerTrack EE Portal', 'Όνομα εφαρμογής', '2026-01-01 08:00:00', '2026-04-01 08:00:00'),
('app_slogan', 'Διαχείριση Ειδικών Επιστημόνων ΤΕΠΑΚ', 'Υπότιτλος εφαρμογής', '2026-01-01 08:00:00', '2026-04-01 08:00:00'),
('app_description', 'Ολοκληρωμένη διαχείριση recruitment και enrollment για ειδικούς επιστήμονες.', 'Περιγραφή εφαρμογής', '2026-01-01 08:00:00', '2026-04-01 08:00:00'),
('admin_email', 'admin@tepak.cy', 'Email διαχειριστή', '2026-01-01 08:00:00', '2026-04-01 08:00:00'),
('support_phone', '+35725002400', 'Τηλέφωνο υποστήριξης', '2026-01-01 08:00:00', '2026-04-01 08:00:00'),
('lms_portal_url', 'https://moodle.tepak.cy', 'Προτεινόμενο URL σύνδεσης LMS', '2026-01-01 08:00:00', '2026-04-01 08:00:00'),
('maintenance_mode', '0', 'Ενεργοποίηση λειτουργίας συντήρησης', '2026-01-01 08:00:00', '2026-04-01 08:00:00');

INSERT INTO lms_connections (id, name, api_url, api_key, api_secret, status, description, created_at, updated_at) VALUES
(1, 'Local Moodle Placeholder', 'https://moodle.tepak.cy/webservice/rest/server.php', 'demo-key', 'demo-secret', 'testing', 'Τοπική placeholder καταχώρηση χωρίς πραγματική σύνδεση API.', '2026-01-05 09:00:00', '2026-04-01 09:00:00');

INSERT INTO lms_users (user_id, lms_connection_id, lms_user_id, lms_username, access_status, created_at, updated_at) VALUES
(8, 1, 'mdl-1008', 'specialist1', 'active', '2026-03-28 10:00:00', '2026-04-02 09:00:00'),
(9, 1, 'mdl-1009', 'specialist2', 'pending', '2026-04-05 12:00:00', '2026-04-20 10:00:00');

INSERT INTO specialist_enrollments (id, user_id, course_id, lms_course_id, access_status, enrolled_at, created_at, updated_at) VALUES
(1, 8, 1, 'mdl-course-326', 'active', '2026-03-28 10:30:00', '2026-03-28 10:00:00', '2026-04-02 09:00:00'),
(2, 8, 2, 'mdl-course-301', 'active', '2026-04-02 09:30:00', '2026-04-01 08:00:00', '2026-04-02 09:30:00'),
(3, 9, 3, 'mdl-course-210', 'pending', NULL, '2026-04-05 12:00:00', '2026-04-20 10:00:00'),
(4, 9, 5, 'mdl-course-101', 'inactive', '2026-01-12 09:00:00', '2026-01-10 09:00:00', '2026-03-18 08:45:00'),
(5, 9, 8, 'mdl-course-420', 'active', '2026-04-08 11:15:00', '2026-04-07 15:00:00', '2026-04-08 11:15:00');

INSERT INTO enrollment_logs (user_id, action, action_type, target_id, target_type, details, status, error_message, created_at, performed_by) VALUES
(8, 'Access activated for CSE326', 'manual_enroll', 1, 'specialist_enrollment', '{"course_code":"CSE326","new_status":"active"}', 'success', NULL, '2026-03-28 10:30:00', 2),
(8, 'Full sync executed locally', 'full_sync', NULL, 'sync_schedule', '{"source":"manual_full_sync"}', 'success', NULL, '2026-04-12 09:00:00', 1),
(9, 'Pending access check completed', 'status_check', 3, 'specialist_enrollment', '{"course_code":"ECE210","current_status":"pending"}', 'success', NULL, '2026-04-20 10:15:00', 2),
(9, 'Access deactivated for LAN101', 'manual_unenroll', 4, 'specialist_enrollment', '{"course_code":"LAN101","new_status":"inactive"}', 'success', NULL, '2026-03-18 08:45:00', 2),
(9, 'Auto sync waiting for approval', 'auto_sync', 3, 'specialist_enrollment', '{"course_code":"ECE210","queue":"pending"}', 'pending', NULL, '2026-04-21 08:00:00', 1);

INSERT INTO sync_schedules (id, name, sync_type, is_enabled, frequency_minutes, last_sync_at, next_sync_at, created_at, updated_at) VALUES
(1, 'Automatic LMS Sync', 'auto_sync', 1, 120, '2026-04-24 08:00:00', '2026-04-24 10:00:00', '2026-01-05 09:00:00', '2026-04-24 08:00:00'),
(2, 'Manual Full Sync', 'full_sync', 0, 1440, '2026-04-12 09:00:00', '2026-04-25 09:00:00', '2026-01-05 09:00:00', '2026-04-12 09:00:00');

INSERT INTO notifications (user_id, title, message, notification_type, related_entity_id, related_entity_type, is_read, read_at, created_at) VALUES
(1, 'Νέα αίτηση: Database Systems Specialist', 'Ο χρήστης Χρίστος Παύλου υπέβαλε αίτηση για τη θέση "Database Systems Specialist".', 'new_application', 2, 'candidate_application', 0, NULL, '2026-04-18 11:11:00'),
(2, 'Εκκρεμής πρόσβαση LMS', 'Η Κατερίνα Θεοδώρου έχει pending πρόσβαση για το μάθημα ECE210.', 'enrollment', 3, 'specialist_enrollment', 0, NULL, '2026-04-20 10:20:00'),
(1, 'Σύστημα συγχρονισμού ενεργό', 'Ο αυτόματος συγχρονισμός είναι ενεργός και προγραμματισμένος κάθε 120 λεπτά.', 'system_update', 1, 'sync_schedule', 1, '2026-04-24 08:05:00', '2026-04-24 08:00:00');

INSERT INTO audit_logs (user_id, action, entity_type, entity_id, changes, ip_address, user_agent, created_at) VALUES
(1, 'Updated recruitment configuration', 'job_announcement', 2, '{"status":"published"}', '127.0.0.1', 'Mozilla/5.0', '2026-04-02 10:05:00'),
(2, 'Ran LMS status check', 'specialist_enrollment', 3, '{"status":"pending"}', '127.0.0.1', 'Mozilla/5.0', '2026-04-20 10:15:00'),
(5, 'Submitted candidate application', 'candidate_application', 1, '{"status":"accepted"}', '127.0.0.1', 'Mozilla/5.0', '2026-04-05 09:30:00');

SET FOREIGN_KEY_CHECKS = 1;

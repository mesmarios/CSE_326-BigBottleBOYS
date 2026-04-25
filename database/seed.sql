USE bigbrothers;

INSERT INTO users (username, email, password_hash, role) VALUES
('admin_user', 'admin@example.com', '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.', 'admin'),
('candidate_one', 'candidate1@example.com', '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.', 'user'),
('candidate_two', 'candidate2@example.com', '$2y$12$0x2K4Ff6EJb7pMU6hMx85u19XZXrHoawsVVYovOIqaxCtwZpbU4l.', 'user');

INSERT INTO job_announcements (user_id, title, description, status) VALUES
(1, 'Database Teaching Assistant', 'Support for the database laboratory and weekly exercises.', 'open'),
(1, 'Web Development Instructor', 'Teaching support for PHP, HTML, and secure backend practices.', 'open'),
(1, 'Software Engineering Mentor', 'Guidance for student teams working on semester projects.', 'open'),
(1, 'Research Assistant in Data Systems', 'Participation in academic research related to data systems.', 'closed'),
(1, 'Systems Support Specialist', 'Operational support for internal academic software systems.', 'open');

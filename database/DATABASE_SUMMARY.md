# Database Summary

Σύνοψη της βάσης δεδομένων του project `CSE_326 BigBottleBOYS`.

## Βασικά Στοιχεία

- Όνομα βάσης: `bigbrothers`
- Schema file: `database/database.sql`
- Seed file: `database/seed.sql`
- Connection file: `database/db.php`

## Κοινό Password Demo Χρηστών

Όλοι οι seeded χρήστες έχουν το ίδιο password:

- `Demo1234!`

## Ρόλοι Χρηστών

Ο ρόλος αποθηκεύεται στη στήλη `users.role` ως:

- `admin`
- `hr`
- `evaluator`
- `candidate`
- `ee_hired`

## Redirect μετά το Login

| Role | Redirect |
|---|---|
| `admin` | `module-select.php` |
| `hr` | `module-select.php` |
| `evaluator` | `modules/recruitmentModule/index.php` |
| `candidate` | `modules/recruitmentModule/index.php` |
| `ee_hired` | `enrollment/dashboard.php` |

## Πίνακες Βάσης

Η βάση περιέχει `16` tables.

### Core Users

| Πίνακας | Περιγραφή |
|---|---|
| `users` | Όλοι οι χρήστες της εφαρμογής |

### Academic Structure

| Πίνακας | Περιγραφή |
|---|---|
| `schools` | Σχολές |
| `departments` | Τμήματα ανά σχολή |
| `courses` | Μαθήματα ανά τμήμα |

### Recruitment

| Πίνακας | Περιγραφή |
|---|---|
| `recruitment_periods` | Περίοδοι αιτήσεων |
| `job_announcements` | Αγγελίες / προκηρύξεις θέσεων |
| `application_evaluators` | Ανάθεση αξιολογητών σε αγγελίες |
| `candidate_applications` | Αιτήσεις υποψηφίων |
| `application_form_fields` | Δυναμικά πεδία αίτησης |
| `application_responses` | Απαντήσεις σε δυναμικά πεδία |

### Enrollment / LMS

| Πίνακας | Περιγραφή |
|---|---|
| `lms_access` | Πρόσβαση χρήστη σε μάθημα |
| `specialist_enrollments` | Enrollment records για ΕΕ |
| `enrollment_logs` | Log ενεργειών enrollment / sync |

### System

| Πίνακας | Περιγραφή |
|---|---|
| `system_settings` | System settings και branding |
| `notifications` | Ειδοποιήσεις χρηστών |
| `audit_logs` | Audit log ενεργειών |

## Κύριες Σχέσεις

- `departments.school_id -> schools.id`
- `courses.department_id -> departments.id`
- `job_announcements.period_id -> recruitment_periods.id`
- `job_announcements.school_id -> schools.id`
- `job_announcements.department_id -> departments.id`
- `job_announcements.course_id -> courses.id`
- `application_evaluators.announcement_id -> job_announcements.id`
- `application_evaluators.evaluator_id -> users.id`
- `candidate_applications.announcement_id -> job_announcements.id`
- `candidate_applications.candidate_id -> users.id`
- `candidate_applications.reviewed_by -> users.id`
- `application_responses.application_id -> candidate_applications.id`
- `application_responses.field_id -> application_form_fields.id`
- `lms_access.user_id -> users.id`
- `lms_access.course_id -> courses.id`
- `specialist_enrollments.user_id -> users.id`
- `specialist_enrollments.course_id -> courses.id`
- `notifications.user_id -> users.id`
- `audit_logs.user_id -> users.id`

## Seed Data Overview

### Demo Users

Το `seed.sql` δημιουργεί `20` χρήστες:

- `3 admin`
- `1 hr`
- `2 evaluator`
- `12 candidate`
- `2 ee_hired`

### Ενδεικτικά Demo Emails

| Role | Email |
|---|---|
| `admin` | `admin@tepak.cy` |
| `admin` | `admin2@tepak.cy` |
| `admin` | `admin3@tepak.cy` |
| `hr` | `hr@tepak.cy` |
| `evaluator` | `eval.giorgos@tepak.cy` |
| `evaluator` | `eval.christos@tepak.cy` |
| `candidate` | `nikos.andreou@student.tepak.cy` |
| `candidate` | `maria.christou@student.tepak.cy` |
| `candidate` | `panagiotis.ioannou@student.tepak.cy` |
| `ee_hired` | `sofia.mihail@tepak.cy` |
| `ee_hired` | `andreas.petrou@tepak.cy` |

### Ακαδημαϊκά Δεδομένα

Το seed περιλαμβάνει:

- `3` σχολές
- `5` τμήματα
- `9` μαθήματα

### Recruitment Δεδομένα

Το seed περιλαμβάνει:

- `3` recruitment periods
- `15` job announcements
- assignments evaluators
- αιτήσεις σε καταστάσεις:
  - `draft`
  - `submitted`
  - `under_review`
  - `accepted`
  - `rejected`
  - `withdrawn`

### Enrollment Δεδομένα

Το seed περιλαμβάνει:

- active / inactive records στο `lms_access`
- αντίστοιχα records στο `specialist_enrollments`
- sync settings στο `system_settings`

## System Settings που υπάρχουν στο Seed

| Key | Περιγραφή |
|---|---|
| `app_name` | Όνομα εφαρμογής |
| `institution_name` | Όνομα ιδρύματος |
| `moodle_url` | Moodle base URL |
| `moodle_token` | Moodle token placeholder |
| `lms_auto_sync_enabled` | Auto sync on/off |
| `lms_last_sync_at` | Τελευταίος sync χρόνος |
| `lms_last_sync_log` | Τελευταίο sync log |
| `maintenance_mode` | Maintenance mode flag |

## Import Οδηγίες

### Με phpMyAdmin

1. Δημιούργησε ή κάνε import τη βάση `bigbrothers`
2. Κάνε import το `database/database.sql`
3. Κάνε import το `database/seed.sql`

### Με terminal

```bash
mysql -u root < database/database.sql
mysql -u root bigbrothers < database/seed.sql
```

## Σημειώσεις

- Το `seed.sql` μηδενίζει δεδομένα πριν επανεισάγει demo περιεχόμενο.
- Η εφαρμογή βασίζεται σε κοινή βάση και στα 3 modules.
- Οι demo χρήστες χρησιμοποιούν όλοι το ίδιο password για εύκολη παρουσίαση/demo.

## Ομάδα

- Μάριος Σιήττας - Α.Φ.Τ. 27432
- Μάριος Μεσαρίτης - Α.Φ.Τ. 27818
- Μιχαλής Τσαδιώτης - Α.Φ.Τ. 28053

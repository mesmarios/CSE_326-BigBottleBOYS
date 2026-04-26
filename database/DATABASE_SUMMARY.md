# SPECIALIST MANAGEMENT SYSTEM — DATABASE SUMMARY

Σύστημα Διαχείρισης Ειδικών Επιστημόνων (ΕΕ) — ΤΕΠΑΚ

---


Password of users: Demo1234!

## Αρχεία Βάσης Δεδομένων

| Αρχείο | Περιεχόμενο |
|--------|-------------|
| `database/database.sql` | Πλήρες schema — δημιουργία DB + 19 tables + indexes |
| `database/seed.sql` | Δεδομένα δοκιμής — 9 χρήστες, μαθήματα, αιτήσεις, κ.λπ. |

**Όνομα Βάσης:** `bigbrothers`  
**Σύνδεση:** `c:\xampp\htdocs\CSE_326-BigBottleBOYS\database\db.php` (PDO)

---

## Εισαγωγή σε phpMyAdmin

1. Άνοιξε `http://localhost/phpmyadmin`
2. Αν υπάρχει ήδη η βάση `bigbrothers`, διέγραψέ την (Drop)
3. **Import** → επίλεξε `database/database.sql` → Go  
   *(δημιουργεί τη βάση και όλους τους πίνακες)*
4. **Import** → επίλεξε `database/seed.sql` → Go  
   *(εισάγει τα δεδομένα δοκιμής)*

---

## Ρόλοι Χρηστών & Πρόσβαση

Ο ρόλος αποθηκεύεται ως `ENUM` στη στήλη `users.role`. Δεν υπάρχουν ξεχωριστοί πίνακες roles/user_roles.

| Ρόλος | Πού ανακατευθύνεται μετά το login | Πρόσβαση |
|-------|-----------------------------------|----------|
| `admin` | `module-select.php` | Admin Module + Enrollment Module |
| `hr` | `module-select.php` | Recruitment Module + Enrollment Module |
| `evaluator` | `modules/recruitmentModule/index.php` | Recruitment Module (αξιολόγηση μόνο) |
| `candidate` | `modules/recruitmentModule/index.php` | Recruitment Module (υποβολή αιτήσεων) |
| `ee_hired` | `enrollment/dashboard.php` | Enrollment Module (μόνο δική τους LMS πρόσβαση) |

---

## Πίνακες (19 συνολικά)

### Χρήστες
| Πίνακας | Περιγραφή |
|---------|-----------|
| `users` | Όλοι οι χρήστες — role ENUM('admin','hr','evaluator','candidate','ee_hired') |

### Ακαδημαϊκή Δομή
| Πίνακας | Περιγραφή |
|---------|-----------|
| `schools` | Σχολές ΤΕΠΑΚ |
| `departments` | Τμήματα (FK → schools) |
| `courses` | Μαθήματα με κωδικό, credits, semester (FK → departments) |

### Recruitment Module
| Πίνακας | Περιγραφή |
|---------|-----------|
| `recruitment_periods` | Περίοδοι προσλήψεων (planning/active/closed/archived) |
| `job_announcements` | Προκηρύξεις θέσεων (FK → periods, schools, departments, courses) |
| `application_evaluators` | Αξιολογητές ανά προκήρυξη |
| `candidate_applications` | Αιτήσεις υποψηφίων (draft→submitted→under_review→accepted/rejected/withdrawn) |
| `application_form_fields` | Δυναμικά πεδία φόρμας ανά προκήρυξη |
| `application_responses` | Απαντήσεις υποψηφίων σε κάθε πεδίο |

### Enrollment / LMS Module
| Πίνακας | Περιγραφή |
|---------|-----------|
| `lms_connections` | Ρυθμίσεις Moodle server (api_url, api_key, status) |
| `lms_access` | Πρόσβαση ανά χρήστη/μάθημα (active/inactive), UNIQUE(user_id, course_id) |
| `specialist_enrollments` | Λεπτομερής εγγραφή ΕΕ σε Moodle (με lms_course_id) |
| `sync_schedules` | Χρονοδιαγράμματα αυτόματου sync |
| `enrollment_logs` | Log κάθε ενέργειας enrollment |

### Σύστημα
| Πίνακας | Περιγραφή |
|---------|-----------|
| `system_settings` | Κλειδί/τιμή ρυθμίσεων (app_name, moodle_url, maintenance_mode, κ.λπ.) |
| `themes` | Θέματα εμφάνισης (logo, primary_color, secondary_color) |
| `notifications` | Ειδοποιήσεις ανά χρήστη |
| `audit_logs` | Πλήρες ιστορικό ενεργειών (user_id, action, entity, ip, user_agent) |

---

## Δεδομένα Δοκιμής (seed.sql)

### Χρήστες (9 σύνολο) — Κωδικός: `Demo1234!`

| Ρόλος | Email | Όνομα |
|-------|-------|-------|
| `admin` | admin@tepak.cy | Μάριος Μεσαρίτης |
| `hr` | hr@tepak.cy | Ελένη Παπαδοπούλου |
| `evaluator` | evaluator1@tepak.cy | Κώστας Νικολάου |
| `evaluator` | evaluator2@tepak.cy | Άννα Γεωργίου |
| `candidate` | candidate1@example.com | Γιώργης Παπαδάκης |
| `candidate` | candidate2@example.com | Μαρία Σταύρου |
| `candidate` | candidate3@example.com | Νίκος Χριστοδούλου |
| `ee_hired` | ee1@tepak.cy | Σοφία Μιχαήλ |
| `ee_hired` | ee2@tepak.cy | Ανδρέας Πέτρου |

### Ακαδημαϊκά Δεδομένα
- **2 Σχολές**: Μηχανικής & Τεχνολογίας, Επιστημών
- **3 Τμήματα**: Πληροφορικής, Ηλεκτρολόγων, Μαθηματικών
- **6 Μαθήματα**: CSE326, CSE315, CSE201, EEE301, MATH101, MATH202

### Recruitment
- **2 Περίοδοι προσλήψεων**: Περίοδος 2026 (active), Περίοδος 2025 (closed)
- **6 Προκηρύξεις** κατανεμημένες στις δύο περιόδους
- **5 Αιτήσεις** σε διάφορα στάδια (submitted, under_review, accepted, rejected)

### LMS Access
| Χρήστης | Μάθημα | Κατάσταση |
|---------|--------|-----------|
| Σοφία Μιχαήλ | CSE326 | `active` |
| Ανδρέας Πέτρου | CSE315 | `inactive` |

---

## Αρχιτεκτονική (ER Διάγραμμα)

```
┌──────────────────────────────────┐
│             USERS                │
│  id · username · email           │
│  role ENUM(admin|hr|evaluator|   │
│         candidate|ee_hired)      │
│  password_hash · first_name ...  │
└──┬───────────────────────────────┘
   │
   │ (candidate_id)           (evaluator_id)
   │                               │
   ▼                               │
CANDIDATE_APPLICATIONS ◄───────────┘
   │ (announcement_id)     APPLICATION_EVALUATORS
   │
   ▼
JOB_ANNOUNCEMENTS ──── APPLICATION_FORM_FIELDS
   │ (period_id)                   │
   │ (school_id)         APPLICATION_RESPONSES
   │ (department_id)
   │ (course_id)
   │
   ├──► RECRUITMENT_PERIODS
   ├──► SCHOOLS
   ├──► DEPARTMENTS
   └──► COURSES ◄──── LMS_ACCESS ◄──── USERS (ee_hired)
                      SPECIALIST_ENROLLMENTS

SYSTEM_SETTINGS   THEMES   NOTIFICATIONS   AUDIT_LOGS
LMS_CONNECTIONS   SYNC_SCHEDULES   ENROLLMENT_LOGS
```

---

## Ασφάλεια

- **Password hashing**: `password_hash()` bcrypt cost 12
- **SQL Injection**: PDO prepared statements παντού
- **XSS**: `htmlspecialchars()` σε όλα τα output
- **CSRF**: Προτείνεται token σε forms
- **Audit log**: Κάθε ενέργεια καταγράφεται με IP + user_agent
- **Maintenance mode**: Dual mechanism — `system_settings.maintenance_mode` (DB) + `maintenance.lock` (file fallback)

---

## Δομή Φακέλων (σχετικά με βάση)

```
c:\xampp\htdocs\CSE_326-BigBottleBOYS\
├── database/
│   ├── database.sql          ← Schema (τρέξε πρώτο)
│   ├── seed.sql              ← Δεδομένα (τρέξε δεύτερο)
│   ├── db.php                ← PDO σύνδεση
│   └── DATABASE_SUMMARY.md  ← Αυτό το αρχείο
├── login.php
├── register.php
├── module-select.php         ← Επιλογή ενότητας (admin/hr)
├── modules/
│   ├── admin/                ← Admin Module
│   └── recruitmentModule/   ← Recruitment Module
└── enrollment/               ← Enrollment Module
    ├── dashboard.php
    ├── lms-sync.php
    ├── full-sync.php
    └── report.php
```

---

**Τελευταία ενημέρωση**: Απρίλιος 2026  
**Βάση Δεδομένων**: `bigbrothers`  
**Stack**: PHP + MariaDB (XAMPP) · AdminLTE v4 · Bootstrap 5.3.7

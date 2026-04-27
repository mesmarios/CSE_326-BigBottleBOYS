# File Index

Συνοπτικός οδηγός των βασικών αρχείων του project και του ρόλου τους.

## Κύρια Τεκμηρίωση

| Αρχείο | Ρόλος |
|---|---|
| `README.md` | Κεντρική περιγραφή repository, ομάδα, setup, demo χρήστες |
| `database/DATABASE_SUMMARY.md` | Σύνοψη βάσης δεδομένων, πίνακες, seed data |
| `database/FILE_INDEX.md` | Αυτό το αρχείο |

## Database

| Αρχείο | Ρόλος |
|---|---|
| `database/database.sql` | Πλήρες schema της βάσης `bigbrothers` |
| `database/seed.sql` | Demo δεδομένα για χρήστες, μαθήματα, αιτήσεις, enrollment |
| `database/db.php` | PDO σύνδεση με τη βάση |

## Root / Entry Pages

| Αρχείο | Ρόλος |
|---|---|
| `index.php` | Landing page |
| `login.php` | Root login page |
| `register.php` | Root register page |
| `logout.php` | Logout |
| `module-select.php` | Επιλογή module για `admin` και `hr` |
| `maintenance.php` | Maintenance page |

## Auth

| Αρχείο | Ρόλος |
|---|---|
| `auth/login.php` | Auth route login |
| `auth/register.php` | Auth route register |
| `auth/logout.php` | Auth route logout |

## Shared Includes

| Αρχείο | Ρόλος |
|---|---|
| `includes/layout.php` | Κοινό layout για recruitment πλευρά |
| `includes/header.php` | Κοινό header recruitment |
| `includes/nav.php` | Κοινό sidebar/navigation recruitment |
| `includes/footer.php` | Shared footer scripts |
| `includes/functions.php` | Γενικές helper συναρτήσεις |
| `includes/config.php` | App constants / legacy config |
| `includes/db.php` | Wrapper include για DB |
| `includes/admin-guard.php` | Guard για admin pages |
| `includes/admin-branding.php` | Branding context για logo/favicon/app name |
| `includes/admin-footer.php` | Shared admin footer |
| `includes/maintenance-mode.php` | Maintenance mode logic |
| `includes/database-helper.php` | Helper queries / DB operations |

## Admin Module

| Αρχείο | Ρόλος |
|---|---|
| `modules/admin/index.php` | Admin dashboard |
| `modules/admin/manage_users.php` | Διαχείριση χρηστών και ρόλων |
| `modules/admin/manage_recruitment.php` | Διαχείριση recruitment δεδομένων |
| `modules/admin/configure_system.php` | Branding, maintenance, backup |
| `modules/admin/report.php` | Reports και charts για recruitment/admin |
| `modules/admin/my_profile.php` | Προφίλ admin και αλλαγή password |

## Recruitment Module

| Αρχείο | Ρόλος |
|---|---|
| `modules/recruitmentModule/index.php` | Recruitment dashboard |
| `modules/recruitmentModule/myprofile.php` | Προφίλ χρήστη recruitment |
| `modules/recruitmentModule/myapplication.php` | Δημιουργία και διαχείριση αιτήσεων |
| `modules/recruitmentModule/applicationstatus.php` | Παρακολούθηση κατάστασης αιτήσεων |

## Enrollment Module

| Αρχείο | Ρόλος |
|---|---|
| `enrollment/dashboard.php` | Enrollment dashboard |
| `enrollment/lms-sync.php` | LMS access management |
| `enrollment/full-sync.php` | Manual / auto sync controls |
| `enrollment/report.php` | Enrollment statistics και export |
| `enrollment/includes/enrollment-guard.php` | Role guard για enrollment |
| `enrollment/includes/header.php` | Enrollment header |
| `enrollment/includes/sidebar.php` | Enrollment sidebar |

## API Endpoints

| Αρχείο | Ρόλος |
|---|---|
| `api/applications.php` | API για applications / responses |
| `api/download.php` | File downloads |
| `api/enrollment.php` | Enrollment / LMS actions API |
| `api/notifications.php` | Notifications API |
| `api/profile.php` | Profile updates / password / avatar |

## Assets

| Φάκελος | Ρόλος |
|---|---|
| `assets/css/` | Global styles, admin styles, UI utilities |
| `assets/js/` | Shared JavaScript |
| `assets/images/` | Logo, favicon, avatars, static images |
| `recruitment/assets/` | Recruitment-specific CSS/JS/images |

## Uploads

| Φάκελος | Ρόλος |
|---|---|
| `uploads/applications/` | Uploaded application files |
| `uploads/profile_pics/` | Profile pictures |

## Legacy / Course-required Files

| Αρχείο | Ρόλος |
|---|---|
| `modules/dashboard.php` | Legacy/required course file |
| `modules/list.php` | Legacy/required course file |

## Demo Credentials

Όλοι οι seeded χρήστες έχουν κοινό password:

- `Demo1234!`

Παραδείγματα:

- `admin@tepak.cy`
- `hr@tepak.cy`
- `eval.giorgos@tepak.cy`
- `nikos.andreou@student.tepak.cy`
- `sofia.mihail@tepak.cy`

## Ομάδα

- Μάριος Σιήττας - Α.Φ.Τ. 27432
- Μάριος Μεσαρίτης - Α.Φ.Τ. 27818
- Μιχαλής Τσαδιώτης - Α.Φ.Τ. 28053

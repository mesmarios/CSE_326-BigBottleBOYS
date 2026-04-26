# CSE_326 BigBottleBOYS

Διαδικτυακή εφαρμογή διαχείρισης Ειδικών Επιστημόνων (ΕΕ) για το ΤΕΠΑΚ, υλοποιημένη στο πλαίσιο του μαθήματος `CSE 326`.

Η εφαρμογή καλύπτει:
- `Admin Module`
- `Recruitment Module`
- `Enrollment Module`

## Ομάδα

- Μάριος Σιήττας - Α.Φ.Τ. 27432
- Μάριος Μεσαρίτης - Α.Φ.Τ. 27818
- Μιχαλής Τσαδιώτης - Α.Φ.Τ. 28053

## Περιγραφή Project

Το σύστημα υποστηρίζει:
- διαχείριση χρηστών και ρόλων
- διαχείριση προκηρύξεων, σχολών, τμημάτων, μαθημάτων και περιόδων αιτήσεων
- υποβολή και παρακολούθηση αιτήσεων υποψηφίων
- βασική διαχείριση enrollment/LMS access για προσληφθέντες ΕΕ
- dashboards και reports για admin και enrollment

## Modules

### 1. Admin Module
- `Dashboard`
- `Manage Users`
- `Manage Recruitment`
- `Configure System`
- `Report`
- `My Profile`

### 2. Recruitment Module
- `Dashboard`
- `My Profile`
- `My Applications`
- `Application Status`

### 3. Enrollment Module
- `Dashboard`
- `LMS Sync`
- `Full Sync`
- `Report`

## Τεχνολογίες

- `PHP`
- `MySQL / MariaDB`
- `HTML5`
- `CSS3`
- `JavaScript`
- `Bootstrap 5`
- `AdminLTE`
- `XAMPP`

## Δομή Repository

```text
CSE_326-BigBottleBOYS/
├── api/                     # API endpoints
├── assets/                  # CSS, JS, images
├── auth/                    # auth routes
├── database/                # schema, seed, DB docs
├── enrollment/              # Enrollment module
├── includes/                # shared guards, layout, helpers
├── modules/
│   ├── admin/               # Admin module
│   ├── recruitmentModule/   # Recruitment module
│   ├── dashboard.php        # legacy/required course file
│   └── list.php             # legacy/required course file
├── uploads/                 # uploaded files
├── index.php                # landing page
├── login.php                # root login
├── register.php             # root register
└── module-select.php        # module selection for admin/hr
```

## Database

- Όνομα βάσης: `bigbrothers`
- Schema file: `database/database.sql`
- Seed file: `database/seed.sql`
- PDO connection: `database/db.php`

## Εγκατάσταση

1. Τοποθέτησε το project μέσα στο `htdocs` του XAMPP.
2. Άνοιξε `phpMyAdmin`.
3. Κάνε import πρώτα το `database/database.sql`.
4. Κάνε import μετά το `database/seed.sql`.

Εναλλακτικά από terminal:

```bash
mysql -u root < database/database.sql
mysql -u root bigbrothers < database/seed.sql
```

## Εκτέλεση

Άνοιξε:

- `http://localhost/CSE_326-BigBottleBOYS/`

Χρήσιμα routes:

- `http://localhost/CSE_326-BigBottleBOYS/login.php`
- `http://localhost/CSE_326-BigBottleBOYS/register.php`
- `http://localhost/CSE_326-BigBottleBOYS/module-select.php`
- `http://localhost/CSE_326-BigBottleBOYS/modules/admin/index.php`
- `http://localhost/CSE_326-BigBottleBOYS/modules/recruitmentModule/index.php`
- `http://localhost/CSE_326-BigBottleBOYS/enrollment/dashboard.php`

## Demo Users

Όλοι οι seeded χρήστες έχουν το ίδιο password:

- `Demo1234!`

Ενδεικτικοί λογαριασμοί:

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

Για πλήρη λίστα demo λογαριασμών δες το `database/seed.sql`.

## Χρήσιμα Αρχεία Τεκμηρίωσης

- [database/DATABASE_SUMMARY.md](./database/DATABASE_SUMMARY.md)
- [database/FILE_INDEX.md](./database/FILE_INDEX.md)

## Σημειώσεις

- Η εφαρμογή χρησιμοποιεί κοινή βάση δεδομένων για όλα τα modules.
- Η πρόσβαση στα modules ελέγχεται με βάση το `role` του χρήστη.
- Η σύνδεση με Moodle στην παρούσα έκδοση λειτουργεί ως τοπικό simulation / integration layer για τις ανάγκες του project.

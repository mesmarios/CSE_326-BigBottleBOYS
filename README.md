# CSE 326 BigBottleBOYS

## Ομάδα

- Μάριος Σιήττας - Α.Φ.Τ. 27432
- Μάριος Μεσαρίτης - Α.Φ.Τ. 27818
- Μιχαλής Τσαδιώτης - Α.Φ.Τ. 28053

## Κατανομή εργασίας

- Μάριος Σιήττας (27432): dashboard/admin σελίδες, σύνδεση διεπαφών με δεδομένα βάσης, γενική ενοποίηση εφαρμογής.
- Μάριος Μεσαρίτης (27818): authentication flow, keyword search, βελτιώσεις ασφάλειας, schema/seed προσαρμογές.
- Μιχαλής Τσαδιώτης (28053): README, έλεγχοι ασφάλειας, τεκμηρίωση και υποστήριξη τελικής παράδοσης.

Η παραπάνω κατανομή βασίζεται στα commits του repository και στην τελική δομή παράδοσης.

## Υποχρεωτική δομή για το παραδοτέο

- `database/schema.sql`
- `database/seed.sql`
- `includes/db.php`
- `auth/register.php`
- `auth/login.php`
- `auth/logout.php`
- `modules/dashboard.php`
- `modules/list.php`

Το repository περιέχει και επιπλέον αρχεία του project, αλλά τα παραπάνω είναι τα βασικά αρχεία που ζητά η εκφώνηση.

## Οδηγίες εγκατάστασης

1. Εγκαταστήστε Apache, PHP και MySQL ή χρησιμοποιήστε XAMPP/LAMP.
2. Τοποθετήστε τον φάκελο του project μέσα στο web root, π.χ.:
   - XAMPP: `htdocs/`
   - LAMP: `/var/www/html/`
3. Δημιουργήστε τη βάση:

```sql
CREATE DATABASE bigbrothers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Κάντε import πρώτα το schema και μετά τα demo δεδομένα:

```bash
mysql -u root -p bigbrothers < database/schema.sql
mysql -u root -p bigbrothers < database/seed.sql
```

Demo password για τους seeded users: `Password123!`

5. Ελέγξτε ότι το connection file χρησιμοποιεί τα σωστά credentials:
   - αρχείο: `includes/db.php` -> φορτώνει το `database/db.php`
   - βάση: `bigbrothers`
   - host: `localhost`
   - user: `root`

## Εκτέλεση

Ανοίξτε στον browser το project και χρησιμοποιήστε τα υποχρεωτικά routes:

- `http://localhost/CSE_326-BigBottleBOYS/auth/register.php`
- `http://localhost/CSE_326-BigBottleBOYS/auth/login.php`
- `http://localhost/CSE_326-BigBottleBOYS/modules/dashboard.php`
- `http://localhost/CSE_326-BigBottleBOYS/modules/list.php`

## Σημειώσεις ασφάλειας

- Χρησιμοποιείται PDO με prepared statements.
- Τα passwords αποθηκεύονται μόνο με `password_hash()`.
- Η επαλήθευση σύνδεσης γίνεται με `password_verify()`.
- Όλα τα redirects συνοδεύονται από `exit`.
- Η έξοδος προς HTML γίνεται με `htmlspecialchars()` όπου εμφανίζονται δυναμικά δεδομένα.

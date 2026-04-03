# README

## Στοιχεία Ομάδας

Η ομάδα ανάπτυξης αποτελείται από τους:

* Μάριος Σιήττας — Α.Φ.Τ. 27432
* Μάριος Μεσαριτής — Α.Φ.Τ. 27818
* Μιχαλής Τσαδιώτης — Α.Φ.Τ. 28053

---

## Κατανομή Εργασίας

Η εργασία υλοποιήθηκε συλλογικά από όλα τα μέλη της ομάδας.
Δεν υπήρξε αυστηρός διαχωρισμός καθηκόντων, καθώς όλοι οι φοιτητές συνέβαλαν σε όλα τα μέρη του project.

Συγκεκριμένα, η ομάδα συνεργάστηκε για την υλοποίηση των παρακάτω:

* Dashboard
* Front-end (Frontview)
* Database (βάση δεδομένων & schema)

Όλα τα μέλη συμμετείχαν ενεργά στον σχεδιασμό, την ανάπτυξη και τον έλεγχο της εφαρμογής.

---

## Οδηγίες Εγκατάστασης

### Εκτέλεση με LAMP (Linux)

```bash
sudo apt update
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql
```

Τοποθετήστε τον φάκελο του project στο:

```
/var/www/html/
```

Παράδειγμα:

```bash
sudo cp -r project_folder /var/www/html/
```

Δημιουργήστε τη βάση δεδομένων:

```sql
CREATE DATABASE project_db;
```

Κάντε εισαγωγή του αρχείου `schema.sql`:

```bash
mysql -u root -p project_db < schema.sql
```

---

### Εκτέλεση με XAMPP (Windows)

Εγκαταστήστε και ανοίξτε το XAMPP.

Ενεργοποιήστε τα Apache και MySQL.

Τοποθετήστε τον φάκελο του project στο:

```
C:\xampp\htdocs\
```

Δημιουργήστε τη βάση δεδομένων:

```sql
CREATE DATABASE project_db;
```

Κάντε εισαγωγή του αρχείου `schema.sql`:

```bash
mysql -u root -p project_db < schema.sql
```

---

## Ρύθμιση Σύνδεσης

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "project_db";
```

---

## Εκτέλεση Εφαρμογής

```
http://localhost/project_folder/
```

---

## Παρατηρήσεις

* Βεβαιωθείτε ότι Apache και MySQL είναι ενεργά.
* Το αρχείο `schema.sql` πρέπει να βρίσκεται στον φάκελο του project.
* Τα στοιχεία σύνδεσης πρέπει να αντιστοιχούν στη βάση δεδομένων που δημιουργήθηκε.

# PDO Prepared Statements - Οδηγός Ασφαλείας

## SQL Injection - Τι να αποφεύγεις

### Από την παρουσίαση του Κύριου

> **ΠΡΟΣΟΧΗ:** Ποτέ μην βάζεις user input απευθείας μέσα στο SQL query!

```php
// ΛΑΘΟΣ — SQL Injection vulnerability
$email = $_POST['email'];
$pdo->query("SELECT * FROM users WHERE email = '$email'");
```

---

## Prepared Statements - Σωστός τρόπος

### Μία εγγραφή (`fetch`)

```php
// ΣΩΣΤΟ — Prepared Statement
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(); // επιστρέφει 1 εγγραφή ή false
```

### Πολλές εγγραφές (`fetchAll`)

```php
// ΣΩΣΤΟ — Πολλές εγγραφές
$stmt = $pdo->prepare('SELECT * FROM applications WHERE status = :s');
$stmt->execute([':s' => 'pending']);
$rows = $stmt->fetchAll(); // array με όλες τις εγγραφές
```

---

## Κανόνας

| | Τρόπος | Ασφαλές; |
|---|---|---|
| ❌ | `$pdo->query("... '$var'")` | Όχι |
| ✅ | `$pdo->prepare()` + `execute([...])` | Ναι |

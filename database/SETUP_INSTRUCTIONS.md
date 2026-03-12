# Specialist Management System - Database Setup Instructions

## 📦 Files Created

Your database setup includes the following files:

### 1. **database.sql** (Root directory)
   - Complete database schema with 21 tables
   - All relationships, constraints, and indexes
   - Ready to import into MySQL/MariaDB

### 2. **seed.sql** (Root directory)
   - Sample test data
   - 15 test users with different roles
   - Sample job announcements and applications
   - LMS configuration examples
   - Test data for development

### 3. **BigBottleBOYS/includes/config.php** (UPDATED)
   - Database connection configuration
   - Application settings
   - Security and file upload configurations
   - Helper functions for password hashing

### 4. **BigBottleBOYS/includes/database-helper.php** (NEW)
   - Reusable database query functions
   - Common operations for all modules
   - Statistics and reporting functions
   - Audit and notification management

### 5. **DATABASE_GUIDE.md** (Root directory)
   - Complete documentation
   - Table descriptions
   - Common queries
   - Integration guidelines

---

## 🚀 Quick Setup (5 minutes)

### Step 1: Import the Database (Choose one method)

**Method A: Using MySQL Command Line**
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/CSE_326-BigBottleBOYS

# Import schema
mysql -u root < database.sql

# Import seed data
mysql -u root specialist_management_system < seed.sql
```

**Method B: Using phpMyAdmin**
1. Open http://localhost/phpmyadmin
2. Click "Import" tab
3. Choose database.sql → Click Import
4. Select `specialist_management_system` database
5. Click "Import" tab again
6. Choose seed.sql → Click Import

**Method C: Using XAMPP Control Panel**
1. Open phpMyAdmin from XAMPP Control Panel
2. Follow Method B above

### Step 2: Verify Installation

Create a test file `test-db-connection.php` in your BigBottleBOYS folder:

```php
<?php
require_once 'includes/config.php';

try {
    $stmt = getDBConnection()->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    echo "<h2>✓ Database Connected Successfully!</h2>";
    echo "<p>Total Users: " . $result['count'] . "</p>";
    
    // Test DatabaseHelper class
    require_once 'includes/database-helper.php';
    $users = DatabaseHelper::getAllUsers([], 5);
    
    echo "<h3>Sample Users:</h3>";
    echo "<ul>";
    foreach ($users as $user) {
        echo "<li>" . $user['first_name'] . " " . $user['last_name'] . " (" . $user['email'] . ")</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>✗ Database Connection Failed:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
```

Then visit: `http://localhost/BigBottleBOYS/test-db-connection.php`

### Step 3: Configure Your Application

Edit `BigBottleBOYS/includes/config.php`:

```php
// Update these values if needed
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Add your password if you set one
define('DB_NAME', 'specialist_management_system');

// Update these with your institution details
define('INSTITUTION_EMAIL', 'your-email@tepak.cy');
define('INSTITUTION_PHONE', '+357 XX XXXXXX');
define('MOODLE_API_URL', 'your-moodle-url');
define('MOODLE_API_KEY', 'your-moodle-api-key');
```

---

## 📋 Default Test Credentials

All passwords: `password123` (case-sensitive)

### Admin Account
- **Email**: admin@tepak.cy
- **Password**: password123
- **Name**: Γιάννης Παπαδόπουλος

### HR Manager Account
- **Email**: hr@tepak.cy
- **Password**: password123
- **Name**: Δημήτρης Οικονόμου

### Evaluator Account
- **Email**: evaluator1@tepak.cy
- **Password**: password123
- **Name**: Αντώνης Κωνσταντίνου

### Candidate Account
- **Email**: candidate1@example.com
- **Password**: password123
- **Name**: Παναγιώτης Κυριακίδης

### Specialist Account
- **Email**: specialist1@tepak.cy
- **Password**: password123
- **Name**: Ανδρέας Λοΐζου

---

## 📊 Database Schema Overview

### Core Tables (21 total)
- **Authentication**: users, roles, user_roles
- **Academic Structure**: schools, departments, courses
- **Recruitment**: recruitment_periods, job_announcements, candidate_applications, application_evaluators, application_form_fields, application_responses
- **Enrollment/LMS**: lms_connections, lms_users, specialist_enrollments, enrollment_logs, sync_schedules
- **Configuration**: system_settings, themes
- **System**: notifications, audit_logs

---

## 🔧 Using DatabaseHelper Class

The `database-helper.php` provides convenient functions:

```php
<?php
require_once 'includes/config.php';
require_once 'includes/database-helper.php';

// Get all users
$users = DatabaseHelper::getAllUsers([], 20, 0);

// Get job announcements
$announcements = DatabaseHelper::getJobAnnouncements(['status' => 'published']);

// Get recruitment statistics
$stats = DatabaseHelper::getRecruitmentStats();

// Get enrollment statistics
$enrollStats = DatabaseHelper::getEnrollmentStats();

// Create a new application
$appId = DatabaseHelper::createApplication($announcement_id, $candidate_id);

// Update application response
DatabaseHelper::updateApplicationResponse($appId, $field_id, 'response value');

// Submit application
DatabaseHelper::submitApplication($appId);

// Add notification
DatabaseHelper::createNotification($user_id, 'Title', 'Message', 'type');

// Add audit log
DatabaseHelper::addAuditLog($user_id, 'Action', 'users', $target_id);

// Get/Update system settings
$value = DatabaseHelper::getSystemSetting('app_name');
DatabaseHelper::updateSystemSetting('app_name', 'New Value');
?>
```

---

## 🔐 Password Hashing

All passwords in the seed data are hashed using bcrypt. Use these functions:

```php
<?php
// Hash a password
$hashed = hashPassword('password123');

// Verify a password
$isValid = verifyPassword('password123', $hashedPassword);
?>
```

---

## 📈 Sample Data Included

The seed.sql includes:

```
✓ 5 Roles (Admin, HR Manager, Evaluator, Candidate, Specialist)
✓ 15 Test Users (different roles)
✓ 3 Schools
✓ 8 Departments
✓ 20 Courses
✓ 3 Recruitment Periods
✓ 5 Job Announcements
✓ 9 Candidate Applications
✓ 2 LMS Connections (Moodle)
✓ 6 Specialist Enrollments
✓ Sample Notifications & Audit Logs
```

---

## 🛠️ Common Tasks

### View All Tables
```sql
mysql> USE specialist_management_system;
mysql> SHOW TABLES;
```

### Check Table Structure
```sql
mysql> DESCRIBE users;
```

### Count Records
```sql
mysql> SELECT COUNT(*) FROM job_announcements;
```

### Reset Passwords (in database)
```php
<?php
require_once 'includes/config.php';

$pdo = getDBConnection();
$newHash = hashPassword('newpassword123');
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->execute([$newHash, 'admin@tepak.cy']);
?>
```

### Reset Database (Start Over)
```bash
# Backup current database (optional)
mysqldump -u root specialist_management_system > backup.sql

# Drop and recreate
mysql -u root -e "DROP DATABASE specialist_management_system;"
mysql -u root < database.sql
mysql -u root specialist_management_system < seed.sql
```

---

## 🔗 Integration Points

### Connecting to Your Pages

In your PHP pages, use:

```php
<?php
require_once '../includes/config.php';
require_once '../includes/database-helper.php';

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Get user data
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Use database helper
$announcements = DatabaseHelper::getJobAnnouncements();
?>
```

---

## 📞 Troubleshooting

### "SQLSTATE[HY000] [2002] Connection refused"
- **Solution**: XAMPP MySQL is not running. Start it from Control Panel.

### "SQLSTATE[HY000] [1045] Access denied for user 'root'"
- **Solution**: Check your password in config.php. Default for XAMPP is empty.

### "Unknown database 'specialist_management_system'"
- **Solution**: Re-import database.sql file.

### "Cannot import seed.sql"
- **Solution**: Make sure database schema (database.sql) is imported first.

### Files Not Found Errors
- **Solution**: Check include paths in config.php are correct for your directory structure.

---

## 📚 Additional Resources

- **Database Guide**: See DATABASE_GUIDE.md for complete table documentation
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **PHP PDO Guide**: https://www.php.net/manual/en/book.pdo.php
- **Password Hashing**: https://www.php.net/manual/en/function.password-hash.php

---

## ✅ Checklist

- [ ] database.sql imported successfully
- [ ] seed.sql imported successfully
- [ ] config.php updated with correct credentials
- [ ] Test connection verified
- [ ] Sample data visible in database
- [ ] DatabaseHelper class working
- [ ] Default users can log in
- [ ] Moodle API credentials configured (optional)

---

## 🎯 Next Steps

1. **Set up authentication** - Use config.php functions for login
2. **Create admin dashboard** - Query database using DatabaseHelper
3. **Build recruitment module** - Use job announcements queries
4. **Implement enrollment** - Use specialist enrollment functions
5. **Configure Moodle API** - Update LMS connection credentials
6. **Add notifications** - Use notification functions
7. **Audit logging** - Track user actions with audit logs

---

**Created**: February 2024  
**Database Version**: 1.0.0  
**MySQL Version**: 5.7+  
**PHP Version**: 7.4+

For questions or issues, refer to the DATABASE_GUIDE.md file.

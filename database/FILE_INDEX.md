# 📑 Complete File Index

## 🎯 START HERE
1. **README_DATABASE.md** (THIS DIRECTORY) - Quick overview, test accounts, troubleshooting
2. **test-connection.php** (BigBottleBOYS/) - Visual verification tool, run first

---

## 📚 Documentation Files

### Quick Start
- **SETUP_INSTRUCTIONS.md** - Step-by-step setup guide
- **DATABASE_SUMMARY.md** - Executive summary with examples

### Complete Reference
- **DATABASE_GUIDE.md** - Full table documentation (35+ pages)
- **README_DATABASE.md** - Quick reference guide
- **FILE_INDEX.md** - This file

---

## 💾 Database Files

### Core Files (Root Directory)
```
database.sql          - Database schema (21 tables)
seed.sql             - Sample test data
```

**How to import:**
```bash
mysql -u root < database.sql
mysql -u root specialist_management_system < seed.sql
```

---

## 🐘 PHP Files (BigBottleBOYS/includes/)

### Configuration
```
config.php
├── Database connection settings
├── Application constants
├── Session configuration
├── File upload settings
├── Security functions (password hashing)
└── Utility functions (escape HTML, etc.)
```

### Database Helper Library
```
database-helper.php
├── getAllUsers()
├── getJobAnnouncements()
├── getCandidateApplications()
├── getSpecialistEnrollments()
├── createApplication()
├── submitApplication()
├── updateApplicationResponse()
├── getRecruitmentStats()
├── getEnrollmentStats()
├── createNotification()
├── getUnreadNotifications()
├── addEnrollmentLog()
├── addAuditLog()
├── getSystemSetting()
└── 11+ more functions
```

**How to use:**
```php
<?php
require_once 'includes/config.php';
require_once 'includes/database-helper.php';

$users = DatabaseHelper::getAllUsers();
$stats = DatabaseHelper::getRecruitmentStats();
?>
```

---

## 🧪 Testing

### Verification Tool
```
BigBottleBOYS/test-connection.php
- Tests database connection
- Verifies all tables exist
- Checks sample data
- Tests DatabaseHelper functions
- Shows configuration
```

**Access at:** http://localhost/BigBottleBOYS/test-connection.php

---

## 📊 Database Structure

### 21 Tables in 6 Categories

**Authentication & Users (3 tables)**
```
users          - User accounts
roles          - User roles
user_roles     - User-role assignments
```

**Academic Structure (3 tables)**
```
schools        - Schools/Faculties
departments    - Academic departments
courses        - Courses
```

**Recruitment Module (6 tables)**
```
recruitment_periods           - Recruitment periods
job_announcements            - Job postings
application_evaluators       - Evaluator assignments
candidate_applications       - Applications by candidates
application_form_fields      - Dynamic form fields
application_responses        - Candidate responses
```

**Enrollment/LMS Module (5 tables)**
```
lms_connections              - Moodle/LMS configurations
lms_users                    - Users in LMS
specialist_enrollments       - Course enrollments for specialists
enrollment_logs              - Sync operations log
sync_schedules               - Automatic sync scheduling
```

**System Configuration (2 tables)**
```
system_settings              - Application settings
themes                       - UI themes/branding
```

**System Operations (2 tables)**
```
notifications                - User notifications
audit_logs                   - Security audit trail
```

---

## 👥 Sample Test Data

### Users (15 total)
- 3 Admins
- 2 HR Managers
- 3 Evaluators
- 5 Candidates
- 2 Specialists (can be more in real usage)

All passwords: `password123`

### Sample Data Included
- 3 Schools
- 8 Departments
- 20 Courses
- 3 Recruitment Periods
- 5 Job Announcements
- 9 Candidate Applications
- 2 Moodle Connections
- 6 Specialist Enrollments
- Sample notifications and audit logs

---

## 🔧 Configuration Guide

### Database Connection (config.php)
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');              // Add password if needed
define('DB_NAME', 'specialist_management_system');
```

### Application Settings (config.php)
```php
define('APP_NAME', 'Σύστημα Διαχείρισης Ειδικών Επιστημόνων');
define('INSTITUTION_NAME', 'ΤΕΠΑΚ');
define('MAX_FILE_SIZE', 5242880);   // 5MB
define('ITEMS_PER_PAGE', 20);
```

### Moodle Integration (config.php)
```php
define('MOODLE_API_URL', 'https://moodle.tepak.cy/webservice/rest/server.php');
define('MOODLE_API_KEY', 'your_api_key_here');
```

---

## 🚀 Quick Commands

### Import Database
```bash
# Create schema
mysql -u root < database.sql

# Load sample data
mysql -u root specialist_management_system < seed.sql
```

### Check MySQL Status
```bash
# Check if running
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server status

# Start if needed
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start
```

### List Tables
```bash
mysql -u root specialist_management_system -e "SHOW TABLES;"
```

### Count Records
```bash
mysql -u root specialist_management_system -e "SELECT 'users' as table_name, COUNT(*) as count FROM users;"
```

### Reset Password
```php
<?php
require_once 'includes/config.php';
$pdo = getDBConnection();
$new_hash = hashPassword('newpassword123');
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->execute([$new_hash, 'admin@tepak.cy']);
?>
```

---

## 🧠 Key Concepts

### User Roles
- **Admin**: Full system access
- **HR Manager**: Recruitment and enrollment management
- **Evaluator**: Review job applications
- **Candidate**: Apply for positions
- **Specialist**: Hired specialist with LMS access

### Application Status Flow
Draft → Submitted → Under Review → Accepted/Rejected → Enrolled (in LMS)

### Enrollment Status Options
- pending (waiting for activation)
- active (has LMS access)
- inactive (no access)
- suspended (temporarily disabled)

### Recruitment Period Status
- planning (preparing)
- active (accepting applications)
- closed (no new applications)
- archived (historical data)

---

## 📈 Statistics & Reporting

### Recruitment Statistics
```php
$stats = DatabaseHelper::getRecruitmentStats();
// Returns:
// - total_announcements
// - total_applications
// - applications_by_status
// - applications_per_announcement
```

### Enrollment Statistics
```php
$stats = DatabaseHelper::getEnrollmentStats();
// Returns:
// - total_specialists
// - active_enrollments
// - pending_enrollments
// - enrollments_by_status
// - recent_syncs
```

---

## 🔒 Security Features

✓ **Password Hashing**
- Algorithm: Bcrypt
- Cost Factor: 10
- Function: `hashPassword()`, `verifyPassword()`

✓ **SQL Injection Prevention**
- Using PDO prepared statements
- All user input parameterized

✓ **XSS Prevention**
- HTML escaping: `escape()` function
- Available in config.php

✓ **Audit Logging**
- All user actions tracked
- IP addresses recorded
- User agent stored
- Changes recorded as JSON

✓ **File Upload Security**
- Whitelist of allowed file types
- File size limits
- Save outside web root

---

## ✅ Pre-Flight Checklist

- [ ] MySQL is running
- [ ] database.sql imported  
- [ ] seed.sql imported
- [ ] config.php updated (if needed)
- [ ] test-connection.php runs successfully
- [ ] Sample users can log in
- [ ] DatabaseHelper functions work
- [ ] All 21 tables created
- [ ] No error messages

---

## 🆘 Common Issues & Solutions

**Issue**: "Connection refused"
- **Solution**: Start MySQL: `sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start`

**Issue**: "Unknown database"
- **Solution**: Import database.sql first

**Issue**: "Table already exists"
- **Solution**: Drop database and reimport: `mysql -u root -e "DROP DATABASE specialist_management_system;"`

**Issue**: "Access denied for user 'root'"
- **Solution**: Check password in config.php (default is empty for XAMPP)

**Issue**: Password not working
- **Solution**: All sample users have password `password123`

---

## 📚 Documentation Map

```
README_DATABASE.md (You are here)
├── SETUP_INSTRUCTIONS.md (How to set up)
├── DATABASE_GUIDE.md (Complete reference)
├── DATABASE_SUMMARY.md (Quick overview)
└── FILE_INDEX.md (This document)

BigBottleBOYS/
├── includes/
│   ├── config.php (Database connection)
│   ├── database-helper.php (Query functions)
│   ├── functions.php (Utility functions)
│   ├── header.php (HTML header)
│   ├── footer.php (HTML footer)
│   ├── nav.php (Navigation menu)
│   └── layout.php (Page layout)
├── test-connection.php (Verification tool)
├── login.php (Login page)
├── register.php (Registration page)
└── logout.php (Logout handler)

Root Directory
├── database.sql (Schema - 21 tables)
├── seed.sql (Sample data)
├── README_DATABASE.md (Quick start)
├── SETUP_INSTRUCTIONS.md (Detailed setup)
├── DATABASE_GUIDE.md (Complete docs)
├── DATABASE_SUMMARY.md (Executive summary)
└── FILE_INDEX.md (This file)
```

---

## 🎓 Learning Path

1. **Day 1**: Set up database, run test-connection.php
2. **Day 2**: Read SETUP_INSTRUCTIONS.md, understand sample data
3. **Day 3**: Review DATABASE_GUIDE.md table descriptions
4. **Day 4**: Test DatabaseHelper functions with sample queries
5. **Day 5**: Begin building admin module
6. **Week 2**: Build recruitment module
7. **Week 3**: Build enrollment module
8. **Week 4**: Integrate Moodle LMS API

---

## 📞 Support Resources

- **Complete Table Documentation**: DATABASE_GUIDE.md
- **Setup & Troubleshooting**: SETUP_INSTRUCTIONS.md
- **Quick Reference**: DATABASE_SUMMARY.md
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **PHP PDO Guide**: https://www.php.net/manual/en/book.pdo.php
- **Password Hashing**: https://www.php.net/manual/en/function.password-hash.php

---

## 🎯 Next Action

1. **If you haven't imported the database yet:**
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/CSE_326-BigBottleBOYS
   mysql -u root < database.sql
   mysql -u root specialist_management_system < seed.sql
   ```

2. **Then verify the installation:**
   Open: **http://localhost/BigBottleBOYS/test-connection.php**

3. **Finally, read:**
   - SETUP_INSTRUCTIONS.md (if setting up)
   - DATABASE_GUIDE.md (if developing)
   - DATABASE_SUMMARY.md (for quick reference)

---

**Version**: 1.0.0  
**Created**: February 2024  
**Status**: Production Ready  
**Tables**: 21  
**Test Users**: 15  
**Sample Data**: Complete  

Ready to build! 🚀

# 🎓 Specialist Management System (ΕΕ - Ειδικοί Επιστήμονες)

**Complete Database Schema & Configuration for TEPAK**

---

## 📢 Quick Start

### 1️⃣ Import Database
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/CSE_326-BigBottleBOYS

# Import schema (creates database and tables)
mysql -u root < database.sql

# Import sample data (test users, announcements, applications)
mysql -u root specialist_management_system < seed.sql
```

### 2️⃣ Verify Installation
Visit: **http://localhost/BigBottleBOYS/test-connection.php**

### 3️⃣ Start Building
- Use `DatabaseHelper::` functions for database queries
- Check `DATABASE_GUIDE.md` for complete documentation
- See `SETUP_INSTRUCTIONS.md` for examples

---

## 📦 What's Included

### Files Created

| File | Purpose | Location |
|------|---------|----------|
| `database.sql` | Database schema (21 tables) | Root directory |
| `seed.sql` | Sample test data | Root directory |
| `DATABASE_GUIDE.md` | Complete documentation | Root directory |
| `SETUP_INSTRUCTIONS.md` | Quick start guide | Root directory |
| `DATABASE_SUMMARY.md` | Overview summary | Root directory |
| `config.php` | Database connection | `includes/` |
| `database-helper.php` | Reusable functions | `includes/` |
| `test-connection.php` | Verification tool | `BigBottleBOYS/` |

---

## 👥 Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@tepak.cy | password123 |
| HR Manager | hr@tepak.cy | password123 |
| Evaluator | evaluator1@tepak.cy | password123 |
| Candidate | candidate1@example.com | password123 |
| Specialist | specialist1@tepak.cy | password123 |

---

## 📊 Database Overview

### 21 Tables Organized in 6 Categories:

**Authentication (3 tables)**
- users, roles, user_roles

**Academic Structure (3 tables)**
- schools, departments, courses

**Recruitment Module (6 tables)**
- recruitment_periods, job_announcements, application_evaluators
- candidate_applications, application_form_fields, application_responses

**Enrollment/LMS Module (5 tables)**
- lms_connections, lms_users, specialist_enrollments
- enrollment_logs, sync_schedules

**System Configuration (2 tables)**
- system_settings, themes

**System Operations (2 tables)**
- notifications, audit_logs

---

## 🔧 Key Functions

### DatabaseHelper Class (25+ functions)

```php
<?php
require_once 'includes/database-helper.php';

// User Management
DatabaseHelper::getAllUsers($filters, $limit, $offset);

// Recruitment
DatabaseHelper::getJobAnnouncements($filters, $limit, $offset);
DatabaseHelper::getCandidateApplications($announcement_id, $filters);
DatabaseHelper::createApplication($announcement_id, $candidate_id);
DatabaseHelper::submitApplication($application_id);

// Enrollment
DatabaseHelper::getSpecialistEnrollments($user_id);
DatabaseHelper::addEnrollmentLog($user_id, $action, $type, $details);

// Statistics
DatabaseHelper::getRecruitmentStats();
DatabaseHelper::getEnrollmentStats();

// Notifications
DatabaseHelper::createNotification($user_id, $title, $message);
DatabaseHelper::getUnreadNotifications($user_id);

// Audit
DatabaseHelper::addAuditLog($user_id, $action, $entity_type);

// Settings
DatabaseHelper::getSystemSetting($key);
DatabaseHelper::updateSystemSetting($key, $value);
?>
```

---

## 📖 Documentation

### 📚 Main Documentation Files:

1. **DATABASE_GUIDE.md** (~35 pages)
   - Complete table descriptions
   - Relationships diagram
   - Common queries
   - Integration examples
   - Performance notes

2. **SETUP_INSTRUCTIONS.md** (~25 pages)
   - Step-by-step setup
   - Troubleshooting guide
   - Default credentials
   - Usage examples
   - Common tasks

3. **DATABASE_SUMMARY.md** (~20 pages)
   - Quick overview
   - Architecture diagram
   - Feature list
   - Security notes
   - Next steps

---

## 🚀 What's Ready

✅ **Complete Database Schema**
- 21 production-ready tables
- All relationships and constraints
- Optimized indexes
- Foreign key enforcement

✅ **Sample Data**
- 15 test users (all roles)
- 3 schools with 8 departments
- 20 courses
- 5 job announcements
- 9 practice applications
- Moodle integration examples

✅ **PHP Configuration**
- Database connection (PDO)
- Password hashing functions
- Session management
- HTML escaping/sanitization
- Security best practices

✅ **Helper Library**
- 25+ reusable functions
- All common queries
- Error handling
- Pagination support
- JSON encoding/decoding

✅ **Documentation**
- Complete API reference
- Integration guides
- Troubleshooting help
- Usage examples
- Security guidelines

---

## 🎯 Three Modules Supported

### 1. Admin Module
- Dashboard with statistics
- User management
- Recruitment configuration
- System settings
- Report generation
- Audit logging

### 2. Recruitment Module
- Job announcement management
- Application submission
- Dynamic forms
- Application tracking
- Status updates
- Evaluator assignment

### 3. Enrollment Module
- Specialist enrollment management
- LMS/Moodle integration
- Access control
- Sync scheduling
- Enrollment statistics
- LMS connection management

---

## 🔐 Security Features

✓ **Bcrypt password hashing** (cost factor 10)
✓ **PDO prepared statements** (SQL injection prevention)
✓ **HTML escaping** (XSS prevention)
✓ **Complete audit logging** (all user actions tracked)
✓ **Role-based access control** (5 roles with different permissions)
✓ **Session management** (configurable timeouts)
✓ **IP address tracking** (for security analysis)
✓ **File upload validation** (whitelist of allowed types)

---

## 📈 Optimization

✓ **Indexed foreign keys** (fast lookups)
✓ **Indexed status columns** (quick filtering)
✓ **Pagination support** (efficient data loading)
✓ **JSON storage** (flexible additional data)
✓ **View-friendly queries** (pre-built SELECT statements)

---

## 🔄 Moodle Integration

The database includes **LMS connection management** to integrate with Moodle:

1. **Multiple LMS Support** - Connect to multiple Moodle instances
2. **User Mapping** - Track users in both systems
3. **Course Enrollment** - Manage specialist access to courses
4. **Sync Logging** - Complete audit trail of all sync operations
5. **Automatic Scheduling** - Configure auto-sync intervals

---

## 📝 Usage Example

```php
<?php
// Include configuration
require_once 'includes/config.php';
require_once 'includes/database-helper.php';

// Get recruiter dashboard data
$announcements = DatabaseHelper::getJobAnnouncements(['status' => 'published'], 20, 0);
$stats = DatabaseHelper::getRecruitmentStats();
$enrollmentStats = DatabaseHelper::getEnrollmentStats();

// Display in admin dashboard
foreach ($stats['applications_per_announcement'] as $ann) {
    echo "Position: " . $ann['title'] . " - Applications: " . $ann['count'];
}

// Check specialist enrollments
$enrollments = DatabaseHelper::getSpecialistEnrollments($specialist_id);
foreach ($enrollments as $enrollment) {
    echo "Course: " . $enrollment['course_name'] . " - Status: " . $enrollment['access_status'];
}
?>
```

---

## 🛡️ Configuration Checklist

- [x] Database schema created (database.sql)
- [x] Sample data loaded (seed.sql)
- [x] Database connection configured (config.php)
- [x] Helper functions available (database-helper.php)
- [x] Test file created (test-connection.php)
- [ ] **YOUR TASK**: Update Moodle API credentials (if using LMS integration)
- [ ] **YOUR TASK**: Configure email settings (if using notifications)
- [ ] **YOUR TASK**: Set up file upload directory with proper permissions

---

## 🧪 Testing

Run the verification test to ensure everything is working:

```bash
# Start XAMPP MySQL
# Navigate to: http://localhost/BigBottleBOYS/test-connection.php
```

The test will verify:
- ✓ Database connection
- ✓ All 21 tables created
- ✓ Sample data loaded
- ✓ DatabaseHelper functions working
- ✓ Configuration correct

---

## 📞 Troubleshooting

### MySQL not running?
```bash
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start
```

### Database already exists?
```bash
mysql -u root -e "DROP DATABASE specialist_management_system;"
mysql -u root < database.sql
```

### Can't import seed.sql?
- Make sure database.sql was imported first
- Check error message in MySQL console
- Verify file is in correct directory

### Connection timeout?
- Check MySQL is running in XAMPP Control Panel
- Verify localhost is reachable
- Check firewall settings

See **SETUP_INSTRUCTIONS.md** for more troubleshooting.

---

## 📚 Learning Resources

- **Database Guide**: DATABASE_GUIDE.md (complete reference)
- **Setup Help**: SETUP_INSTRUCTIONS.md (step-by-step)
- **Summary**: DATABASE_SUMMARY.md (quick overview)
- **PHP PDO**: https://www.php.net/manual/en/book.pdo.php
- **MySQL Docs**: https://dev.mysql.com/doc/

---

## 🎓 Next Steps for Your Project

1. **Run `test-connection.php`** to verify setup
2. **Read DATABASE_GUIDE.md** for table documentation
3. **Use DatabaseHelper functions** in your PHP pages
4. **Build authentication pages** (login.php, register.php)
5. **Create admin dashboard** (query recruitment stats)
6. **Build recruitment module** (announcements, applications)
7. **Implement enrollment features** (specialist management)
8. **Configure Moodle API** (LMS integration)
9. **Add email notifications** (status updates)
10. **Deploy to production** (backup database regularly)

---

## 📋 System Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **XAMPP**: Current version
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)

---

## 🎉 You're All Set!

Your database is **fully functional and ready to use**. All sample data is loaded, documentation is complete, and helper functions are available.

Start integrating it with your PHP pages today!

---

## 📞 Support Files

- `DATABASE_GUIDE.md` - Complete documentation
- `SETUP_INSTRUCTIONS.md` - Detailed setup guide
- `DATABASE_SUMMARY.md` - Quick reference
- `config.php` - Configuration and constants
- `database-helper.php` - Function library
- `test-connection.php` - Verification tool

---

**Created**: February 2024  
**Version**: 1.0.0  
**Status**: Production Ready  
**Language**: MySQL 5.7+  
**Encoding**: UTF-8 Unicode (Greek support)

---

Good luck with your Specialist Management System! 🚀

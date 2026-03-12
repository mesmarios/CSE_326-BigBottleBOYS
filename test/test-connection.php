<?php
/**
 * Database Connection Verification Test
 * 
 * Save this file as: test-connection.php in the BigBottleBOYS directory
 * Access it at: http://localhost/BigBottleBOYS/test-connection.php
 * 
 * This will verify:
 * 1. Database connection is working
 * 2. All tables are created
 * 3. Sample data is loaded
 * 4. DatabaseHelper class works
 */

// Enable error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/database-helper.php';

?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .header p { font-size: 1.1em; opacity: 0.9; }
        .content { padding: 40px; }
        .section {
            margin-bottom: 40px;
            border-left: 4px solid #667eea;
            padding-left: 20px;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.8em;
        }
        .status-box {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        .status-box.success {
            border-left-color: #28a745;
            background: #f0f8f0;
        }
        .status-box.error {
            border-left-color: #dc3545;
            background: #f8f0f0;
        }
        .status-box.info {
            border-left-color: #17a2b8;
            background: #f0f7f8;
        }
        .status-icon {
            font-size: 1.5em;
            margin-right: 10px;
            display: inline-block;
            width: 30px;
        }
        .status-text {
            display: inline-block;
            vertical-align: middle;
        }
        .status-text strong { color: #333; }
        .status-text p { color: #666; margin-top: 5px; font-size: 0.9em; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }
        table thead {
            background: #667eea;
            color: white;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table tr:hover { background: #f9f9f9; }
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        .footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ddd;
        }
        .test-results {
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            margin-left: 10px;
        }
        .badge.success { background: #28a745; color: white; }
        .badge.error { background: #dc3545; color: white; }
        .badge.warning { background: #ffc107; color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Database Connection Test</h1>
            <p>Specialist Management System - TEPAK</p>
        </div>
        
        <div class="content">
            <?php
            $all_passed = true;
            $test_results = [];
            
            // Test 1: Database Connection
            echo '<div class="section">';
            echo '<h2>📊 Database Connection</h2>';
            
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->query("SELECT VERSION() as version");
                $result = $stmt->fetch();
                
                echo '<div class="status-box success">';
                echo '<span class="status-icon">✓</span>';
                echo '<div class="status-text">';
                echo '<strong>Database Connected Successfully</strong>';
                echo '<p>MySQL Version: ' . htmlspecialchars($result['version']) . '</p>';
                echo '</div></div>';
                
                $test_results['connection'] = true;
            } catch (Exception $e) {
                $all_passed = false;
                echo '<div class="status-box error">';
                echo '<span class="status-icon">✗</span>';
                echo '<div class="status-text">';
                echo '<strong>Database Connection Failed</strong>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div></div>';
                $test_results['connection'] = false;
            }
            
            echo '</div>';
            
            if ($test_results['connection']) {
                // Test 2: Table Count
                echo '<div class="section">';
                echo '<h2>📋 Database Tables</h2>';
                
                try {
                    $pdo = getDBConnection();
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'specialist_management_system'");
                    $result = $stmt->fetch();
                    $table_count = $result['count'];
                    
                    echo '<div class="status-box ' . ($table_count >= 21 ? 'success' : 'warning') . '">';
                    echo '<span class="status-icon">' . ($table_count >= 21 ? '✓' : '⚠') . '</span>';
                    echo '<div class="status-text">';
                    echo '<strong>Tables Found: ' . $table_count . '/21</strong>';
                    if ($table_count < 21) {
                        echo '<p>Warning: Not all tables have been created yet. Run: mysql -u root < database.sql</p>';
                        $all_passed = false;
                    }
                    echo '</div></div>';
                    
                    // List all tables
                    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'specialist_management_system' ORDER BY table_name");
                    $tables = $stmt->fetchAll();
                    
                    if (!empty($tables)) {
                        echo '<table><thead><tr><th>Table Name</th><th>Record Count</th></tr></thead><tbody>';
                        foreach ($tables as $table) {
                            $table_name = $table['table_name'];
                            $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM `" . $table_name . "`");
                            $count = $count_stmt->fetch()['count'];
                            echo '<tr><td>' . htmlspecialchars($table_name) . '</td><td>' . $count . ' records</td></tr>';
                        }
                        echo '</tbody></table>';
                    }
                    
                    $test_results['tables'] = true;
                } catch (Exception $e) {
                    $all_passed = false;
                    echo '<div class="status-box error">';
                    echo '<span class="status-icon">✗</span>';
                    echo '<div class="status-text">';
                    echo '<strong>Failed to retrieve tables</strong>';
                    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '</div></div>';
                    $test_results['tables'] = false;
                }
                
                echo '</div>';
                
                // Test 3: Sample Data
                echo '<div class="section">';
                echo '<h2>👥 Sample Data Verification</h2>';
                
                try {
                    $pdo = getDBConnection();
                    
                    // Users count
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                    $user_count = $stmt->fetch()['count'];
                    
                    // Job announcements
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM job_announcements");
                    $announcement_count = $stmt->fetch()['count'];
                    
                    // Applications
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM candidate_applications");
                    $application_count = $stmt->fetch()['count'];
                    
                    echo '<div class="status-box ' . ($user_count >= 15 ? 'success' : 'warning') . '">';
                    echo '<span class="status-icon">' . ($user_count >= 15 ? '✓' : '⚠') . '</span>';
                    echo '<div class="status-text">';
                    echo '<strong>Users Found: ' . $user_count . '/15</strong>';
                    echo '<p>If count is 0, run: mysql -u root specialist_management_system < seed.sql</p>';
                    echo '</div></div>';
                    
                    echo '<div class="status-box info">';
                    echo '<span class="status-icon">ℹ</span>';
                    echo '<div class="status-text">';
                    echo '<strong>Sample Data Statistics</strong>';
                    echo '<p>Job Announcements: ' . $announcement_count . ' | Applications: ' . $application_count . '</p>';
                    echo '</div></div>';
                    
                    // Show sample users
                    echo '<h3 style="margin-top: 20px; margin-bottom: 10px;">Sample User Accounts:</h3>';
                    $stmt = $pdo->query("SELECT id, email, first_name, last_name, status FROM users LIMIT 10");
                    $users = $stmt->fetchAll();
                    
                    if (!empty($users)) {
                        echo '<table><thead><tr><th>Email</th><th>Name</th><th>Status</th></tr></thead><tbody>';
                        foreach ($users as $user) {
                            echo '<tr><td><code>' . htmlspecialchars($user['email']) . '</code></td><td>' . htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) . '</td><td>' . htmlspecialchars($user['status']) . '</td></tr>';
                        }
                        echo '</tbody></table>';
                        echo '<p style="margin-top: 10px; color: #666; font-size: 0.9em;">✓ All sample users have password: <strong>password123</strong></p>';
                    }
                    
                    $test_results['sample_data'] = $user_count >= 15;
                } catch (Exception $e) {
                    echo '<div class="status-box error">';
                    echo '<span class="status-icon">✗</span>';
                    echo '<div class="status-text">';
                    echo '<strong>Failed to verify sample data</strong>';
                    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '</div></div>';
                    $test_results['sample_data'] = false;
                }
                
                echo '</div>';
                
                // Test 4: DatabaseHelper Class
                echo '<div class="section">';
                echo '<h2>🔧 DatabaseHelper Functions Test</h2>';
                
                try {
                    // Test getAllUsers
                    $users = DatabaseHelper::getAllUsers([], 5, 0);
                    echo '<div class="status-box success">';
                    echo '<span class="status-icon">✓</span>';
                    echo '<div class="status-text">';
                    echo '<strong>DatabaseHelper::getAllUsers()</strong> - Working ' . count($users) . ' users retrieved';
                    echo '</div></div>';
                    
                    // Test getJobAnnouncements
                    $announcements = DatabaseHelper::getJobAnnouncements([], 5, 0);
                    echo '<div class="status-box success">';
                    echo '<span class="status-icon">✓</span>';
                    echo '<div class="status-text">';
                    echo '<strong>DatabaseHelper::getJobAnnouncements()</strong> - Working ' . count($announcements) . ' announcements retrieved';
                    echo '</div></div>';
                    
                    // Test getRecruitmentStats
                    $stats = DatabaseHelper::getRecruitmentStats();
                    echo '<div class="status-box success">';
                    echo '<span class="status-icon">✓</span>';
                    echo '<div class="status-text">';
                    echo '<strong>DatabaseHelper::getRecruitmentStats()</strong> - Working ';
                    echo $stats['total_announcements'] . ' announcements, ' . $stats['total_applications'] . ' applications';
                    echo '</div></div>';
                    
                    $test_results['helper_functions'] = true;
                } catch (Exception $e) {
                    $all_passed = false;
                    echo '<div class="status-box error">';
                    echo '<span class="status-icon">✗</span>';
                    echo '<div class="status-text">';
                    echo '<strong>DatabaseHelper Functions Failed</strong>';
                    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '</div></div>';
                    $test_results['helper_functions'] = false;
                }
                
                echo '</div>';
                
                // Test 5: Configuration
                echo '<div class="section">';
                echo '<h2>⚙️ Configuration Settings</h2>';
                
                echo '<table><thead><tr><th>Setting</th><th>Value</th></tr></thead><tbody>';
                echo '<tr><td>App Name</td><td>' . htmlspecialchars(APP_NAME) . '</td></tr>';
                echo '<tr><td>Database Host</td><td>' . htmlspecialchars(DB_HOST) . '</td></tr>';
                echo '<tr><td>Database Name</td><td>' . htmlspecialchars(DB_NAME) . '</td></tr>';
                echo '<tr><td>Max File Size</td><td>' . (MAX_FILE_SIZE / 1024 / 1024) . ' MB</td></tr>';
                echo '<tr><td>Items Per Page</td><td>' . ITEMS_PER_PAGE . '</td></tr>';
                echo '</tbody></table>';
                
                echo '</div>';
            }
            
            // Final Summary
            echo '<div class="section">';
            echo '<h2>🎉 Summary</h2>';
            
            if ($all_passed && $test_results['connection'] && $test_results['tables'] && $test_results['sample_data']) {
                echo '<div class="status-box success">';
                echo '<span class="status-icon">✓</span>';
                echo '<div class="status-text">';
                echo '<strong>All Tests Passed!</strong>';
                echo '<p>Your database is fully configured and ready to use.</p>';
                echo '</div></div>';
                
                echo '<div class="code-block">✓ Database connection working<br/>✓ All 21 tables created<br/>✓ Sample data loaded<br/>✓ DatabaseHelper functions available<br/>✓ Configuration correct</div>';
                
                echo '<h3 style="margin-top: 20px; color: #333;">Next Steps:</h3>';
                echo '<ol style="margin-left: 20px; color: #666; line-height: 1.8;">';
                echo '<li>Use DatabaseHelper class for database queries in your PHP files</li>';
                echo '<li>Include config.php at the top of your files: require_once "includes/config.php";</li>';
                echo '<li>Check DATABASE_GUIDE.md for complete table documentation</li>';
                echo '<li>See SETUP_INSTRUCTIONS.md for integration examples</li>';
                echo '<li>Test login with: admin@tepak.cy / password123</li>';
                echo '</ol>';
            } else {
                echo '<div class="status-box error">';
                echo '<span class="status-icon">✗</span>';
                echo '<div class="status-text">';
                echo '<strong>Some Tests Failed</strong>';
                echo '<p>Please review the errors above and run the required SQL imports.</p>';
                echo '</div></div>';
                
                echo '<h3 style="margin-top: 20px; color: #333;">Required Actions:</h3>';
                echo '<ol style="margin-left: 20px; color: #666; line-height: 1.8;">';
                if (!$test_results['connection']) {
                    echo '<li>✗ Check MySQL connection: Start XAMPP MySQL service</li>';
                }
                if (!$test_results['tables']) {
                    echo '<li>✗ Import database schema: mysql -u root < database.sql</li>';
                }
                if (!$test_results['sample_data']) {
                    echo '<li>✗ Import sample data: mysql -u root specialist_management_system < seed.sql</li>';
                }
                echo '</ol>';
            }
            
            echo '</div>';
            ?>
        </div>
        
        <div class="footer">
            <p>Specialist Management System - Database Verification Test</p>
            <p style="font-size: 0.9em; margin-top: 10px;">Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
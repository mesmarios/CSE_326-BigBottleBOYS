<?php
require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../includes/config.php';

$pdo = getDBConnection();

function upsertSetting(PDO $pdo, string $key, string $value, string $description = ''): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO system_settings (setting_key, setting_value, description)
         VALUES (:key, :value, :description)
         ON DUPLICATE KEY UPDATE
            setting_value = VALUES(setting_value),
            description = VALUES(description),
            updated_at = NOW()'
    );
    $stmt->execute([
        ':key' => $key,
        ':value' => $value,
        ':description' => $description,
    ]);
}

function getSettingsMap(PDO $pdo): array
{
    $rows = $pdo->query('SELECT setting_key, setting_value FROM system_settings')->fetchAll(PDO::FETCH_ASSOC);
    $map = [];
    foreach ($rows as $row) {
        $map[$row['setting_key']] = $row['setting_value'];
    }
    return $map;
}

function redirectConfig(string $message, string $type = 'success'): void
{
    header('Location: configure_system.php?msg=' . urlencode($message) . '&mtype=' . urlencode($type));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_general') {
        upsertSetting($pdo, 'app_name', trim($_POST['app_name'] ?? ''), 'Όνομα εφαρμογής');
        upsertSetting($pdo, 'app_subtitle', trim($_POST['app_subtitle'] ?? ''), 'Υπότιτλος εφαρμογής');
        upsertSetting($pdo, 'app_description', trim($_POST['app_description'] ?? ''), 'Περιγραφή εφαρμογής');
        upsertSetting($pdo, 'institution_email', trim($_POST['institution_email'] ?? ''), 'Email ιδρύματος');
        upsertSetting($pdo, 'institution_phone', trim($_POST['institution_phone'] ?? ''), 'Τηλέφωνο ιδρύματος');
        redirectConfig('Οι γενικές ρυθμίσεις αποθηκεύτηκαν επιτυχώς.');
    }

    if ($action === 'save_theme') {
        $themeName = trim($_POST['theme_name'] ?? 'System Theme');
        $logoUrl = trim($_POST['logo_url'] ?? '');
        $primaryColor = trim($_POST['primary_color'] ?? '#007bff');
        $secondaryColor = trim($_POST['secondary_color'] ?? '#6c757d');
        $description = trim($_POST['theme_description'] ?? '');

        $theme = $pdo->query('SELECT id FROM themes ORDER BY is_active DESC, id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if ($theme) {
            $stmt = $pdo->prepare(
                'UPDATE themes
                 SET name = :name,
                     logo_url = :logo_url,
                     primary_color = :primary_color,
                     secondary_color = :secondary_color,
                     description = :description,
                     is_active = 1,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':name' => $themeName,
                ':logo_url' => $logoUrl !== '' ? $logoUrl : null,
                ':primary_color' => $primaryColor,
                ':secondary_color' => $secondaryColor,
                ':description' => $description !== '' ? $description : null,
                ':id' => $theme['id'],
            ]);
            $pdo->exec('UPDATE themes SET is_active = CASE WHEN id = ' . (int) $theme['id'] . ' THEN 1 ELSE 0 END');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO themes (name, logo_url, primary_color, secondary_color, description, is_active)
                 VALUES (:name, :logo_url, :primary_color, :secondary_color, :description, 1)'
            );
            $stmt->execute([
                ':name' => $themeName,
                ':logo_url' => $logoUrl !== '' ? $logoUrl : null,
                ':primary_color' => $primaryColor,
                ':secondary_color' => $secondaryColor,
                ':description' => $description !== '' ? $description : null,
            ]);
        }

        redirectConfig('Οι ρυθμίσεις θέματος αποθηκεύτηκαν επιτυχώς.');
    }

    if ($action === 'save_moodle' || $action === 'test_moodle') {
        $enabled = isset($_POST['moodle_enabled']) ? '1' : '0';
        $url = trim($_POST['api_url'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');
        $apiSecret = trim($_POST['api_secret'] ?? '');
        $name = trim($_POST['connection_name'] ?? 'Moodle Main Connection');
        $status = $enabled === '1' ? 'active' : 'inactive';

        $connection = $pdo->query('SELECT id FROM lms_connections ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if ($connection) {
            $stmt = $pdo->prepare(
                'UPDATE lms_connections
                 SET name = :name,
                     api_url = :api_url,
                     api_key = :api_key,
                     api_secret = :api_secret,
                     status = :status,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':name' => $name,
                ':api_url' => $url,
                ':api_key' => $apiKey,
                ':api_secret' => $apiSecret !== '' ? $apiSecret : null,
                ':status' => $status,
                ':id' => $connection['id'],
            ]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO lms_connections (name, api_url, api_key, api_secret, status, description)
                 VALUES (:name, :api_url, :api_key, :api_secret, :status, :description)'
            );
            $stmt->execute([
                ':name' => $name,
                ':api_url' => $url,
                ':api_key' => $apiKey,
                ':api_secret' => $apiSecret !== '' ? $apiSecret : null,
                ':status' => $status,
                ':description' => 'Primary Moodle connection',
            ]);
        }

        upsertSetting($pdo, 'moodle_enabled', $enabled, 'Ενεργοποίηση σύνδεσης Moodle');

        if ($action === 'test_moodle') {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                redirectConfig('Το URL του Moodle δεν είναι έγκυρο.', 'danger');
            }

            $reachable = false;
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_exec($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $reachable = $httpCode > 0 && $httpCode < 500;
                curl_close($ch);
            } else {
                $headers = @get_headers($url);
                $reachable = is_array($headers) && !empty($headers);
            }

            if ($reachable) {
                redirectConfig('Η δοκιμή σύνδεσης Moodle ολοκληρώθηκε επιτυχώς.');
            }

            redirectConfig('Η σύνδεση Moodle αποθηκεύτηκε, αλλά η δοκιμή δεν πέτυχε.', 'danger');
        }

        redirectConfig('Οι ρυθμίσεις Moodle αποθηκεύτηκαν επιτυχώς.');
    }

    if ($action === 'save_smtp') {
        upsertSetting($pdo, 'smtp_enabled', isset($_POST['smtp_enabled']) ? '1' : '0', 'Ενεργοποίηση SMTP');
        upsertSetting($pdo, 'smtp_host', trim($_POST['smtp_host'] ?? ''), 'SMTP host');
        upsertSetting($pdo, 'smtp_port', trim($_POST['smtp_port'] ?? ''), 'SMTP port');
        upsertSetting($pdo, 'smtp_user', trim($_POST['smtp_user'] ?? ''), 'SMTP username');
        upsertSetting($pdo, 'smtp_pass', trim($_POST['smtp_pass'] ?? ''), 'SMTP password');
        upsertSetting($pdo, 'smtp_from_email', trim($_POST['smtp_from_email'] ?? ''), 'SMTP sender email');
        upsertSetting($pdo, 'smtp_from_name', trim($_POST['smtp_from_name'] ?? ''), 'SMTP sender name');
        upsertSetting($pdo, 'smtp_encryption', trim($_POST['smtp_encryption'] ?? 'TLS'), 'SMTP encryption');
        redirectConfig('Οι ρυθμίσεις SMTP αποθηκεύτηκαν επιτυχώς.');
    }

    if ($action === 'save_maintenance') {
        upsertSetting($pdo, 'maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0', 'Maintenance mode');
        redirectConfig('Η ρύθμιση συντήρησης αποθηκεύτηκε επιτυχώς.');
    }
}

$settings = getSettingsMap($pdo);
$theme = $pdo->query('SELECT * FROM themes ORDER BY is_active DESC, id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
$moodle = $pdo->query('SELECT * FROM lms_connections ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];

$msg = $_GET['msg'] ?? '';
$msgType = $_GET['mtype'] ?? 'success';
?>
<!doctype html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <title>Admin | Configure System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/adminlte.css">
    <style>
        body { background: #f5f7fb; font-family: Arial, sans-serif; }
        .page-shell { max-width: 1180px; margin: 32px auto; padding: 0 16px; }
        .topbar, .card { background: #fff; border: 1px solid #dbe3ee; border-radius: 14px; box-shadow: 0 10px 24px rgba(15,23,42,0.06); }
        .topbar { padding: 18px 22px; margin-bottom: 18px; display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .topbar a { text-decoration: none; font-weight: 700; color: #1d4ed8; margin-right: 14px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .card { padding: 22px; }
        .section-title { margin: 0 0 14px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 6px; font-weight: 700; }
        input, textarea, select { width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; }
        textarea { min-height: 110px; resize: vertical; }
        .btn { display: inline-block; padding: 12px 16px; border: 0; border-radius: 8px; cursor: pointer; font-weight: 700; text-decoration: none; }
        .btn-primary { background: #1d4ed8; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #0f172a; }
        .alert { padding: 14px 16px; border-radius: 10px; margin-bottom: 16px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .muted { color: #64748b; }
        .badge-inline { display: inline-block; padding: 6px 10px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-weight: 700; }
        @media (max-width: 900px) {
            .grid, .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="topbar">
            <div>
                <a href="index.php">Dashboard</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="manage_recruitment.php">Manage Recruitment</a>
                <a href="configure_system.php">Configure System</a>
                <a href="report.php">Reports</a>
            </div>
            <div><a href="../../logout.php">Logout</a></div>
        </div>

        <?php if ($msg !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($msgType, ENT_QUOTES, 'UTF-8') === 'danger' ? 'danger' : 'success' ?>">
                <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <section class="card">
                <h3 class="section-title">Γενικές Ρυθμίσεις</h3>
                <form method="post">
                    <input type="hidden" name="action" value="save_general">
                    <div class="form-grid">
                        <div>
                            <label for="app_name">Όνομα Εφαρμογής</label>
                            <input id="app_name" type="text" name="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? 'Specialist Management System', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="app_subtitle">Υπότιτλος</label>
                            <input id="app_subtitle" type="text" name="app_subtitle" value="<?= htmlspecialchars($settings['app_subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="full">
                            <label for="app_description">Περιγραφή</label>
                            <textarea id="app_description" name="app_description"><?= htmlspecialchars($settings['app_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div>
                            <label for="institution_email">Email Ιδρύματος</label>
                            <input id="institution_email" type="email" name="institution_email" value="<?= htmlspecialchars($settings['institution_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="institution_phone">Τηλέφωνο Ιδρύματος</label>
                            <input id="institution_phone" type="text" name="institution_phone" value="<?= htmlspecialchars($settings['institution_phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="full">
                            <button class="btn btn-primary" type="submit">Αποθήκευση Γενικών Ρυθμίσεων</button>
                        </div>
                    </div>
                </form>
            </section>

            <section class="card">
                <h3 class="section-title">Theme / Branding</h3>
                <form method="post">
                    <input type="hidden" name="action" value="save_theme">
                    <div class="form-grid">
                        <div>
                            <label for="theme_name">Όνομα Θέματος</label>
                            <input id="theme_name" type="text" name="theme_name" value="<?= htmlspecialchars($theme['name'] ?? 'System Theme', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="logo_url">Logo URL</label>
                            <input id="logo_url" type="text" name="logo_url" value="<?= htmlspecialchars($theme['logo_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="primary_color">Κύριο Χρώμα</label>
                            <input id="primary_color" type="text" name="primary_color" value="<?= htmlspecialchars($theme['primary_color'] ?? '#007bff', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="secondary_color">Δευτερεύον Χρώμα</label>
                            <input id="secondary_color" type="text" name="secondary_color" value="<?= htmlspecialchars($theme['secondary_color'] ?? '#6c757d', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="full">
                            <label for="theme_description">Περιγραφή Θέματος</label>
                            <textarea id="theme_description" name="theme_description"><?= htmlspecialchars($theme['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div class="full">
                            <button class="btn btn-primary" type="submit">Αποθήκευση Theme</button>
                        </div>
                    </div>
                </form>
            </section>

            <section class="card">
                <h3 class="section-title">Moodle Integration</h3>
                <p class="muted">Τρέχουσα κατάσταση: <span class="badge-inline"><?= htmlspecialchars($moodle['status'] ?? 'inactive', ENT_QUOTES, 'UTF-8') ?></span></p>
                <form method="post" style="margin-bottom: 12px;">
                    <input type="hidden" name="action" value="save_moodle">
                    <div class="form-grid">
                        <div class="full">
                            <label><input type="checkbox" name="moodle_enabled" <?= (($settings['moodle_enabled'] ?? '1') === '1') ? 'checked' : '' ?>> Ενεργοποίηση σύνδεσης Moodle</label>
                        </div>
                        <div>
                            <label for="connection_name">Όνομα Σύνδεσης</label>
                            <input id="connection_name" type="text" name="connection_name" value="<?= htmlspecialchars($moodle['name'] ?? 'Moodle Main Connection', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="api_url">Moodle URL</label>
                            <input id="api_url" type="url" name="api_url" value="<?= htmlspecialchars($moodle['api_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="api_key">API Key</label>
                            <input id="api_key" type="text" name="api_key" value="<?= htmlspecialchars($moodle['api_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="api_secret">API Secret</label>
                            <input id="api_secret" type="text" name="api_secret" value="<?= htmlspecialchars($moodle['api_secret'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="full">
                            <button class="btn btn-primary" type="submit">Αποθήκευση Moodle Settings</button>
                        </div>
                    </div>
                </form>

                <form method="post">
                    <input type="hidden" name="action" value="test_moodle">
                    <input type="hidden" name="moodle_enabled" value="<?= (($settings['moodle_enabled'] ?? '1') === '1') ? '1' : '0' ?>">
                    <input type="hidden" name="connection_name" value="<?= htmlspecialchars($moodle['name'] ?? 'Moodle Main Connection', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="api_url" value="<?= htmlspecialchars($moodle['api_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="api_key" value="<?= htmlspecialchars($moodle['api_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="api_secret" value="<?= htmlspecialchars($moodle['api_secret'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button class="btn btn-secondary" type="submit">Δοκιμή Σύνδεσης Moodle</button>
                </form>
            </section>

            <section class="card">
                <h3 class="section-title">SMTP / Email</h3>
                <form method="post" style="margin-bottom: 18px;">
                    <input type="hidden" name="action" value="save_smtp">
                    <div class="form-grid">
                        <div class="full">
                            <label><input type="checkbox" name="smtp_enabled" <?= (($settings['smtp_enabled'] ?? '1') === '1') ? 'checked' : '' ?>> Ενεργοποίηση SMTP</label>
                        </div>
                        <div>
                            <label for="smtp_host">SMTP Host</label>
                            <input id="smtp_host" type="text" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="smtp_port">Port</label>
                            <input id="smtp_port" type="text" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="smtp_user">Username</label>
                            <input id="smtp_user" type="text" name="smtp_user" value="<?= htmlspecialchars($settings['smtp_user'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="smtp_pass">Password</label>
                            <input id="smtp_pass" type="text" name="smtp_pass" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="smtp_from_email">Sender Email</label>
                            <input id="smtp_from_email" type="email" name="smtp_from_email" value="<?= htmlspecialchars($settings['smtp_from_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label for="smtp_from_name">Sender Name</label>
                            <input id="smtp_from_name" type="text" name="smtp_from_name" value="<?= htmlspecialchars($settings['smtp_from_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="full">
                            <label for="smtp_encryption">Encryption</label>
                            <select id="smtp_encryption" name="smtp_encryption">
                                <?php $enc = $settings['smtp_encryption'] ?? 'TLS'; ?>
                                <option value="NONE" <?= $enc === 'NONE' ? 'selected' : '' ?>>NONE</option>
                                <option value="SSL" <?= $enc === 'SSL' ? 'selected' : '' ?>>SSL</option>
                                <option value="TLS" <?= $enc === 'TLS' ? 'selected' : '' ?>>TLS</option>
                            </select>
                        </div>
                        <div class="full">
                            <button class="btn btn-primary" type="submit">Αποθήκευση SMTP</button>
                        </div>
                    </div>
                </form>

                <form method="post">
                    <input type="hidden" name="action" value="save_maintenance">
                    <label><input type="checkbox" name="maintenance_mode" <?= (($settings['maintenance_mode'] ?? '0') === '1') ? 'checked' : '' ?>> Maintenance Mode</label>
                    <div style="margin-top: 12px;">
                        <button class="btn btn-secondary" type="submit">Αποθήκευση Maintenance Mode</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</body>
</html>

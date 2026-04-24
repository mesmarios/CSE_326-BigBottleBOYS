<?php
declare(strict_types=1);

if (!function_exists('adminGetBrandingContext')) {
    function adminGetBrandingContext(PDO $pdo): array
    {
        $defaults = [
            'app_name' => 'CareerTrack',
            'app_slogan' => 'Σύστημα Διαχείρισης Αιτήσεων',
            'app_description' => 'Σύστημα διαχείρισης αιτήσεων εκπαιδευτικού προσωπικού για ακαδημαϊκά ιδρύματα.',
            'admin_email' => 'admin@university.gr',
            'support_phone' => '+30 210 1234567',
        ];

        $settings = $defaults;
        $fallbackSettings = [
            'institution_email' => '',
            'institution_phone' => '',
        ];
        $requestedKeys = array_merge(array_keys($defaults), array_keys($fallbackSettings));
        $placeholders = implode(',', array_fill(0, count($requestedKeys), '?'));

        $stmt = $pdo->prepare(
            "SELECT setting_key, setting_value
             FROM system_settings
             WHERE setting_key IN ($placeholders)"
        );
        $stmt->execute($requestedKeys);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $key = (string)($row['setting_key'] ?? '');
            $value = trim((string)($row['setting_value'] ?? ''));

            if ($value === '') {
                continue;
            }

            if (array_key_exists($key, $settings)) {
                $settings[$key] = $value;
            } elseif (array_key_exists($key, $fallbackSettings)) {
                $fallbackSettings[$key] = $value;
            }
        }

        if ($settings['admin_email'] === $defaults['admin_email'] && $fallbackSettings['institution_email'] !== '') {
            $settings['admin_email'] = $fallbackSettings['institution_email'];
        }

        if ($settings['support_phone'] === $defaults['support_phone'] && $fallbackSettings['institution_phone'] !== '') {
            $settings['support_phone'] = $fallbackSettings['institution_phone'];
        }

        $logoFiles = glob(__DIR__ . '/../assets/images/site-logo.*');
        $faviconFiles = glob(__DIR__ . '/../assets/images/site-favicon.*');

        $logo = !empty($logoFiles)
            ? '../../assets/images/' . basename($logoFiles[0]) . '?v=' . (int)@filemtime($logoFiles[0])
            : '../../assets/images/AdminLTELogo.png';
        $favicon = !empty($faviconFiles)
            ? '../../assets/images/' . basename($faviconFiles[0]) . '?v=' . (int)@filemtime($faviconFiles[0])
            : null;

        return [
            'settings' => $settings,
            'brand_text' => $settings['app_name'],
            'logo' => $logo,
            'favicon' => $favicon,
        ];
    }
}

if (!function_exists('adminSaveGeneralSettings')) {
    function adminSaveGeneralSettings(PDO $pdo, array $settings): void
    {
        $descriptions = [
            'app_name' => 'Όνομα εφαρμογής',
            'app_slogan' => 'Υπότιτλος εφαρμογής',
            'app_description' => 'Περιγραφή εφαρμογής',
            'admin_email' => 'Email διαχειριστή',
            'support_phone' => 'Τηλέφωνο υποστήριξης',
        ];

        $selectStmt = $pdo->prepare('SELECT id FROM system_settings WHERE setting_key = ?');
        $updateStmt = $pdo->prepare(
            'UPDATE system_settings
             SET setting_value = ?, description = ?, updated_at = NOW()
             WHERE setting_key = ?'
        );
        $insertStmt = $pdo->prepare(
            'INSERT INTO system_settings (setting_key, setting_value, description)
             VALUES (?, ?, ?)'
        );

        $pdo->beginTransaction();

        try {
            foreach ($descriptions as $key => $description) {
                $value = trim((string)($settings[$key] ?? ''));
                $selectStmt->execute([$key]);
                $exists = $selectStmt->fetchColumn();

                if ($exists !== false) {
                    $updateStmt->execute([$value, $description, $key]);
                    continue;
                }

                $insertStmt->execute([$key, $value, $description]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }
}

<?php
declare(strict_types=1);

function maintenanceModeLockPath(): string
{
    return dirname(__DIR__) . '/maintenance.lock';
}

function maintenanceModeSettingKey(): string
{
    return 'maintenance_mode';
}

function maintenanceModeDescription(): string
{
    return 'Ενεργοποίηση λειτουργίας συντήρησης για όλους τους χρήστες εκτός admin.';
}

function maintenanceModeNormalizeValue(mixed $value): bool
{
    if (is_bool($value)) {
        return $value;
    }

    $normalized = strtolower(trim((string)$value));
    return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
}

function maintenanceModeGetPdo(): ?PDO
{
    static $resolved = false;
    static $cachedPdo = null;

    if ($resolved) {
        return $cachedPdo;
    }

    $resolved = true;

    if (($GLOBALS['pdo'] ?? null) instanceof PDO) {
        $cachedPdo = $GLOBALS['pdo'];
        return $cachedPdo;
    }

    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $port = defined('DB_PORT') ? (int)DB_PORT : 3306;
    $name = defined('DB_NAME') ? DB_NAME : 'bigbrothers';
    $user = defined('DB_USER') ? DB_USER : 'root';
    $pass = defined('DB_PASS') ? DB_PASS : '';

    try {
        $cachedPdo = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name),
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (Throwable $e) {
        $cachedPdo = null;
    }

    return $cachedPdo;
}

function maintenanceModeReadFromDatabase(): ?bool
{
    $pdo = maintenanceModeGetPdo();
    if (!$pdo instanceof PDO) {
        return null;
    }

    try {
        $stmt = $pdo->prepare('SELECT setting_value FROM system_settings WHERE setting_key = :setting_key LIMIT 1');
        $stmt->execute([':setting_key' => maintenanceModeSettingKey()]);
        $value = $stmt->fetchColumn();

        if ($value === false || $value === null) {
            return null;
        }

        return maintenanceModeNormalizeValue($value);
    } catch (Throwable $e) {
        return null;
    }
}

function maintenanceModeWriteToDatabase(bool $enabled): bool
{
    $pdo = maintenanceModeGetPdo();
    if (!$pdo instanceof PDO) {
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO system_settings (setting_key, setting_value, description)
             VALUES (:setting_key, :setting_value, :description)
             ON DUPLICATE KEY UPDATE
               setting_value = VALUES(setting_value),
               description = VALUES(description),
               updated_at = CURRENT_TIMESTAMP'
        );

        return $stmt->execute([
            ':setting_key' => maintenanceModeSettingKey(),
            ':setting_value' => $enabled ? '1' : '0',
            ':description' => maintenanceModeDescription(),
        ]);
    } catch (Throwable $e) {
        return false;
    }
}

function maintenanceModeSyncLockFile(bool $enabled): bool
{
    $lockPath = maintenanceModeLockPath();

    if ($enabled) {
        $result = @file_put_contents($lockPath, date('Y-m-d H:i:s'));
        clearstatcache(true, $lockPath);
        return $result !== false && is_file($lockPath);
    }

    $removed = !is_file($lockPath) || @unlink($lockPath);
    clearstatcache(true, $lockPath);
    return $removed && !is_file($lockPath);
}

function isMaintenanceModeActive(): bool
{
    $databaseValue = maintenanceModeReadFromDatabase();
    if ($databaseValue !== null) {
        return $databaseValue;
    }

    return is_file(maintenanceModeLockPath());
}

function setMaintenanceMode(bool $enabled): bool
{
    $databaseUpdated = maintenanceModeWriteToDatabase($enabled);
    $lockFileSynced = maintenanceModeSyncLockFile($enabled);

    if ($databaseUpdated) {
        return true;
    }

    return $lockFileSynced;
}

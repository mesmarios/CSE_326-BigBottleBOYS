<?php
declare(strict_types=1);
// API guide (EL/EN):
// Enrollment management endpoint with GET/POST/PUT/DELETE action routing.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
}

require_once __DIR__ . '/../database/db.php';

$currentUserId   = (int)$_SESSION['user_id'];
$currentUserRole = (string)($_SESSION['role'] ?? '');
$method          = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$body            = readRequestBody();
$action          = resolveAction($method, $body);

// REST-style method dispatch. Same endpoint, different handlers by HTTP verb.
try {
    if ($method === 'GET') {
        handleGet($pdo, $currentUserId, $currentUserRole, $action);
        exit;
    }

    if ($method === 'POST') {
        handlePost($pdo, $currentUserId, $currentUserRole, $action, $body);
        exit;
    }

    if ($method === 'PUT') {
        handlePut($pdo, $currentUserId, $currentUserRole, $action, $body);
        exit;
    }

    if ($method === 'DELETE') {
        handleDelete($pdo, $currentUserId, $currentUserRole, $action, $body);
        exit;
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (Throwable $e) {
    jsonResponse(['success' => false, 'error' => 'Enrollment API error: ' . $e->getMessage()], 500);
}

function handleGet(PDO $pdo, int $currentUserId, string $currentUserRole, string $action): void
{
    // GET: read-only operations (users, courses, logs, settings, my_access).
    $isAdminHr = in_array($currentUserRole, ['admin', 'hr'], true);

    if ($action === 'courses') {
        if (!$isAdminHr) {
            jsonResponse(['success' => false, 'error' => 'Forbidden'], 403);
        }
        $rows = $pdo->query('SELECT id, code, name FROM courses ORDER BY name ASC')->fetchAll();
        jsonResponse(['success' => true, 'courses' => $rows]);
    }

    if ($action === 'settings') {
        if (!$isAdminHr) {
            jsonResponse(['success' => false, 'error' => 'Forbidden'], 403);
        }
        jsonResponse(['success' => true, 'settings' => getSyncSettings($pdo)]);
    }

    if ($action === 'logs') {
        if (!$isAdminHr) {
            jsonResponse(['success' => false, 'error' => 'Forbidden'], 403);
        }
        $limit = max(1, min((int)($_GET['limit'] ?? 50), 200));
        $stmt = $pdo->prepare(
            "SELECT el.id, el.user_id, el.action, el.action_type, el.target_id, el.target_type,
                    el.details, el.status, el.error_message, el.performed_by, el.created_at
             FROM enrollment_logs el
             ORDER BY el.id DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            if (is_string($row['details']) && $row['details'] !== '') {
                $decoded = json_decode($row['details'], true);
                $row['details'] = is_array($decoded) ? $decoded : $row['details'];
            }
        }
        unset($row);

        jsonResponse(['success' => true, 'logs' => $rows]);
    }

    if ($action === 'my_access') {
        $targetUserId = (int)($_GET['target_user_id'] ?? 0);
        if ($targetUserId <= 0) {
            $targetUserId = $currentUserId;
        }

        if (!$isAdminHr && $targetUserId !== $currentUserId) {
            jsonResponse(['success' => false, 'error' => 'Forbidden'], 403);
        }

        $access = getLatestAccessForUser($pdo, $targetUserId);
        jsonResponse(['success' => true, 'access' => $access]);
    }

    if ($action === 'list_users' || $action === '') {
        if (!$isAdminHr) {
            jsonResponse(['success' => false, 'error' => 'Forbidden'], 403);
        }
        $keyword = trim((string)($_GET['q'] ?? ''));
        $status  = (string)($_GET['status'] ?? '');
        if (!in_array($status, ['active', 'inactive', 'none', ''], true)) {
            $status = '';
        }

        $sql = "SELECT
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    la.status AS lms_status,
                    la.course_id AS lms_course_id,
                    la.granted_at,
                    la.updated_at,
                    c.name AS course_name
                FROM users u
                LEFT JOIN lms_access la
                    ON la.id = (
                        SELECT la2.id
                        FROM lms_access la2
                        WHERE la2.user_id = u.id
                        ORDER BY (la2.status = 'active') DESC, la2.updated_at DESC, la2.id DESC
                        LIMIT 1
                    )
                LEFT JOIN courses c ON c.id = la.course_id
                WHERE u.role = 'ee_hired'";

        $params = [];
        if ($keyword !== '') {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        if ($status === 'active') {
            $sql .= " AND la.status = 'active'";
        } elseif ($status === 'inactive') {
            $sql .= " AND la.status = 'inactive'";
        } elseif ($status === 'none') {
            $sql .= " AND la.id IS NULL";
        }

        $sql .= " ORDER BY u.last_name ASC, u.first_name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        jsonResponse(['success' => true, 'users' => $rows]);
    }

    jsonResponse(['success' => false, 'error' => 'Unknown action'], 400);
}

function handlePost(PDO $pdo, int $currentUserId, string $currentUserRole, string $action, array $body): void
{
    // POST: create/trigger actions (enable, disable, change_course, force_sync).
    enforceAdminHr($currentUserRole);

    if (in_array($action, ['enable', 'create_access'], true)) {
        $targetUserId = (int)requestValue('target_user_id', $body, 0);
        $courseId = (int)requestValue('target_course_id', $body, 0);
        if ($targetUserId <= 0 || $courseId <= 0) {
            jsonResponse(['success' => false, 'error' => 'Missing target_user_id or target_course_id'], 422);
        }
        $access = setUserAccess($pdo, $targetUserId, $courseId, $currentUserId, 'manual_enroll', 'Enable LMS access');
        jsonResponse(['success' => true, 'message' => 'Access enabled', 'access' => $access]);
    }

    if ($action === 'change_course') {
        $targetUserId = (int)requestValue('target_user_id', $body, 0);
        $courseId = (int)requestValue('target_course_id', $body, 0);
        if ($targetUserId <= 0 || $courseId <= 0) {
            jsonResponse(['success' => false, 'error' => 'Missing target_user_id or target_course_id'], 422);
        }
        $access = setUserAccess($pdo, $targetUserId, $courseId, $currentUserId, 'manual_enroll', 'Change LMS course');
        jsonResponse(['success' => true, 'message' => 'Course changed', 'access' => $access]);
    }

    if ($action === 'disable') {
        $targetUserId = (int)requestValue('target_user_id', $body, 0);
        if ($targetUserId <= 0) {
            jsonResponse(['success' => false, 'error' => 'Missing target_user_id'], 422);
        }
        disableUserAccess($pdo, $targetUserId, $currentUserId);
        jsonResponse(['success' => true, 'message' => 'Access disabled']);
    }

    if ($action === 'force_sync') {
        $result = runFullSync($pdo, $currentUserId);
        jsonResponse(['success' => true, 'message' => 'Full sync completed', 'result' => $result]);
    }

    if ($action === 'toggle_auto') {
        $newValue = toggleAutoSync($pdo, $currentUserId);
        jsonResponse(['success' => true, 'message' => 'Auto sync updated', 'auto_sync_enabled' => $newValue]);
    }

    jsonResponse(['success' => false, 'error' => 'Unknown action'], 400);
}

function handlePut(PDO $pdo, int $currentUserId, string $currentUserRole, string $action, array $body): void
{
    // PUT: update existing access assignment for a user.
    enforceAdminHr($currentUserRole);

    if ($action !== 'update_access' && $action !== 'change_course') {
        jsonResponse(['success' => false, 'error' => 'Unknown action'], 400);
    }

    $targetUserId = (int)requestValue('target_user_id', $body, 0);
    $courseId = (int)requestValue('target_course_id', $body, 0);
    if ($targetUserId <= 0 || $courseId <= 0) {
        jsonResponse(['success' => false, 'error' => 'Missing target_user_id or target_course_id'], 422);
    }

    $access = setUserAccess($pdo, $targetUserId, $courseId, $currentUserId, 'manual_enroll', 'Update LMS access');
    jsonResponse(['success' => true, 'message' => 'Access updated', 'access' => $access]);
}

function handleDelete(PDO $pdo, int $currentUserId, string $currentUserRole, string $action, array $body): void
{
    // DELETE: logical delete (set access inactive), not physical row removal.
    enforceAdminHr($currentUserRole);

    if ($action !== 'delete_access' && $action !== 'disable') {
        jsonResponse(['success' => false, 'error' => 'Unknown action'], 400);
    }

    $targetUserId = (int)requestValue('target_user_id', $body, 0);
    if ($targetUserId <= 0) {
        jsonResponse(['success' => false, 'error' => 'Missing target_user_id'], 422);
    }

    disableUserAccess($pdo, $targetUserId, $currentUserId);
    jsonResponse(['success' => true, 'message' => 'Access deleted (soft delete: status=inactive)']);
}

function setUserAccess(
    PDO $pdo,
    int $targetUserId,
    int $courseId,
    int $performedBy,
    string $actionType,
    string $actionLabel
): array {
    ensureEeHiredUser($pdo, $targetUserId);
    ensureCourseExists($pdo, $courseId);

    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            "UPDATE lms_access
             SET status='inactive', revoked_at=NOW(), updated_at=NOW()
             WHERE user_id = ? AND status = 'active' AND course_id <> ?"
        )->execute([$targetUserId, $courseId]);

        $pdo->prepare(
            "INSERT INTO lms_access (user_id, course_id, status, granted_at, revoked_at)
             VALUES (?, ?, 'active', NOW(), NULL)
             ON DUPLICATE KEY UPDATE
                 status='active',
                 granted_at=NOW(),
                 revoked_at=NULL,
                 updated_at=NOW()"
        )->execute([$targetUserId, $courseId]);

        $pdo->prepare(
            "UPDATE specialist_enrollments
             SET access_status = 'inactive', updated_at = NOW()
             WHERE user_id = ? AND access_status = 'active' AND course_id <> ?"
        )->execute([$targetUserId, $courseId]);

        $updateSe = $pdo->prepare(
            "UPDATE specialist_enrollments
             SET access_status='active', enrolled_at=COALESCE(enrolled_at, NOW()), updated_at=NOW()
             WHERE user_id=? AND course_id=?
             ORDER BY id DESC
             LIMIT 1"
        );
        $updateSe->execute([$targetUserId, $courseId]);
        if ($updateSe->rowCount() === 0) {
            $pdo->prepare(
                "INSERT INTO specialist_enrollments (user_id, course_id, access_status, enrolled_at)
                 VALUES (?, ?, 'active', NOW())"
            )->execute([$targetUserId, $courseId]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        writeEnrollmentLog(
            $pdo,
            $targetUserId,
            $actionLabel,
            $actionType,
            $courseId,
            'course',
            ['target_user_id' => $targetUserId, 'target_course_id' => $courseId],
            'failed',
            $performedBy,
            $e->getMessage()
        );
        throw $e;
    }

    $access = getLatestAccessForUser($pdo, $targetUserId);
    writeEnrollmentLog(
        $pdo,
        $targetUserId,
        $actionLabel,
        $actionType,
        $courseId,
        'course',
        ['target_user_id' => $targetUserId, 'target_course_id' => $courseId],
        'success',
        $performedBy
    );

    return $access ?? [];
}

function disableUserAccess(PDO $pdo, int $targetUserId, int $performedBy): void
{
    ensureEeHiredUser($pdo, $targetUserId);

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "UPDATE lms_access
             SET status='inactive', revoked_at=NOW(), updated_at=NOW()
             WHERE user_id=? AND status='active'"
        );
        $stmt->execute([$targetUserId]);

        $pdo->prepare(
            "UPDATE specialist_enrollments
             SET access_status='inactive', updated_at=NOW()
             WHERE user_id=? AND access_status='active'"
        )->execute([$targetUserId]);

        $changedRows = $stmt->rowCount();
        $pdo->commit();

        writeEnrollmentLog(
            $pdo,
            $targetUserId,
            'Disable LMS access',
            'manual_unenroll',
            null,
            'user',
            ['target_user_id' => $targetUserId, 'changed_rows' => $changedRows],
            'success',
            $performedBy
        );
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        writeEnrollmentLog(
            $pdo,
            $targetUserId,
            'Disable LMS access',
            'manual_unenroll',
            null,
            'user',
            ['target_user_id' => $targetUserId],
            'failed',
            $performedBy,
            $e->getMessage()
        );
        throw $e;
    }
}

function runFullSync(PDO $pdo, int $performedBy): array
{
    $now = date('Y-m-d H:i:s');
    $eeIds = $pdo->query("SELECT id FROM users WHERE role='ee_hired'")->fetchAll(PDO::FETCH_COLUMN);
    $processed = 0;

    $pdo->beginTransaction();
    try {
        $updAccess = $pdo->prepare("UPDATE lms_access SET updated_at = NOW() WHERE user_id = ?");
        $updEnroll = $pdo->prepare("UPDATE specialist_enrollments SET updated_at = NOW() WHERE user_id = ?");
        foreach ($eeIds as $uid) {
            $targetId = (int)$uid;
            $updAccess->execute([$targetId]);
            $updEnroll->execute([$targetId]);
            $processed++;
        }

        $logMessage = sprintf('[%s] Full sync completed. %d EE records processed.', $now, $processed);
        upsertSystemSetting(
            $pdo,
            'lms_last_sync_at',
            $now,
            'Latest LMS sync timestamp (ISO)'
        );
        upsertSystemSetting(
            $pdo,
            'lms_last_sync_log',
            $logMessage,
            'Log for latest LMS sync'
        );

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        writeEnrollmentLog(
            $pdo,
            $performedBy,
            'Run full LMS sync',
            'full_sync',
            null,
            'sync',
            ['processed' => $processed],
            'failed',
            $performedBy,
            $e->getMessage()
        );
        throw $e;
    }

    writeEnrollmentLog(
        $pdo,
        $performedBy,
        'Run full LMS sync',
        'full_sync',
        null,
        'sync',
        ['processed' => $processed],
        'success',
        $performedBy
    );

    return [
        'processed' => $processed,
        'last_sync_at' => $now,
    ];
}

function toggleAutoSync(PDO $pdo, int $performedBy): string
{
    $settings = getSyncSettings($pdo);
    $current = $settings['lms_auto_sync_enabled'] ?? '0';
    $next = ($current === '1') ? '0' : '1';

    upsertSystemSetting(
        $pdo,
        'lms_auto_sync_enabled',
        $next,
        'Automatic LMS sync flag (0=off,1=on)'
    );

    writeEnrollmentLog(
        $pdo,
        $performedBy,
        'Toggle auto LMS sync',
        'auto_sync',
        null,
        'sync',
        ['old_value' => $current, 'new_value' => $next],
        'success',
        $performedBy
    );

    return $next;
}

function getSyncSettings(PDO $pdo): array
{
    $defaults = [
        'lms_auto_sync_enabled' => '0',
        'lms_last_sync_at' => '',
        'lms_last_sync_log' => '',
    ];

    $stmt = $pdo->prepare(
        "SELECT setting_key, setting_value
         FROM system_settings
         WHERE setting_key IN ('lms_auto_sync_enabled','lms_last_sync_at','lms_last_sync_log')"
    );
    $stmt->execute();
    foreach ($stmt->fetchAll() as $row) {
        $defaults[(string)$row['setting_key']] = (string)$row['setting_value'];
    }

    return $defaults;
}

function getLatestAccessForUser(PDO $pdo, int $targetUserId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT la.id, la.user_id, la.course_id, la.status, la.granted_at, la.revoked_at, la.updated_at,
                c.name AS course_name, c.code AS course_code
         FROM lms_access la
         LEFT JOIN courses c ON c.id = la.course_id
         WHERE la.user_id = ?
         ORDER BY (la.status='active') DESC, la.updated_at DESC, la.id DESC
         LIMIT 1"
    );
    $stmt->execute([$targetUserId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function upsertSystemSetting(PDO $pdo, string $key, string $value, string $description): void
{
    $stmt = $pdo->prepare(
        "INSERT INTO system_settings (setting_key, setting_value, description)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()"
    );
    $stmt->execute([$key, $value, $description]);
}

function ensureEeHiredUser(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'ee_hired' LIMIT 1");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'Target user not found or not ee_hired'], 404);
    }
}

function ensureCourseExists(PDO $pdo, int $courseId): void
{
    $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? LIMIT 1");
    $stmt->execute([$courseId]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'Target course not found'], 404);
    }
}

function writeEnrollmentLog(
    PDO $pdo,
    int $userId,
    string $action,
    string $actionType,
    ?int $targetId,
    ?string $targetType,
    ?array $details,
    string $status,
    ?int $performedBy,
    ?string $errorMessage = null
): void {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO enrollment_logs
                (user_id, action, action_type, target_id, target_type, details, status, error_message, performed_by)
             VALUES
                (:user_id, :action, :action_type, :target_id, :target_type, :details, :status, :error_message, :performed_by)"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':action', $action, PDO::PARAM_STR);
        $stmt->bindValue(':action_type', $actionType, PDO::PARAM_STR);
        $stmt->bindValue(':target_id', $targetId, $targetId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':target_type', $targetType, $targetType === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':details', $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null, $details ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':error_message', $errorMessage, $errorMessage === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':performed_by', $performedBy, $performedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
    } catch (Throwable $e) {
        // Logging should never break the API response.
    }
}

function enforceAdminHr(string $currentUserRole): void
{
    if (!in_array($currentUserRole, ['admin', 'hr'], true)) {
        jsonResponse(['success' => false, 'error' => 'Forbidden'], 403);
    }
}

function resolveAction(string $method, array $body): string
{
    if ($method === 'GET') {
        return trim((string)($_GET['action'] ?? ''));
    }

    if ($method === 'POST') {
        $postAction = $_POST['action'] ?? $_POST['lms_action'] ?? $_POST['sync_action'] ?? '';
        if ($postAction !== '') {
            return trim((string)$postAction);
        }
        return trim((string)($body['action'] ?? ''));
    }

    return trim((string)($_GET['action'] ?? ($body['action'] ?? '')));
}

function readRequestBody(): array
{
    $raw = file_get_contents('php://input');
    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }

    $contentTypeRaw = (string)($_SERVER['CONTENT_TYPE'] ?? '');
    $contentType = strtolower(trim(explode(';', $contentTypeRaw)[0]));
    if ($contentType === 'application/json') {
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return $decoded;
    }

    parse_str($raw, $parsed);
    return is_array($parsed) ? $parsed : [];
}

function requestValue(string $key, array $body, mixed $default = null): mixed
{
    if (array_key_exists($key, $_POST)) {
        return $_POST[$key];
    }
    if (array_key_exists($key, $body)) {
        return $body[$key];
    }
    if (array_key_exists($key, $_GET)) {
        return $_GET[$key];
    }
    return $default;
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

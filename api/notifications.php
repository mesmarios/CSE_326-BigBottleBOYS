<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../database/db.php';

$userId = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleGet($pdo, $userId);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'mark_read') {
    handleMarkRead($pdo, $userId);
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

/* ── GET: return notifications for the user ──────────────────────── */
function handleGet(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare("
        SELECT id, title, message, is_read, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'id'      => (int)$r['id'],
            'text'    => htmlspecialchars($r['title'] . (($r['message'] ?? '') !== '' ? ': ' . $r['message'] : ''), ENT_QUOTES, 'UTF-8'),
            'date'    => $r['created_at'],
            'read'    => (bool)$r['is_read'],
        ];
    }

    $unread = count(array_filter($out, fn($n) => !$n['read']));
    echo json_encode(['success' => true, 'notifications' => $out, 'unread' => $unread]);
}

/* ── POST mark_read: mark one or all notifications as read ───────── */
function handleMarkRead(PDO $pdo, int $userId): void {
    $body = json_decode(file_get_contents('php://input'), true);
    $notifId = isset($body['id']) ? (int)$body['id'] : null;

    if ($notifId) {
        $stmt = $pdo->prepare("
            UPDATE notifications SET is_read = 1, read_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notifId, $userId]);
    } else {
        // Mark all as read
        $stmt = $pdo->prepare("
            UPDATE notifications SET is_read = 1, read_at = NOW()
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
    }

    echo json_encode(['success' => true]);
}

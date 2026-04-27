<?php
// API guide (EL/EN): admin notifications endpoint (currently mark_all_read action).
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../database/db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'mark_all_read') {
    // Bulk update unread notifications for current admin only.
    $adminId = (int)$_SESSION['user_id'];
    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1, read_at = NOW()
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$adminId]);
    echo json_encode(['success' => true, 'updated' => $stmt->rowCount()]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);

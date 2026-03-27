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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleGet($pdo, $userId);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePost($pdo, $userId);
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

/* ── GET: return profile + academic data ─────────────────────────── */
function handleGet(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare("
        SELECT first_name, last_name, email, phone, address, profile_data
        FROM users WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $profileData = $user['profile_data'] ? json_decode($user['profile_data'], true) : [];

    echo json_encode([
        'success'      => true,
        'first_name'   => $user['first_name'],
        'last_name'    => $user['last_name'],
        'email'        => $user['email'],
        'phone'        => $user['phone']   ?? '',
        'address'      => $user['address'] ?? '',
        'profile_data' => $profileData,
    ]);
}

/* ── POST: update user info + academic data ──────────────────────── */
function handlePost(PDO $pdo, int $userId): void {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
        return;
    }

    // Sanitise basic fields
    $firstName = trim($body['first_name'] ?? '');
    $lastName  = trim($body['last_name']  ?? '');
    $phone     = trim($body['phone']      ?? '');
    $address   = trim($body['address']    ?? '');

    if ($firstName === '' || $lastName === '') {
        echo json_encode(['success' => false, 'error' => 'Name fields are required']);
        return;
    }

    // Academic / extra data goes in profile_data JSON column
    $pd = $body['profile_data'] ?? [];
    $profileData = json_encode([
        'dob'            => $pd['dob']            ?? '',
        'degree'         => $pd['degree']         ?? '',
        'institution'    => $pd['institution']    ?? '',
        'specialization' => $pd['specialization'] ?? '',
        'experience'     => $pd['experience']     ?? '',
        'summary'        => $pd['summary']        ?? '',
    ]);

    $stmt = $pdo->prepare("
        UPDATE users
        SET first_name = ?, last_name = ?, phone = ?, address = ?,
            profile_data = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$firstName, $lastName, $phone, $address, $profileData, $userId]);

    // Keep session first_name/last_name in sync
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name']  = $lastName;

    echo json_encode(['success' => true]);
}

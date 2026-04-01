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
        SELECT first_name, last_name, email, phone, address,
               dob, degree, institution, specialization, experience, summary
        FROM users WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    echo json_encode([
        'success'    => true,
        'first_name' => $user['first_name'],
        'last_name'  => $user['last_name'],
        'email'      => $user['email'],
        'phone'      => $user['phone']   ?? '',
        'address'    => $user['address'] ?? '',
        'profile_data' => [
            'dob'            => $user['dob']            ?? '',
            'degree'         => $user['degree']         ?? '',
            'institution'    => $user['institution']    ?? '',
            'specialization' => $user['specialization'] ?? '',
            'experience'     => $user['experience'] !== null ? (string)$user['experience'] : '',
            'summary'        => $user['summary']        ?? '',
        ],
    ]);
}

/* ── POST: update user info + academic data ──────────────────────── */
function handlePost(PDO $pdo, int $userId): void {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
        return;
    }

    $firstName = trim($body['first_name'] ?? '');
    $lastName  = trim($body['last_name']  ?? '');
    $phone     = trim($body['phone']      ?? '');
    $address   = trim($body['address']    ?? '');

    if ($firstName === '' || $lastName === '') {
        echo json_encode(['success' => false, 'error' => 'Name fields are required']);
        return;
    }

    $pd             = $body['profile_data'] ?? [];
    $dob            = trim($pd['dob']            ?? '') ?: null;
    $degree         = trim($pd['degree']         ?? '') ?: null;
    $institution    = trim($pd['institution']    ?? '') ?: null;
    $specialization = trim($pd['specialization'] ?? '') ?: null;
    $experience     = ($pd['experience'] ?? '') !== '' ? (int)$pd['experience'] : null;
    $summary        = trim($pd['summary']        ?? '') ?: null;

    $stmt = $pdo->prepare("
        UPDATE users
        SET first_name = ?, last_name = ?, phone = ?, address = ?,
            dob = ?, degree = ?, institution = ?,
            specialization = ?, experience = ?, summary = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $firstName, $lastName, $phone, $address,
        $dob, $degree, $institution, $specialization, $experience, $summary,
        $userId,
    ]);

    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name']  = $lastName;

    echo json_encode(['success' => true]);
}

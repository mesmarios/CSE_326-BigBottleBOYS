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
    try {
        handleGet($pdo, $userId);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to load profile data']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        handlePost($pdo, $userId);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Η ενημέρωση προφίλ απέτυχε.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function buildAvatarSrc(string $binary, ?string $mimeType): string {
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $safeMimeType = in_array((string)$mimeType, $allowedMimeTypes, true)
        ? (string)$mimeType
        : 'image/jpeg';

    return 'data:' . $safeMimeType . ';base64,' . base64_encode($binary);
}

function getUsersTableColumns(PDO $pdo): array {
    static $columns = null;

    if (is_array($columns)) {
        return $columns;
    }

    $stmt = $pdo->query('SHOW COLUMNS FROM users');
    $columns = [];

    foreach ($stmt->fetchAll() as $row) {
        if (isset($row['Field'])) {
            $columns[] = (string)$row['Field'];
        }
    }

    return $columns;
}

function userColumnExists(PDO $pdo, string $column): bool {
    return in_array($column, getUsersTableColumns($pdo), true);
}

function avatarPublicUrlFromRelativePath(string $relativePath): ?string {
    $normalizedPath = ltrim(str_replace('\\', '/', trim($relativePath)), '/');
    if ($normalizedPath === '' || !str_starts_with($normalizedPath, 'uploads/profile_pics/')) {
        return null;
    }

    $absoluteBaseDir = realpath(__DIR__ . '/../uploads/profile_pics');
    if ($absoluteBaseDir === false) {
        return null;
    }

    $absoluteFilePath = realpath(__DIR__ . '/../' . $normalizedPath);
    if ($absoluteFilePath === false || !is_file($absoluteFilePath)) {
        return null;
    }

    if (strpos($absoluteFilePath, $absoluteBaseDir . DIRECTORY_SEPARATOR) !== 0) {
        return null;
    }

    $version = @filemtime($absoluteFilePath) ?: time();
    $encodedPath = implode('/', array_map('rawurlencode', explode('/', $normalizedPath)));
    return '../../' . $encodedPath . '?v=' . $version;
}

function resolveAvatarFileUrl(int $userId, ?string $storedPath = null): ?string {
    if (is_string($storedPath) && trim($storedPath) !== '') {
        $storedUrl = avatarPublicUrlFromRelativePath($storedPath);
        if ($storedUrl !== null) {
            return $storedUrl;
        }
    }

    if ($userId <= 0) {
        return null;
    }

    $matches = glob(__DIR__ . '/../uploads/profile_pics/user_' . $userId . '.*');
    if (!is_array($matches) || $matches === []) {
        return null;
    }

    $filePath = $matches[0];
    $basename = basename($filePath);
    $version = @filemtime($filePath) ?: time();
    return '../../uploads/profile_pics/' . rawurlencode($basename) . '?v=' . $version;
}

function uploadErrorMessage(int $uploadError): string {
    return match ($uploadError) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The file is too large. It exceeds server or form upload limits.',
        UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Please try again.',
        UPLOAD_ERR_NO_FILE => 'No image file selected.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server misconfiguration: missing temporary upload folder.',
        UPLOAD_ERR_CANT_WRITE => 'Server could not write the uploaded file to disk.',
        UPLOAD_ERR_EXTENSION => 'A server extension blocked the upload.',
        default => 'Upload failed. Please try again.',
    };
}

function isValidStrongPassword(string $password): bool {
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password) === 1
        && preg_match('/[a-z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1
        && preg_match('/[!@#$%^&*()_+\-=]/', $password) === 1;
}

function handlePasswordChange(PDO $pdo, int $userId): void {
    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Συμπληρώστε όλα τα πεδία κωδικού.']);
        return;
    }

    if ($newPassword !== $confirmPassword) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Ο νέος κωδικός και η επιβεβαίωση δεν ταιριάζουν.']);
        return;
    }

    if (!isValidStrongPassword($newPassword)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Ο νέος κωδικός δεν καλύπτει όλες τις απαιτήσεις ασφαλείας.']);
        return;
    }

    $passwordStmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
    $passwordStmt->execute([':id' => $userId]);
    $passwordRow = $passwordStmt->fetch();

    if (!$passwordRow || !password_verify($currentPassword, (string)$passwordRow['password_hash'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Ο τρέχων κωδικός δεν είναι σωστός.']);
        return;
    }

    if (password_verify($newPassword, (string)$passwordRow['password_hash'])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Ο νέος κωδικός πρέπει να είναι διαφορετικός από τον τρέχοντα.']);
        return;
    }

    $updates = ['password_hash = :password_hash'];
    if (userColumnExists($pdo, 'updated_at')) {
        $updates[] = 'updated_at = NOW()';
    }

    $updateStmt = $pdo->prepare(
        'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = :id'
    );
    $updateStmt->execute([
        ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ':id' => $userId,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Ο κωδικός πρόσβασης άλλαξε επιτυχώς.',
    ]);
}

function handleAvatarUpload(PDO $pdo, int $userId): void {
    if (!isset($_FILES['avatar']) || !is_array($_FILES['avatar'])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'No image file selected']);
        return;
    }

    $avatarFile = $_FILES['avatar'];
    $uploadError = (int)($avatarFile['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError !== UPLOAD_ERR_OK) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => uploadErrorMessage($uploadError)]);
        return;
    }

    $tmpPath = (string)($avatarFile['tmp_name'] ?? '');
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Invalid uploaded file']);
        return;
    }

    $maxBytes = 8 * 1024 * 1024;
    $fileSize = (int)($avatarFile['size'] ?? 0);
    if ($fileSize <= 0 || $fileSize > $maxBytes) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Image must be up to 8MB']);
        return;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMimeType = $finfo ? finfo_file($finfo, $tmpPath) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array((string)$detectedMimeType, $allowedMimeTypes, true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Only JPG, PNG, GIF or WEBP images are allowed']);
        return;
    }

    $extByMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $extension = $extByMime[(string)$detectedMimeType] ?? 'jpg';
    $avatarDir = __DIR__ . '/../uploads/profile_pics';
    if (!is_dir($avatarDir) && !@mkdir($avatarDir, 0775, true) && !is_dir($avatarDir)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to create profile image directory']);
        return;
    }

    $existingFiles = glob($avatarDir . '/user_' . $userId . '.*');
    if (is_array($existingFiles)) {
        foreach ($existingFiles as $existingFile) {
            @unlink($existingFile);
        }
    }

    $targetPath = $avatarDir . '/user_' . $userId . '.' . $extension;
    $savedToFile = move_uploaded_file($tmpPath, $targetPath);
    if (!$savedToFile) {
        $savedToFile = @copy($tmpPath, $targetPath);
    }

    if (!$savedToFile) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to save image file on server']);
        return;
    }

    $binaryData = file_get_contents($targetPath);
    if ($binaryData === false || $binaryData === '') {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to read image data']);
        return;
    }

    $relativeAvatarPath = 'uploads/profile_pics/' . basename($targetPath);
    $setParts = ['profilepic = :profilepic'];
    if (userColumnExists($pdo, 'profilepic_mime')) {
        $setParts[] = 'profilepic_mime = :profilepic_mime';
    }
    if (userColumnExists($pdo, 'profilepic_path')) {
        $setParts[] = 'profilepic_path = :profilepic_path';
    }
    if (userColumnExists($pdo, 'updated_at')) {
        $setParts[] = 'updated_at = NOW()';
    }

    $stmt = $pdo->prepare(
        'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE id = :id'
    );
    $stmt->bindValue(':profilepic', $binaryData, PDO::PARAM_LOB);
    if (userColumnExists($pdo, 'profilepic_mime')) {
        $stmt->bindValue(':profilepic_mime', (string)$detectedMimeType, PDO::PARAM_STR);
    }
    if (userColumnExists($pdo, 'profilepic_path')) {
        $stmt->bindValue(':profilepic_path', $relativeAvatarPath, PDO::PARAM_STR);
    }
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'avatar_src' => resolveAvatarFileUrl($userId, $relativeAvatarPath) ?? buildAvatarSrc($binaryData, (string)$detectedMimeType),
        'avatar_path' => $relativeAvatarPath,
    ]);
}

/* ── GET: return profile + academic data ─────────────────────────── */
function handleGet(PDO $pdo, int $userId): void {
    $availableColumns = getUsersTableColumns($pdo);
    $candidateColumns = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'dob',
        'degree',
        'institution',
        'specialization',
        'experience',
        'summary',
        'profilepic',
        'profilepic_mime',
        'profilepic_path',
    ];
    $selectColumns = [];
    foreach ($candidateColumns as $column) {
        if (in_array($column, $availableColumns, true)) {
            $selectColumns[] = $column;
        }
    }

    if ($selectColumns === []) {
        $selectColumns[] = 'id';
    }

    $stmt = $pdo->prepare(
        'SELECT ' . implode(', ', $selectColumns) . ' FROM users WHERE id = ?'
    );
    $stmt->execute([$userId]);
    $user = $stmt->fetch() ?: [];

    $avatarFileUrl = resolveAvatarFileUrl($userId, isset($user['profilepic_path']) ? (string)$user['profilepic_path'] : null);

    echo json_encode([
        'success'    => true,
        'first_name' => $user['first_name'] ?? '',
        'last_name'  => $user['last_name'] ?? '',
        'email'      => $user['email'] ?? '',
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
        'avatar_src' => $avatarFileUrl
            ?? (!empty($user['profilepic'])
                ? buildAvatarSrc((string)$user['profilepic'], (string)($user['profilepic_mime'] ?? ''))
                : null),
    ]);
}

/* ── POST: update user info + academic data ──────────────────────── */
function handlePost(PDO $pdo, int $userId): void {
    if (isset($_POST['action']) && $_POST['action'] === 'update_avatar') {
        handleAvatarUpload($pdo, $userId);
        return;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        handlePasswordChange($pdo, $userId);
        return;
    }

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

    $updates = [];
    $params = [];

    $fieldMap = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'phone' => $phone,
        'address' => $address,
        'dob' => $dob,
        'degree' => $degree,
        'institution' => $institution,
        'specialization' => $specialization,
        'experience' => $experience,
        'summary' => $summary,
    ];

    foreach ($fieldMap as $column => $value) {
        if (userColumnExists($pdo, $column)) {
            $updates[] = $column . ' = ?';
            $params[] = $value;
        }
    }

    if (userColumnExists($pdo, 'updated_at')) {
        $updates[] = 'updated_at = NOW()';
    }

    if ($updates === []) {
        echo json_encode(['success' => false, 'error' => 'No compatible profile columns found']);
        return;
    }

    $params[] = $userId;
    $stmt = $pdo->prepare('UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?');
    $stmt->execute($params);

    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name']  = $lastName;

    echo json_encode(['success' => true]);
}

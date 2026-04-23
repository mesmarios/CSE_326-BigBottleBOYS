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
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'save_draft') {
        handleSaveDraft($pdo, $userId);
    } elseif ($action === 'submit') {
        handleSubmit($pdo, $userId);
    } elseif ($action === 'delete') {
        handleDelete($pdo, $userId);
    } else {
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

/* ── Map DB status enum → JS display string ─────────────────────── */
function dbStatusToJs(string $s): string {
    $map = [
        'draft'        => 'Draft',
        'submitted'    => 'Submitted',
        'under_review' => 'Under Review',
        'accepted'     => 'Approved',
        'rejected'     => 'Rejected',
        'withdrawn'    => 'Withdrawn',
    ];
    return $map[$s] ?? 'Draft';
}

/* ── Collect sup paths from individual columns into an array ──────── */
function getSupPaths(array $row): array {
    $paths = [];
    for ($i = 1; $i <= 5; $i++) {
        $col = 'app_sup_path_' . $i;
        if (!empty($row[$col])) {
            $paths[] = $row[$col];
        }
    }
    return $paths;
}

/* ── GET: list all applications for the current user ────────────── */
function handleGet(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare("
        SELECT
            ca.id,
            ca.announcement_id,
            ca.status,
            ca.submitted_at,
            ca.created_at,
            ca.app_phone,
            ca.app_degree,
            ca.app_institution,
            ca.app_specialization,
            ca.app_experience,
            ca.app_summary,
            ca.app_declared,
            ca.app_cv_path,
            ca.app_cl_path,
            ca.app_sup_path_1,
            ca.app_sup_path_2,
            ca.app_sup_path_3,
            ca.app_sup_path_4,
            ca.app_sup_path_5,
            ja.title,
            s.name  AS school_name,
            d.name  AS department_name,
            c.code  AS course_code,
            c.name  AS course_name,
            rp.start_date,
            rp.end_date
        FROM candidate_applications ca
        JOIN job_announcements ja     ON ca.announcement_id = ja.id
        JOIN schools s                ON ja.school_id       = s.id
        JOIN departments d            ON ja.department_id   = d.id
        JOIN courses c                ON ja.course_id       = c.id
        JOIN recruitment_periods rp   ON ja.period_id       = rp.id
        WHERE ca.candidate_id = ?
        ORDER BY ca.created_at DESC
    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    $out = [];
    foreach ($rows as $r) {
        $supPaths = getSupPaths($r);

        $cvFileData  = !empty($r['app_cv_path']) ? '../../api/download.php?app_id=' . $r['id'] . '&type=cv'  : null;
        $clFileData  = !empty($r['app_cl_path']) ? '../../api/download.php?app_id=' . $r['id'] . '&type=cl'  : null;
        $supFilesData = [];
        foreach (array_keys($supPaths) as $i) {
            $supFilesData[] = '../../api/download.php?app_id=' . $r['id'] . '&type=sup&idx=' . $i;
        }

        $out[] = [
            'id'            => (int)$r['id'],
            'callId'        => (int)$r['announcement_id'],
            'status'        => dbStatusToJs($r['status']),
            'submittedDate' => $r['submitted_at'] ? date('Y-m-d', strtotime($r['submitted_at'])) : null,
            'savedAt'       => $r['created_at'],
            'callInfo'      => [
                'id'        => (int)$r['announcement_id'],
                'title'     => $r['title'],
                'department'=> $r['department_name'],
                'school'    => $r['school_name'],
                'courses'   => [$r['course_code'] . ' - ' . $r['course_name']],
                'startDate' => $r['start_date'],
                'endDate'   => $r['end_date'],
            ],
            'data' => [
                'phone'          => $r['app_phone']          ?? '',
                'degree'         => $r['app_degree']         ?? '',
                'institution'    => $r['app_institution']    ?? '',
                'specialization' => $r['app_specialization'] ?? '',
                'experience'     => $r['app_experience'] !== null ? (string)$r['app_experience'] : '',
                'summary'        => $r['app_summary']        ?? '',
                'declared'       => (bool)($r['app_declared'] ?? false),
                'savedAt'        => $r['created_at'],
                'cvFileName'     => !empty($r['app_cv_path']) ? basename($r['app_cv_path']) : null,
                'clFileName'     => !empty($r['app_cl_path']) ? basename($r['app_cl_path']) : null,
                'supFileNames'   => array_map('basename', $supPaths),
                'cvFileData'     => $cvFileData,
                'clFileData'     => $clFileData,
                'supFilesData'   => $supFilesData,
            ],
        ];
    }

    echo json_encode(['success' => true, 'applications' => $out]);
}

/* ── POST save_draft: create or update a draft (JSON body, no files) */
function handleSaveDraft(PDO $pdo, int $userId): void {
    $body = json_decode(file_get_contents('php://input'), true);
    $announcementId = (int)($body['announcement_id'] ?? 0);
    if (!$announcementId) {
        echo json_encode(['success' => false, 'error' => 'Missing announcement_id']);
        return;
    }

    $phone          = trim($body['phone']          ?? '');
    $degree         = trim($body['degree']         ?? '');
    $institution    = trim($body['institution']    ?? '');
    $specialization = trim($body['specialization'] ?? '');
    $experience     = $body['experience'] !== '' && $body['experience'] !== null
                        ? (int)$body['experience'] : null;
    $summary        = trim($body['summary']        ?? '');

    $stmt = $pdo->prepare("
        SELECT id FROM candidate_applications
        WHERE candidate_id = ? AND announcement_id = ? AND status = 'draft'
    ");
    $stmt->execute([$userId, $announcementId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $upd = $pdo->prepare("
            UPDATE candidate_applications
            SET app_phone = ?, app_degree = ?, app_institution = ?,
                app_specialization = ?, app_experience = ?, app_summary = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $upd->execute([$phone, $degree, $institution, $specialization, $experience, $summary, $existing['id']]);
        echo json_encode(['success' => true, 'application_id' => $existing['id']]);
    } else {
        $ins = $pdo->prepare("
            INSERT INTO candidate_applications
                (announcement_id, candidate_id, status, progress,
                 app_phone, app_degree, app_institution, app_specialization,
                 app_experience, app_summary)
            VALUES (?, ?, 'draft', 25, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([$announcementId, $userId, $phone, $degree, $institution, $specialization, $experience, $summary]);
        echo json_encode(['success' => true, 'application_id' => (int)$pdo->lastInsertId()]);
    }
}

/* ── POST submit: save form data + uploaded files, set status=submitted */
function handleSubmit(PDO $pdo, int $userId): void {
    $announcementId = (int)($_POST['announcement_id'] ?? 0);
    if (!$announcementId) {
        echo json_encode(['success' => false, 'error' => 'Missing announcement_id']);
        return;
    }

    // Fetch existing record to preserve file paths not being replaced
    $stmt = $pdo->prepare("
        SELECT id, app_cv_path, app_cl_path,
               app_sup_path_1, app_sup_path_2, app_sup_path_3, app_sup_path_4, app_sup_path_5
        FROM candidate_applications
        WHERE candidate_id = ? AND announcement_id = ?
    ");
    $stmt->execute([$userId, $announcementId]);
    $existing = $stmt->fetch();

    $uploadDir = dirname(__DIR__) . '/uploads/applications/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $prefix  = $userId . '_' . $announcementId . '_';

    $cvPath   = $existing['app_cv_path'] ?? null;
    $clPath   = $existing['app_cl_path'] ?? null;
    $supPaths = $existing ? getSupPaths($existing) : [];

    $saveFile = function(array $fileArr, string $key) use ($uploadDir, $allowed, $prefix): ?string {
        if (empty($fileArr['tmp_name'])) return null;
        $ext = strtolower(pathinfo($fileArr['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) return null;
        $fname = $prefix . $key . '_' . time() . '.' . $ext;
        if (move_uploaded_file($fileArr['tmp_name'], $uploadDir . $fname)) {
            return 'uploads/applications/' . $fname;
        }
        return null;
    };

    if (!empty($_FILES['cv']['tmp_name'])) {
        $cvPath = $saveFile($_FILES['cv'], 'cv') ?? $cvPath;
    }
    if (!empty($_FILES['cl']['tmp_name'])) {
        $clPath = $saveFile($_FILES['cl'], 'cl') ?? $clPath;
    }
    if (!empty($_FILES['sup']['tmp_name'])) {
        $files = $_FILES['sup'];
        $count = is_array($files['tmp_name']) ? count($files['tmp_name']) : 0;
        for ($i = 0; $i < $count && count($supPaths) < 5; $i++) {
            if (!empty($files['tmp_name'][$i])) {
                $single = ['tmp_name' => $files['tmp_name'][$i], 'name' => $files['name'][$i]];
                $path = $saveFile($single, 'sup' . $i);
                if ($path) $supPaths[] = $path;
            }
        }
    }

    // Pad sup paths to 5 slots
    $supPaths = array_values($supPaths);
    while (count($supPaths) < 5) $supPaths[] = null;

    $phone          = trim($_POST['phone']          ?? '');
    $degree         = trim($_POST['degree']         ?? '');
    $institution    = trim($_POST['institution']    ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $experience     = $_POST['experience'] !== '' ? (int)$_POST['experience'] : null;
    $summary        = trim($_POST['summary']        ?? '');

    if ($existing) {
        $upd = $pdo->prepare("
            UPDATE candidate_applications
            SET status = 'submitted', progress = 100, submitted_at = NOW(),
                app_phone = ?, app_degree = ?, app_institution = ?,
                app_specialization = ?, app_experience = ?, app_summary = ?,
                app_declared = 1,
                app_cv_path = ?, app_cl_path = ?,
                app_sup_path_1 = ?, app_sup_path_2 = ?, app_sup_path_3 = ?,
                app_sup_path_4 = ?, app_sup_path_5 = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $upd->execute([
            $phone, $degree, $institution, $specialization, $experience, $summary,
            $cvPath, $clPath,
            $supPaths[0], $supPaths[1], $supPaths[2], $supPaths[3], $supPaths[4],
            $existing['id'],
        ]);
        $appId = $existing['id'];
    } else {
        $ins = $pdo->prepare("
            INSERT INTO candidate_applications
                (announcement_id, candidate_id, status, progress, submitted_at,
                 app_phone, app_degree, app_institution, app_specialization,
                 app_experience, app_summary, app_declared,
                 app_cv_path, app_cl_path,
                 app_sup_path_1, app_sup_path_2, app_sup_path_3, app_sup_path_4, app_sup_path_5)
            VALUES (?, ?, 'submitted', 100, NOW(),
                    ?, ?, ?, ?, ?, ?, 1,
                    ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $announcementId, $userId,
            $phone, $degree, $institution, $specialization, $experience, $summary,
            $cvPath, $clPath,
            $supPaths[0], $supPaths[1], $supPaths[2], $supPaths[3], $supPaths[4],
        ]);
        $appId = (int)$pdo->lastInsertId();
    }

    notifyAdminsNewApplication($pdo, $userId, $announcementId, $appId);
    echo json_encode(['success' => true, 'application_id' => $appId]);
}

/* ── Notify all admins about a new submitted application ─────────── */
function notifyAdminsNewApplication(PDO $pdo, int $candidateId, int $announcementId, int $appId): void {
    try {
        $infoStmt = $pdo->prepare("
            SELECT u.first_name, u.last_name, ja.title AS job_title
            FROM users u
            JOIN candidate_applications ca ON ca.id = :app_id
            JOIN job_announcements ja ON ja.id = ca.announcement_id
            WHERE u.id = :uid
            LIMIT 1
        ");
        $infoStmt->execute([':app_id' => $appId, ':uid' => $candidateId]);
        $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
        if (!$info) return;

        $applicantName = trim($info['first_name'] . ' ' . $info['last_name']);
        $jobTitle      = $info['job_title'];
        $title         = 'Νέα αίτηση: ' . $jobTitle;
        $message       = 'Ο χρήστης ' . $applicantName . ' υπέβαλε αίτηση για τη θέση "' . $jobTitle . '".';

        $adminsStmt = $pdo->query("SELECT id FROM users WHERE role = 'admin'");
        $admins = $adminsStmt->fetchAll(PDO::FETCH_COLUMN);

        $ins = $pdo->prepare("
            INSERT INTO notifications
                (user_id, title, message, notification_type, related_entity_id, related_entity_type)
            VALUES (?, ?, ?, 'new_application', ?, 'candidate_application')
        ");
        foreach ($admins as $adminId) {
            $ins->execute([(int)$adminId, $title, $message, $appId]);
        }
    } catch (Throwable $e) {
        // Notification failure should not break the submit response
    }
}

/* ── POST delete: remove a draft ────────────────────────────────── */
function handleDelete(PDO $pdo, int $userId): void {
    $body = json_decode(file_get_contents('php://input'), true);
    $announcementId = (int)($body['announcement_id'] ?? 0);
    if (!$announcementId) {
        echo json_encode(['success' => false, 'error' => 'Missing announcement_id']);
        return;
    }

    $stmt = $pdo->prepare("
        DELETE FROM candidate_applications
        WHERE candidate_id = ? AND announcement_id = ? AND status = 'draft'
    ");
    $stmt->execute([$userId, $announcementId]);
    echo json_encode(['success' => true]);
}

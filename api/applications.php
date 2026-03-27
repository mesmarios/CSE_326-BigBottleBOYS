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

/* ── GET: list all applications for the current user ────────────── */
function handleGet(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare("
        SELECT
            ca.id,
            ca.announcement_id,
            ca.status,
            ca.submitted_at,
            ca.created_at,
            ca.form_data,
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
        $fd = $r['form_data'] ? json_decode($r['form_data'], true) : [];

        // Build download URLs for files (relative from recruitment module pages)
        $cvFileData  = !empty($fd['cv_path'])  ? '../../api/download.php?app_id=' . $r['id'] . '&type=cv'  : null;
        $clFileData  = !empty($fd['cl_path'])  ? '../../api/download.php?app_id=' . $r['id'] . '&type=cl'  : null;
        $supFilesData = [];
        if (!empty($fd['sup_paths'])) {
            foreach (array_keys($fd['sup_paths']) as $i) {
                $supFilesData[] = '../../api/download.php?app_id=' . $r['id'] . '&type=sup&idx=' . $i;
            }
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
                'phone'          => $fd['phone']          ?? '',
                'degree'         => $fd['degree']         ?? '',
                'institution'    => $fd['institution']    ?? '',
                'specialization' => $fd['specialization'] ?? '',
                'experience'     => $fd['experience']     ?? '',
                'summary'        => $fd['summary']        ?? '',
                'declared'       => $fd['declared']       ?? false,
                'savedAt'        => $fd['savedAt']        ?? $r['created_at'],
                'cvFileName'     => !empty($fd['cv_path'])  ? basename($fd['cv_path'])  : null,
                'clFileName'     => !empty($fd['cl_path'])  ? basename($fd['cl_path'])  : null,
                'supFileNames'   => !empty($fd['sup_paths']) ? array_map('basename', $fd['sup_paths']) : [],
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

    // Check if a draft (or any non-submitted) record already exists
    $stmt = $pdo->prepare("
        SELECT id, form_data FROM candidate_applications
        WHERE candidate_id = ? AND announcement_id = ? AND status = 'draft'
    ");
    $stmt->execute([$userId, $announcementId]);
    $existing = $stmt->fetch();

    // Preserve existing file paths so saving text fields doesn't wipe them
    $existingFd = $existing ? (json_decode($existing['form_data'] ?? '{}', true) ?: []) : [];

    $formData = json_encode([
        'phone'          => $body['phone']          ?? '',
        'degree'         => $body['degree']         ?? '',
        'institution'    => $body['institution']    ?? '',
        'specialization' => $body['specialization'] ?? '',
        'experience'     => $body['experience']     ?? '',
        'summary'        => $body['summary']        ?? '',
        'declared'       => false,
        'savedAt'        => date('c'),
        'cv_path'        => $existingFd['cv_path']   ?? null,
        'cl_path'        => $existingFd['cl_path']   ?? null,
        'sup_paths'      => $existingFd['sup_paths'] ?? [],
    ]);

    if ($existing) {
        $upd = $pdo->prepare("
            UPDATE candidate_applications SET form_data = ?, updated_at = NOW() WHERE id = ?
        ");
        $upd->execute([$formData, $existing['id']]);
        echo json_encode(['success' => true, 'application_id' => $existing['id']]);
    } else {
        $ins = $pdo->prepare("
            INSERT INTO candidate_applications
                (announcement_id, candidate_id, status, progress, form_data)
            VALUES (?, ?, 'draft', 25, ?)
        ");
        $ins->execute([$announcementId, $userId, $formData]);
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

    // Fetch existing record (draft or previous submission)
    $stmt = $pdo->prepare("
        SELECT id, form_data FROM candidate_applications
        WHERE candidate_id = ? AND announcement_id = ?
    ");
    $stmt->execute([$userId, $announcementId]);
    $existing = $stmt->fetch();
    $existingFd = $existing ? (json_decode($existing['form_data'] ?? '{}', true) ?: []) : [];

    // Upload dir (relative to project root)
    $uploadDir = dirname(__DIR__) . '/uploads/applications/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $prefix  = $userId . '_' . $announcementId . '_';

    $cvPath  = $existingFd['cv_path']  ?? null;
    $clPath  = $existingFd['cl_path']  ?? null;
    $supPaths = $existingFd['sup_paths'] ?? [];

    // Helper: move uploaded file safely
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
        // Multiple file input named sup[]
        $files = $_FILES['sup'];
        $count = is_array($files['tmp_name']) ? count($files['tmp_name']) : 0;
        for ($i = 0; $i < $count && count($supPaths) < 5; $i++) {
            if (!empty($files['tmp_name'][$i])) {
                $single = [
                    'tmp_name' => $files['tmp_name'][$i],
                    'name'     => $files['name'][$i],
                ];
                $path = $saveFile($single, 'sup' . $i);
                if ($path) $supPaths[] = $path;
            }
        }
    }

    $formData = json_encode([
        'phone'          => $_POST['phone']          ?? '',
        'degree'         => $_POST['degree']         ?? '',
        'institution'    => $_POST['institution']    ?? '',
        'specialization' => $_POST['specialization'] ?? '',
        'experience'     => $_POST['experience']     ?? '',
        'summary'        => $_POST['summary']        ?? '',
        'declared'       => true,
        'savedAt'        => date('c'),
        'cv_path'        => $cvPath,
        'cl_path'        => $clPath,
        'sup_paths'      => $supPaths,
    ]);

    if ($existing) {
        $upd = $pdo->prepare("
            UPDATE candidate_applications
            SET status = 'submitted', progress = 100,
                submitted_at = NOW(), form_data = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $upd->execute([$formData, $existing['id']]);
        $appId = $existing['id'];
    } else {
        $ins = $pdo->prepare("
            INSERT INTO candidate_applications
                (announcement_id, candidate_id, status, progress, submitted_at, form_data)
            VALUES (?, ?, 'submitted', 100, NOW(), ?)
        ");
        $ins->execute([$announcementId, $userId, $formData]);
        $appId = (int)$pdo->lastInsertId();
    }

    echo json_encode(['success' => true, 'application_id' => $appId]);
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

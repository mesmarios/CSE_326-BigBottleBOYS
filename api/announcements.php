<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../database/db.php';

try {
    $stmt = $pdo->query("
        SELECT
            ja.id,
            ja.title,
            ja.description,
            s.name  AS school_name,
            d.name  AS department_name,
            c.code  AS course_code,
            c.name  AS course_name,
            rp.start_date,
            rp.end_date
        FROM job_announcements ja
        JOIN schools s            ON ja.school_id     = s.id
        JOIN departments d        ON ja.department_id = d.id
        JOIN courses c            ON ja.course_id     = c.id
        JOIN recruitment_periods rp ON ja.period_id   = rp.id
        WHERE ja.status = 'published'
          AND rp.status = 'active'
        ORDER BY ja.created_at DESC
    ");

    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $out[] = [
            'id'          => (int)$r['id'],
            'title'       => $r['title'],
            'description' => $r['description'] ?? '',
            'department'  => $r['department_name'],
            'school'      => $r['school_name'],
            'courses'     => [$r['course_code'] . ' - ' . $r['course_name']],
            'startDate'   => $r['start_date'],
            'endDate'     => $r['end_date'],
        ];
    }

    echo json_encode(['success' => true, 'announcements' => $out]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

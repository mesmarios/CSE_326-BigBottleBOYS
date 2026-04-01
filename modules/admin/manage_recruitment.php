<?php
require_once __DIR__ . '/../../includes/admin-guard.php';
require_once __DIR__ . '/../../includes/config.php';

$pdo = getDBConnection();

function redirectRecruitment(string $message, string $type = 'success', string $anchor = ''): void
{
    $location = 'manage_recruitment.php?msg=' . urlencode($message) . '&mtype=' . urlencode($type);
    if ($anchor !== '') {
        $location .= '#' . $anchor;
    }
    header('Location: ' . $location);
    exit;
}

function safeDelete(PDO $pdo, string $sql, array $params, string $successMessage, string $anchor): void
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        redirectRecruitment($successMessage, 'success', $anchor);
    } catch (Throwable $e) {
        redirectRecruitment('Η διαγραφή δεν μπόρεσε να ολοκληρωθεί λόγω συνδεδεμένων εγγραφών.', 'danger', $anchor);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_school') {
        $id = (int) ($_POST['school_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            redirectRecruitment('Το όνομα σχολής είναι υποχρεωτικό.', 'danger', 'schools');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE schools SET name = :name, description = :description, updated_at = NOW() WHERE id = :id');
            $stmt->execute([':name' => $name, ':description' => $description !== '' ? $description : null, ':id' => $id]);
            redirectRecruitment('Η σχολή ενημερώθηκε επιτυχώς.', 'success', 'schools');
        }

        $stmt = $pdo->prepare('INSERT INTO schools (name, description) VALUES (:name, :description)');
        $stmt->execute([':name' => $name, ':description' => $description !== '' ? $description : null]);
        redirectRecruitment('Η σχολή προστέθηκε επιτυχώς.', 'success', 'schools');
    }

    if ($action === 'delete_school') {
        safeDelete($pdo, 'DELETE FROM schools WHERE id = :id', [':id' => (int) ($_POST['school_id'] ?? 0)], 'Η σχολή διαγράφηκε επιτυχώς.', 'schools');
    }

    if ($action === 'save_department') {
        $id = (int) ($_POST['department_id'] ?? 0);
        $schoolId = (int) ($_POST['school_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($schoolId <= 0 || $name === '') {
            redirectRecruitment('Το τμήμα χρειάζεται σχολή και όνομα.', 'danger', 'departments');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE departments
                 SET school_id = :school_id, name = :name, description = :description, updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':school_id' => $schoolId,
                ':name' => $name,
                ':description' => $description !== '' ? $description : null,
                ':id' => $id,
            ]);
            redirectRecruitment('Το τμήμα ενημερώθηκε επιτυχώς.', 'success', 'departments');
        }

        $stmt = $pdo->prepare('INSERT INTO departments (school_id, name, description) VALUES (:school_id, :name, :description)');
        $stmt->execute([
            ':school_id' => $schoolId,
            ':name' => $name,
            ':description' => $description !== '' ? $description : null,
        ]);
        redirectRecruitment('Το τμήμα προστέθηκε επιτυχώς.', 'success', 'departments');
    }

    if ($action === 'delete_department') {
        safeDelete($pdo, 'DELETE FROM departments WHERE id = :id', [':id' => (int) ($_POST['department_id'] ?? 0)], 'Το τμήμα διαγράφηκε επιτυχώς.', 'departments');
    }

    if ($action === 'save_course') {
        $id = (int) ($_POST['course_id'] ?? 0);
        $departmentId = (int) ($_POST['department_id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $credits = (int) ($_POST['credits'] ?? 0);
        $semester = (int) ($_POST['semester'] ?? 0);

        if ($departmentId <= 0 || $code === '' || $name === '') {
            redirectRecruitment('Το μάθημα χρειάζεται τμήμα, κωδικό και όνομα.', 'danger', 'courses');
        }

        try {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE courses
                     SET department_id = :department_id, code = :code, name = :name, description = :description, credits = :credits, semester = :semester, updated_at = NOW()
                     WHERE id = :id'
                );
                $stmt->execute([
                    ':department_id' => $departmentId,
                    ':code' => $code,
                    ':name' => $name,
                    ':description' => $description !== '' ? $description : null,
                    ':credits' => $credits > 0 ? $credits : null,
                    ':semester' => $semester > 0 ? $semester : null,
                    ':id' => $id,
                ]);
                redirectRecruitment('Το μάθημα ενημερώθηκε επιτυχώς.', 'success', 'courses');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO courses (department_id, code, name, description, credits, semester)
                 VALUES (:department_id, :code, :name, :description, :credits, :semester)'
            );
            $stmt->execute([
                ':department_id' => $departmentId,
                ':code' => $code,
                ':name' => $name,
                ':description' => $description !== '' ? $description : null,
                ':credits' => $credits > 0 ? $credits : null,
                ':semester' => $semester > 0 ? $semester : null,
            ]);
            redirectRecruitment('Το μάθημα προστέθηκε επιτυχώς.', 'success', 'courses');
        } catch (Throwable $e) {
            redirectRecruitment('Ο κωδικός μαθήματος πρέπει να είναι μοναδικός.', 'danger', 'courses');
        }
    }

    if ($action === 'delete_course') {
        safeDelete($pdo, 'DELETE FROM courses WHERE id = :id', [':id' => (int) ($_POST['course_id'] ?? 0)], 'Το μάθημα διαγράφηκε επιτυχώς.', 'courses');
    }

    if ($action === 'save_period') {
        $id = (int) ($_POST['period_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');
        $status = $_POST['status'] ?? 'planning';
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $startDate === '' || $endDate === '') {
            redirectRecruitment('Η περίοδος χρειάζεται τίτλο και ημερομηνίες.', 'danger', 'periods');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE recruitment_periods
                 SET name = :name, start_date = :start_date, end_date = :end_date, status = :status, description = :description, updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':name' => $name,
                ':start_date' => $startDate,
                ':end_date' => $endDate,
                ':status' => $status,
                ':description' => $description !== '' ? $description : null,
                ':id' => $id,
            ]);
            redirectRecruitment('Η περίοδος ενημερώθηκε επιτυχώς.', 'success', 'periods');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO recruitment_periods (name, start_date, end_date, status, description)
             VALUES (:name, :start_date, :end_date, :status, :description)'
        );
        $stmt->execute([
            ':name' => $name,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':status' => $status,
            ':description' => $description !== '' ? $description : null,
        ]);
        redirectRecruitment('Η περίοδος προστέθηκε επιτυχώς.', 'success', 'periods');
    }

    if ($action === 'delete_period') {
        safeDelete($pdo, 'DELETE FROM recruitment_periods WHERE id = :id', [':id' => (int) ($_POST['period_id'] ?? 0)], 'Η περίοδος διαγράφηκε επιτυχώς.', 'periods');
    }

    if ($action === 'save_announcement') {
        $id = (int) ($_POST['announcement_id'] ?? 0);
        $periodId = (int) ($_POST['period_id'] ?? 0);
        $schoolId = (int) ($_POST['school_id'] ?? 0);
        $departmentId = (int) ($_POST['department_id'] ?? 0);
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $positions = max(1, (int) ($_POST['number_of_positions'] ?? 1));
        $status = $_POST['status'] ?? 'draft';

        if ($periodId <= 0 || $schoolId <= 0 || $departmentId <= 0 || $courseId <= 0 || $title === '') {
            redirectRecruitment('Η ανακοίνωση χρειάζεται πλήρη στοιχεία περίοδου, σχολής, τμήματος, μαθήματος και τίτλο.', 'danger', 'announcements');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE job_announcements
                 SET period_id = :period_id,
                     school_id = :school_id,
                     department_id = :department_id,
                     course_id = :course_id,
                     title = :title,
                     description = :description,
                     requirements = :requirements,
                     number_of_positions = :number_of_positions,
                     status = :status,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':period_id' => $periodId,
                ':school_id' => $schoolId,
                ':department_id' => $departmentId,
                ':course_id' => $courseId,
                ':title' => $title,
                ':description' => $description !== '' ? $description : null,
                ':requirements' => $requirements !== '' ? $requirements : null,
                ':number_of_positions' => $positions,
                ':status' => $status,
                ':id' => $id,
            ]);
            redirectRecruitment('Η ανακοίνωση ενημερώθηκε επιτυχώς.', 'success', 'announcements');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO job_announcements (period_id, school_id, department_id, course_id, title, description, requirements, number_of_positions, status)
             VALUES (:period_id, :school_id, :department_id, :course_id, :title, :description, :requirements, :number_of_positions, :status)'
        );
        $stmt->execute([
            ':period_id' => $periodId,
            ':school_id' => $schoolId,
            ':department_id' => $departmentId,
            ':course_id' => $courseId,
            ':title' => $title,
            ':description' => $description !== '' ? $description : null,
            ':requirements' => $requirements !== '' ? $requirements : null,
            ':number_of_positions' => $positions,
            ':status' => $status,
        ]);
        redirectRecruitment('Η ανακοίνωση προστέθηκε επιτυχώς.', 'success', 'announcements');
    }

    if ($action === 'delete_announcement') {
        safeDelete($pdo, 'DELETE FROM job_announcements WHERE id = :id', [':id' => (int) ($_POST['announcement_id'] ?? 0)], 'Η ανακοίνωση διαγράφηκε επιτυχώς.', 'announcements');
    }

    if ($action === 'save_assignment') {
        $assignmentId = (int) ($_POST['assignment_id'] ?? 0);
        $announcementId = (int) ($_POST['announcement_id'] ?? 0);
        $evaluatorId = (int) ($_POST['evaluator_id'] ?? 0);

        if ($announcementId <= 0 || $evaluatorId <= 0) {
            redirectRecruitment('Η ανάθεση χρειάζεται ανακοίνωση και αξιολογητή.', 'danger', 'assignments');
        }

        try {
            if ($assignmentId > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE application_evaluators
                     SET announcement_id = :announcement_id, evaluator_id = :evaluator_id
                     WHERE id = :id'
                );
                $stmt->execute([
                    ':announcement_id' => $announcementId,
                    ':evaluator_id' => $evaluatorId,
                    ':id' => $assignmentId,
                ]);
                redirectRecruitment('Η ανάθεση ενημερώθηκε επιτυχώς.', 'success', 'assignments');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO application_evaluators (announcement_id, evaluator_id)
                 VALUES (:announcement_id, :evaluator_id)'
            );
            $stmt->execute([
                ':announcement_id' => $announcementId,
                ':evaluator_id' => $evaluatorId,
            ]);
            redirectRecruitment('Η ανάθεση αξιολογητή αποθηκεύτηκε επιτυχώς.', 'success', 'assignments');
        } catch (Throwable $e) {
            redirectRecruitment('Η συγκεκριμένη ανάθεση υπάρχει ήδη ή δεν είναι έγκυρη.', 'danger', 'assignments');
        }
    }

    if ($action === 'delete_assignment') {
        safeDelete($pdo, 'DELETE FROM application_evaluators WHERE id = :id', [':id' => (int) ($_POST['assignment_id'] ?? 0)], 'Η ανάθεση διαγράφηκε επιτυχώς.', 'assignments');
    }
}

$editType = $_GET['edit'] ?? '';
$editId = (int) ($_GET['id'] ?? 0);

$schools = $pdo->query('SELECT id, name, description, created_at FROM schools ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$departments = $pdo->query(
    'SELECT d.id, d.school_id, d.name, d.description, s.name AS school_name
     FROM departments d
     INNER JOIN schools s ON s.id = d.school_id
     ORDER BY s.name, d.name'
)->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query(
    'SELECT c.id, c.department_id, c.code, c.name, c.description, c.credits, c.semester, d.name AS department_name
     FROM courses c
     INNER JOIN departments d ON d.id = c.department_id
     ORDER BY d.name, c.name'
)->fetchAll(PDO::FETCH_ASSOC);
$periods = $pdo->query('SELECT * FROM recruitment_periods ORDER BY start_date DESC')->fetchAll(PDO::FETCH_ASSOC);
$announcements = $pdo->query(
    'SELECT ja.id, ja.period_id, ja.school_id, ja.department_id, ja.course_id, ja.title, ja.description, ja.requirements,
            ja.number_of_positions, ja.status, rp.name AS period_name, s.name AS school_name, d.name AS department_name,
            c.name AS course_name, COUNT(ca.id) AS application_count
     FROM job_announcements ja
     INNER JOIN recruitment_periods rp ON rp.id = ja.period_id
     INNER JOIN schools s ON s.id = ja.school_id
     INNER JOIN departments d ON d.id = ja.department_id
     INNER JOIN courses c ON c.id = ja.course_id
     LEFT JOIN candidate_applications ca ON ca.announcement_id = ja.id
     GROUP BY ja.id
     ORDER BY ja.created_at DESC'
)->fetchAll(PDO::FETCH_ASSOC);
$evaluatorUsers = $pdo->query(
    "SELECT id, username, first_name, last_name, email
     FROM users
     WHERE role = 'user'
     ORDER BY first_name, last_name, username"
)->fetchAll(PDO::FETCH_ASSOC);
$assignments = $pdo->query(
    'SELECT ae.id, ae.announcement_id, ae.evaluator_id, ae.created_at,
            ja.title AS announcement_title,
            u.username, u.first_name, u.last_name, u.email
     FROM application_evaluators ae
     INNER JOIN job_announcements ja ON ja.id = ae.announcement_id
     INNER JOIN users u ON u.id = ae.evaluator_id
     ORDER BY ae.created_at DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$schoolForm = ['id' => 0, 'name' => '', 'description' => ''];
$departmentForm = ['id' => 0, 'school_id' => 0, 'name' => '', 'description' => ''];
$courseForm = ['id' => 0, 'department_id' => 0, 'code' => '', 'name' => '', 'description' => '', 'credits' => '', 'semester' => ''];
$periodForm = ['id' => 0, 'name' => '', 'start_date' => '', 'end_date' => '', 'status' => 'planning', 'description' => ''];
$announcementForm = ['id' => 0, 'period_id' => 0, 'school_id' => 0, 'department_id' => 0, 'course_id' => 0, 'title' => '', 'description' => '', 'requirements' => '', 'number_of_positions' => 1, 'status' => 'draft'];
$assignmentForm = ['id' => 0, 'announcement_id' => 0, 'evaluator_id' => 0];

if ($editType === 'school') {
    foreach ($schools as $school) {
        if ((int) $school['id'] === $editId) {
            $schoolForm = ['id' => $school['id'], 'name' => $school['name'], 'description' => (string) ($school['description'] ?? '')];
            break;
        }
    }
}
if ($editType === 'department') {
    foreach ($departments as $department) {
        if ((int) $department['id'] === $editId) {
            $departmentForm = ['id' => $department['id'], 'school_id' => $department['school_id'], 'name' => $department['name'], 'description' => (string) ($department['description'] ?? '')];
            break;
        }
    }
}
if ($editType === 'course') {
    foreach ($courses as $course) {
        if ((int) $course['id'] === $editId) {
            $courseForm = [
                'id' => $course['id'],
                'department_id' => $course['department_id'],
                'code' => $course['code'],
                'name' => $course['name'],
                'description' => (string) ($course['description'] ?? ''),
                'credits' => (string) ($course['credits'] ?? ''),
                'semester' => (string) ($course['semester'] ?? ''),
            ];
            break;
        }
    }
}
if ($editType === 'period') {
    foreach ($periods as $period) {
        if ((int) $period['id'] === $editId) {
            $periodForm = [
                'id' => $period['id'],
                'name' => $period['name'],
                'start_date' => $period['start_date'],
                'end_date' => $period['end_date'],
                'status' => $period['status'],
                'description' => (string) ($period['description'] ?? ''),
            ];
            break;
        }
    }
}
if ($editType === 'announcement') {
    foreach ($announcements as $announcement) {
        if ((int) $announcement['id'] === $editId) {
            $announcementForm = [
                'id' => $announcement['id'],
                'period_id' => $announcement['period_id'],
                'school_id' => $announcement['school_id'],
                'department_id' => $announcement['department_id'],
                'course_id' => $announcement['course_id'],
                'title' => $announcement['title'],
                'description' => (string) ($announcement['description'] ?? ''),
                'requirements' => (string) ($announcement['requirements'] ?? ''),
                'number_of_positions' => (int) $announcement['number_of_positions'],
                'status' => $announcement['status'],
            ];
            break;
        }
    }
}
if ($editType === 'assignment') {
    foreach ($assignments as $assignment) {
        if ((int) $assignment['id'] === $editId) {
            $assignmentForm = [
                'id' => $assignment['id'],
                'announcement_id' => $assignment['announcement_id'],
                'evaluator_id' => $assignment['evaluator_id'],
            ];
            break;
        }
    }
}

$msg = $_GET['msg'] ?? '';
$msgType = $_GET['mtype'] ?? 'success';
?>
<!doctype html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <title>Admin | Manage Recruitment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/adminlte.css">
    <style>
        body { background: #f5f7fb; font-family: Arial, sans-serif; }
        .page-shell { max-width: 1280px; margin: 32px auto; padding: 0 16px; }
        .topbar, .card { background: #fff; border: 1px solid #dbe3ee; border-radius: 14px; box-shadow: 0 10px 24px rgba(15,23,42,0.06); }
        .topbar { padding: 18px 22px; margin-bottom: 18px; display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .topbar a { text-decoration: none; font-weight: 700; color: #1d4ed8; margin-right: 14px; }
        .quick-links { margin-bottom: 18px; display: flex; gap: 10px; flex-wrap: wrap; }
        .quick-links a { text-decoration: none; padding: 10px 12px; background: #eff6ff; color: #1d4ed8; border-radius: 999px; font-weight: 700; }
        .alert { padding: 14px 16px; border-radius: 10px; margin-bottom: 16px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 18px; }
        .stat { padding: 18px; }
        .section { margin-bottom: 18px; }
        .section-title { margin: 0 0 14px; }
        .grid { display: grid; grid-template-columns: 1.25fr 1fr; gap: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: top; }
        th { background: #eff6ff; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 6px; font-weight: 700; }
        input, textarea, select { width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; }
        textarea { min-height: 96px; resize: vertical; }
        .btn { display: inline-block; padding: 10px 14px; border: 0; border-radius: 8px; cursor: pointer; font-weight: 700; text-decoration: none; }
        .btn-primary { background: #1d4ed8; color: #fff; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #0f172a; }
        .inline-form { display: inline; }
        .muted { color: #64748b; }
        @media (max-width: 980px) {
            .stats, .grid, .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="topbar">
            <div>
                <a href="index.php">Dashboard</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="manage_recruitment.php">Manage Recruitment</a>
                <a href="configure_system.php">Configure System</a>
                <a href="report.php">Reports</a>
            </div>
            <div><a href="../../logout.php">Logout</a></div>
        </div>

        <div class="quick-links">
            <a href="#announcements">Announcements</a>
            <a href="#schools">Schools</a>
            <a href="#departments">Departments</a>
            <a href="#courses">Courses</a>
            <a href="#periods">Periods</a>
            <a href="#assignments">Assignments</a>
        </div>

        <?php if ($msg !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($msgType, ENT_QUOTES, 'UTF-8') === 'danger' ? 'danger' : 'success' ?>">
                <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="stats">
            <div class="card stat"><strong><?= count($schools) ?></strong><div class="muted">Schools</div></div>
            <div class="card stat"><strong><?= count($departments) ?></strong><div class="muted">Departments</div></div>
            <div class="card stat"><strong><?= count($courses) ?></strong><div class="muted">Courses</div></div>
            <div class="card stat"><strong><?= count($announcements) ?></strong><div class="muted">Announcements</div></div>
        </div>

        <section class="section" id="announcements">
            <div class="grid">
                <div class="card">
                    <h3 class="section-title">Job Announcements</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Period</th>
                                <th>Course</th>
                                <th>Status</th>
                                <th>Applications</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($announcements as $announcement): ?>
                                <tr>
                                    <td><?= htmlspecialchars($announcement['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($announcement['period_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($announcement['course_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($announcement['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) $announcement['application_count'] ?></td>
                                    <td>
                                        <a class="btn btn-secondary" href="manage_recruitment.php?edit=announcement&id=<?= (int) $announcement['id'] ?>#announcements">Edit</a>
                                        <form class="inline-form" method="post">
                                            <input type="hidden" name="action" value="delete_announcement">
                                            <input type="hidden" name="announcement_id" value="<?= (int) $announcement['id'] ?>">
                                            <button class="btn btn-danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card">
                    <h3 class="section-title"><?= $announcementForm['id'] ? 'Edit Announcement' : 'New Announcement' ?></h3>
                    <form method="post">
                        <input type="hidden" name="action" value="save_announcement">
                        <input type="hidden" name="announcement_id" value="<?= (int) $announcementForm['id'] ?>">
                        <div class="form-grid">
                            <div class="full">
                                <label>Title</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($announcementForm['title'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div>
                                <label>Period</label>
                                <select name="period_id" required>
                                    <option value="">Select period</option>
                                    <?php foreach ($periods as $period): ?>
                                        <option value="<?= (int) $period['id'] ?>" <?= (int) $announcementForm['period_id'] === (int) $period['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($period['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>School</label>
                                <select name="school_id" required>
                                    <option value="">Select school</option>
                                    <?php foreach ($schools as $school): ?>
                                        <option value="<?= (int) $school['id'] ?>" <?= (int) $announcementForm['school_id'] === (int) $school['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($school['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Department</label>
                                <select name="department_id" required>
                                    <option value="">Select department</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= (int) $department['id'] ?>" <?= (int) $announcementForm['department_id'] === (int) $department['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Course</label>
                                <select name="course_id" required>
                                    <option value="">Select course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= (int) $course['id'] ?>" <?= (int) $announcementForm['course_id'] === (int) $course['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($course['code'] . ' - ' . $course['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Positions</label>
                                <input type="number" name="number_of_positions" min="1" value="<?= (int) $announcementForm['number_of_positions'] ?>">
                            </div>
                            <div class="full">
                                <label>Description</label>
                                <textarea name="description"><?= htmlspecialchars($announcementForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div class="full">
                                <label>Requirements</label>
                                <textarea name="requirements"><?= htmlspecialchars($announcementForm['requirements'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div>
                                <label>Status</label>
                                <select name="status">
                                    <?php foreach (['draft', 'published', 'closed', 'cancelled'] as $status): ?>
                                        <option value="<?= $status ?>" <?= $announcementForm['status'] === $status ? 'selected' : '' ?>><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="full">
                                <button class="btn btn-primary" type="submit">Save Announcement</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="section" id="schools">
            <div class="grid">
                <div class="card">
                    <h3 class="section-title">Schools</h3>
                    <table>
                        <thead><tr><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($schools as $school): ?>
                                <tr>
                                    <td><?= htmlspecialchars($school['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($school['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a class="btn btn-secondary" href="manage_recruitment.php?edit=school&id=<?= (int) $school['id'] ?>#schools">Edit</a>
                                        <form class="inline-form" method="post">
                                            <input type="hidden" name="action" value="delete_school">
                                            <input type="hidden" name="school_id" value="<?= (int) $school['id'] ?>">
                                            <button class="btn btn-danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h3 class="section-title"><?= $schoolForm['id'] ? 'Edit School' : 'New School' ?></h3>
                    <form method="post">
                        <input type="hidden" name="action" value="save_school">
                        <input type="hidden" name="school_id" value="<?= (int) $schoolForm['id'] ?>">
                        <div class="form-grid">
                            <div class="full">
                                <label>Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($schoolForm['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="full">
                                <label>Description</label>
                                <textarea name="description"><?= htmlspecialchars($schoolForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div class="full"><button class="btn btn-primary" type="submit">Save School</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="section" id="departments">
            <div class="grid">
                <div class="card">
                    <h3 class="section-title">Departments</h3>
                    <table>
                        <thead><tr><th>Name</th><th>School</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($departments as $department): ?>
                                <tr>
                                    <td><?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($department['school_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a class="btn btn-secondary" href="manage_recruitment.php?edit=department&id=<?= (int) $department['id'] ?>#departments">Edit</a>
                                        <form class="inline-form" method="post">
                                            <input type="hidden" name="action" value="delete_department">
                                            <input type="hidden" name="department_id" value="<?= (int) $department['id'] ?>">
                                            <button class="btn btn-danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h3 class="section-title"><?= $departmentForm['id'] ? 'Edit Department' : 'New Department' ?></h3>
                    <form method="post">
                        <input type="hidden" name="action" value="save_department">
                        <input type="hidden" name="department_id" value="<?= (int) $departmentForm['id'] ?>">
                        <div class="form-grid">
                            <div class="full">
                                <label>School</label>
                                <select name="school_id" required>
                                    <option value="">Select school</option>
                                    <?php foreach ($schools as $school): ?>
                                        <option value="<?= (int) $school['id'] ?>" <?= (int) $departmentForm['school_id'] === (int) $school['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($school['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="full">
                                <label>Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($departmentForm['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="full">
                                <label>Description</label>
                                <textarea name="description"><?= htmlspecialchars($departmentForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div class="full"><button class="btn btn-primary" type="submit">Save Department</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="section" id="courses">
            <div class="grid">
                <div class="card">
                    <h3 class="section-title">Courses</h3>
                    <table>
                        <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['code'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($course['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($course['department_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a class="btn btn-secondary" href="manage_recruitment.php?edit=course&id=<?= (int) $course['id'] ?>#courses">Edit</a>
                                        <form class="inline-form" method="post">
                                            <input type="hidden" name="action" value="delete_course">
                                            <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                                            <button class="btn btn-danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h3 class="section-title"><?= $courseForm['id'] ? 'Edit Course' : 'New Course' ?></h3>
                    <form method="post">
                        <input type="hidden" name="action" value="save_course">
                        <input type="hidden" name="course_id" value="<?= (int) $courseForm['id'] ?>">
                        <div class="form-grid">
                            <div>
                                <label>Department</label>
                                <select name="department_id" required>
                                    <option value="">Select department</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= (int) $department['id'] ?>" <?= (int) $courseForm['department_id'] === (int) $department['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Code</label>
                                <input type="text" name="code" value="<?= htmlspecialchars($courseForm['code'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="full">
                                <label>Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($courseForm['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div>
                                <label>Credits</label>
                                <input type="number" name="credits" min="0" value="<?= htmlspecialchars($courseForm['credits'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div>
                                <label>Semester</label>
                                <input type="number" name="semester" min="0" value="<?= htmlspecialchars($courseForm['semester'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="full">
                                <label>Description</label>
                                <textarea name="description"><?= htmlspecialchars($courseForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div class="full"><button class="btn btn-primary" type="submit">Save Course</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="section" id="periods">
            <div class="grid">
                <div class="card">
                    <h3 class="section-title">Recruitment Periods</h3>
                    <table>
                        <thead><tr><th>Name</th><th>Dates</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($periods as $period): ?>
                                <tr>
                                    <td><?= htmlspecialchars($period['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($period['start_date'] . ' έως ' . $period['end_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($period['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a class="btn btn-secondary" href="manage_recruitment.php?edit=period&id=<?= (int) $period['id'] ?>#periods">Edit</a>
                                        <form class="inline-form" method="post">
                                            <input type="hidden" name="action" value="delete_period">
                                            <input type="hidden" name="period_id" value="<?= (int) $period['id'] ?>">
                                            <button class="btn btn-danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h3 class="section-title"><?= $periodForm['id'] ? 'Edit Period' : 'New Period' ?></h3>
                    <form method="post">
                        <input type="hidden" name="action" value="save_period">
                        <input type="hidden" name="period_id" value="<?= (int) $periodForm['id'] ?>">
                        <div class="form-grid">
                            <div class="full">
                                <label>Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($periodForm['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div>
                                <label>Start Date</label>
                                <input type="date" name="start_date" value="<?= htmlspecialchars($periodForm['start_date'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div>
                                <label>End Date</label>
                                <input type="date" name="end_date" value="<?= htmlspecialchars($periodForm['end_date'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div>
                                <label>Status</label>
                                <select name="status">
                                    <?php foreach (['planning', 'active', 'closed', 'archived'] as $status): ?>
                                        <option value="<?= $status ?>" <?= $periodForm['status'] === $status ? 'selected' : '' ?>><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="full">
                                <label>Description</label>
                                <textarea name="description"><?= htmlspecialchars($periodForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div class="full"><button class="btn btn-primary" type="submit">Save Period</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="section" id="assignments">
            <div class="grid">
                <div class="card">
                    <h3 class="section-title">Evaluator Assignments</h3>
                    <table>
                        <thead><tr><th>Announcement</th><th>Evaluator</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($assignment['announcement_title'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(trim($assignment['first_name'] . ' ' . $assignment['last_name']) . ' (' . $assignment['username'] . ')', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a class="btn btn-secondary" href="manage_recruitment.php?edit=assignment&id=<?= (int) $assignment['id'] ?>#assignments">Edit</a>
                                        <form class="inline-form" method="post">
                                            <input type="hidden" name="action" value="delete_assignment">
                                            <input type="hidden" name="assignment_id" value="<?= (int) $assignment['id'] ?>">
                                            <button class="btn btn-danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h3 class="section-title"><?= $assignmentForm['id'] ? 'Edit Assignment' : 'New Assignment' ?></h3>
                    <form method="post">
                        <input type="hidden" name="action" value="save_assignment">
                        <input type="hidden" name="assignment_id" value="<?= (int) $assignmentForm['id'] ?>">
                        <div class="form-grid">
                            <div class="full">
                                <label>Announcement</label>
                                <select name="announcement_id" required>
                                    <option value="">Select announcement</option>
                                    <?php foreach ($announcements as $announcement): ?>
                                        <option value="<?= (int) $announcement['id'] ?>" <?= (int) $assignmentForm['announcement_id'] === (int) $announcement['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($announcement['title'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="full">
                                <label>Evaluator</label>
                                <select name="evaluator_id" required>
                                    <option value="">Select evaluator</option>
                                    <?php foreach ($evaluatorUsers as $user): ?>
                                        <option value="<?= (int) $user['id'] ?>" <?= (int) $assignmentForm['evaluator_id'] === (int) $user['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name']) . ' (' . $user['username'] . ')', ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="full"><button class="btn btn-primary" type="submit">Save Assignment</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</body>
</html>

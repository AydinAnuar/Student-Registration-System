<?php
require 'lecturer_auth.php';
include 'config_database.php';

$students = $pdo->query(
    "SELECT s.id, s.fullname, s.email,
            GROUP_CONCAT(DISTINCT CONCAT(c.course_code, ' - ', c.course_name)
                         ORDER BY c.course_code SEPARATOR ', ') AS courses
     FROM students s
     LEFT JOIN enrollments e ON e.student_id = s.id
     LEFT JOIN courses c ON c.course_id = e.course_id
     GROUP BY s.id, s.fullname, s.email
     ORDER BY s.fullname"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information - INTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="lecturer.css" rel="stylesheet">
</head>
<body>
    <div class="list-card">
        <h1>Student Information</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Course(s)</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student) { ?>
                        <tr>
                            <td><?= htmlspecialchars($student['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['fullname'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['courses'] ?: 'Not applied', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($students)) { ?>
                        <tr><td colspan="4" class="text-center text-muted">No students registered.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <a href="lecturer_dashboard.php" class="btn-back">Return to Dashboard</a>
    </div>
</body>
</html>

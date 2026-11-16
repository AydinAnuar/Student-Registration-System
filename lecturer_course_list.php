<?php
require 'lecturer_auth.php';
include 'config_database.php';

$courses = $pdo->query(
    'SELECT course_id, course_code, course_name, credits FROM courses ORDER BY course_code'
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Course Information</h2>
        <hr>
        <a href="lecturer_dashboard.php" class="btn btn-secondary mb-3">Return to Dashboard</a>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr><th>Course ID</th><th>Course Code</th><th>Course Name</th><th>Credits</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course) { ?>
                        <tr>
                            <td><?= htmlspecialchars($course['course_id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($course['course_code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($course['credits'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($courses)) { ?>
                        <tr><td colspan="4" class="text-center text-muted">No courses available.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

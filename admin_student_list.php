<?php

session_start();

include 'config_database.php';

$students = $pdo->query(
    "SELECT * FROM students"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Student Information</h2>
        <hr>
        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Return to Dashboard</a>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr><th>ID</th><th>Name</th><th>Email</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student) { ?>
                        <tr>
                            <td><?= htmlspecialchars($student['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['fullname'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($students)) { ?>
                        <tr><td colspan="3" class="text-center text-muted">No students registered.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

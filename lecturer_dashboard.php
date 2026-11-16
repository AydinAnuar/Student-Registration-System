<?php
require 'lecturer_auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['lecturer_name'], ENT_QUOTES, 'UTF-8') ?>.</h2>
        <hr>
        <a href="lecturer_student_list.php" class="btn btn-info">View Students</a>
        <a href="lecturer_course_list.php" class="btn btn-success">View Courses</a>
        <a href="lecturer_registrations.php" class="btn btn-primary">Monitor Registrations</a>
        <a href="lecturer_messages.php" class="btn btn-warning">Student Messages</a>
        <a href="lecturer_logout.php" class="btn btn-danger">Logout</a>
        <a href="index.php" class="btn btn-secondary">Return to Home</a>
    </div>
</body>
</html>

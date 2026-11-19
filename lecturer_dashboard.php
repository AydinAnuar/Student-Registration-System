<?php
require 'lecturer_auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - INTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="lecturer.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <h1>Lecturer Dashboard</h1>
        <p class="subtitle">Welcome, <?= htmlspecialchars($_SESSION['lecturer_name'], ENT_QUOTES, 'UTF-8') ?>.</p>
        <hr>
        <div class="menu">
            <a href="lecturer_student_list.php">View Students</a>
            <a href="lecturer_course_list.php">View Courses</a>
            <a href="lecturer_registrations.php">Monitor Registrations</a>
            <a href="lecturer_messages.php">Student Messages</a>
            <a href="lecturer_upload_material.php">Upload Course Material</a>
        </div>
        <div class="bottom-buttons">
            <a href="lecturer_logout.php" class="logout">Logout</a>
            <a href="index.php" class="home">Return to Home</a>
        </div>
    </div>
</body>
</html>

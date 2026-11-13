<?php
session_start();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">


        <h2>Welcome, Admin.</h2>

        <hr>

        <a href="admin_add_course.php" class="btn btn-success">
            Add Course
        </a>



        <a href="admin_student_list.php" class="btn btn-info">
            View Students
        </a>



        <a href="admin_logout.php" class="btn btn-danger">
            Logout
        </a>



        <a href="index.php"
            class="btn btn-secondary">
            Return to Home
        </a>

    </div>
</body>

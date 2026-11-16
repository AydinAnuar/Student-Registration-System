<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">

        <h2>
            Welcome,
            <?php echo $_SESSION['student_name']; ?>.
        </h2>

        <hr>

        <a href="student_enrollment.php"
            class="btn btn-success">
            Enroll Courses
        </a>

        <a href="student_message.php"
            class="btn btn-primary">
            Message a Lecturer
        </a>

        <a href="student_logout.php"
            class="btn btn-danger">
            Logout
        </a>

        <a href="index.php"
            class="btn btn-secondary">
            Return to Home
        </a>

    </div>

</body>

</html>
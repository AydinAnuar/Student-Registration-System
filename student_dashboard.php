<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container d-flex flex-column min-vh-100 py-4">

        <h2 class="text-center">
            Welcome,
            <?php echo $_SESSION['student_name']; ?>.
        </h2>

        <hr>

        
        <div class="flex-grow-1 d-flex flex-column justify-content-center align-items-center text-center">

            <img src="images/campus_students.jpg" alt="College campus" style="width: 100%; max-width: 1000px; height: auto;"> </br>
           <p>You can enroll courses or message a lecturer</p>
           
        </div>

        
        <div class="text-center mt-4">

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

    </div>

</body>

</html>

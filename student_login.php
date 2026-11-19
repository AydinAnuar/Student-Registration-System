
<?php
session_start();
include 'config_database.php';

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM students WHERE email=?");
    $stmt->execute([$email]);

    $student = $stmt->fetch();

    if ($student && $password === $student['password']) {
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['fullname'];

        header("Location: student_dashboard.php");
        exit;
    }

    $error = "Invalid Credentials";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - INTI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="student.css" rel="stylesheet">
</head>

<body>

    <div class="student-card student-card-narrow">
        <h1 class="student-title">Student Login</h1>

        <?php if ($error !== '') { ?>
            <div class="alert alert-danger student-alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php } ?>

        <form method="POST">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" class="form-control mb-3"
                   placeholder="Enter your email" required>

            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" class="form-control mb-4"
                   placeholder="Enter your password" required>

            <button name="login" class="student-primary w-100 mb-2">
                Login
            </button>

            <a href="index.php" class="student-dark w-100">
                Return to Home
            </a>
        </form>
    </div>

</body>
</html>

<?php
session_start();
include 'config_database.php';

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare(
        "SELECT * FROM students WHERE email=?"
    );

    $stmt->execute([$email]);

    $student = $stmt->fetch();

    if (
        $student && $password === $student['password']
    ) {
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
    <title>Student Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- Centers the grey box in the middle of the page -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <!-- Grey box -->
        <div class="p-5 rounded-4 shadow w-100 d-flex flex-column"
            style="max-width: 650px; min-height: 560px; background-color: #e9ecef;">

            <h2 class="text-center fw-bold mb-4">Student Login</h2>

            <div class="text-danger mb-2">
                <?php echo $error; ?>
            </div>

            <form method="POST" class="d-flex flex-column flex-grow-1">

                <label for="email" class="form-label fw-semibold">Email</label>
                <input id="email"
                    type="email"
                    name="email"
                    class="form-control form-control-lg mb-4"
                    placeholder="Email"
                    required>

                <label for="password" class="form-label fw-semibold">Password</label>
                <input id="password"
                    type="password"
                    name="password"
                    class="form-control form-control-lg mb-2"
                    placeholder="Password"
                    required>

                <!-- Buttons at the bottom of the box: Return to Home on the left, Login on the right -->
                <div class="d-flex justify-content-between mt-auto pt-4">
                    <a href="index.php" class="btn btn-secondary btn-lg px-4">Return to Home</a>

                    <button name="login"
                        class="btn btn-primary btn-lg px-5">
                        Login
                    </button>
                </div>

            </form>

        </div>

    </div>

</body>

</html>

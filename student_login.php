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

    <div class="container mt-5">
        <div class="row justify-content-center">

            <div class="col-md-5">

                <div class="card shadow p-4">

                    <h2 class="text-center mb-4">Student Login</h2>

                    <div class="text-danger">
                        <?php echo $error; ?>
                    </div>

                    <form method="POST">
                        <label class="form-label">Email</label>
                        <input type="email"
                            name="email"
                            class="form-control mb-3"
                            placeholder="Email"
                            required>
                        <label class="form-label">Password</label>
                        <input type="password"
                            name="password"
                            class="form-control mb-3"
                            placeholder="Password"
                            required>

                        <button name="login"
                            class="btn btn-primary">
                            Login
                        </button>
                        <a href="index.php" class="btn btn-secondary">Return to Home</a>

                    </form>

                </div>

</body>

</html>
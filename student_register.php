<?php

include 'config_database.php';

$message = "";

if (isset($_POST['register'])) {

    $fullname = trim($_POST['fullname']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    if (!$email) {

        $message = "Invalid email address.";
    } elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {

        $message = "Password must contain at least 8 characters, uppercase, lowercase and a number.";
    } else {

        // Check whether email already exists
        $check = $pdo->prepare(
            "SELECT id FROM students WHERE email = ?"
        );

        $check->execute([$email]);

        if ($check->fetch()) {
            $message = "This email is already registered.";
        } else {

            $sql = $pdo->prepare(
                "INSERT INTO students
                 (fullname, email, password)
                 VALUES (?, ?, ?)"
            );

            if ($sql->execute([
                $fullname,
                $email,
                $password
            ])) {

                $message = "Registration Successful";
                header("Location: student_login.php");
            } else {

                $message = "Registration Failed";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">

        <h2>Student Registration</h2>

        <div class="text-success">
            <?php echo $message; ?>
        </div>

        <form method="POST">

            <input type="text"
                name="fullname"
                placeholder="Full Name"
                class="form-control mb-3"
                required>

            <input type="email"
                name="email"
                placeholder="Email"
                class="form-control mb-3"
                required>

            <input type="password"
                name="password"
                placeholder="Password"
                class="form-control mb-3"
                required>

            <button name="register"
                class="btn btn-success">
                Register
            </button>

        </form>
    </div>

</body>

</html>
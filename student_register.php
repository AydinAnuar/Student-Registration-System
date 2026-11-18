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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- Centers the grey box in the middle of the page -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <!-- Grey box -->
        <div class="p-5 rounded-4 shadow w-100 d-flex flex-column"
            style="max-width: 650px; min-height: 560px; background-color: #e9ecef;">

            <h2 class="text-center fw-bold mb-4">Student Registration</h2>

            <div class="text-success mb-2">
                <?php echo $message; ?>
            </div>

            <form method="POST" class="d-flex flex-column flex-grow-1">

                <label for="fullname" class="form-label fw-semibold">Full Name</label>
                <input id="fullname"
                    type="text"
                    name="fullname"
                    placeholder="Full Name"
                    class="form-control form-control-lg mb-4"
                    required>

                <label for="email" class="form-label fw-semibold">Email</label>
                <input id="email"
                    type="email"
                    name="email"
                    placeholder="Email"
                    class="form-control form-control-lg mb-4"
                    required>

                <label for="password" class="form-label fw-semibold">Password</label>
                <input id="password"
                    type="password"
                    name="password"
                    placeholder="Password"
                    class="form-control form-control-lg mb-2"
                    required>

                <!-- Button at the bottom right of the box -->
                <div class="d-flex justify-content-end mt-auto pt-4">
                    <button name="register"
                        class="btn btn-success btn-lg px-5">
                        Register
                    </button>
                    
                </div>
                <a href="index.php" class="btn btn-secondary">Return to Home</a>

            </form>

        </div>

    </div>

</body>

</html>
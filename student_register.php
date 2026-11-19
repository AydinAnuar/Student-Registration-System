
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

        $check = $pdo->prepare("SELECT id FROM students WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $message = "This email is already registered.";

        } else {

            $sql = $pdo->prepare("INSERT INTO students (fullname, email, password) VALUES (?, ?, ?)");

            if ($sql->execute([$fullname, $email, $password])) {
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
    <title>Student Registration - INTI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="student.css" rel="stylesheet">
</head>

<body>

<div class="student-card student-card-narrow">
    <h1 class="student-title">Student Registration</h1>

    <?php if ($message !== '') { ?>
        <div class="alert alert-danger student-alert">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php } ?>

    <form method="POST">
        <label for="fullname" class="form-label">Full Name</label>
        <input id="fullname" type="text" name="fullname" class="form-control mb-3"
               placeholder="Full Name" required>

        <label for="email" class="form-label">Email</label>
        <input id="email" type="email" name="email" class="form-control mb-3"
               placeholder="Email" required>

        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" name="password" class="form-control mb-4"
               placeholder="Password" required>

        <button type="submit" name="register" class="student-primary w-100 mb-2">
            Register
        </button>

        <a href="index.php" class="student-dark w-100">
            Return to Home
        </a>
    </form>
</div>

</body>
</html>


<?php
session_start();
include 'config_database.php';

$error = '';

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {

        $stmt = $pdo->prepare("
            SELECT username, password
            FROM admins
            WHERE username = ?
        ");

        $stmt->execute([$username]);

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && $password === $admin['password']) {

            $_SESSION['username'] = $admin['username'];

            header("Location: admin_dashboard.php");
            exit();

        } else {
            $error = "Invalid username or password.";
        }

    } else {
        $error = "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - INTI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #FAF5F0;
            font-family: Arial, sans-serif;
        }

        .login-card {
            max-width: 680px;
            background-color: white;
            border-top: 5px solid #E30613;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.10);
        }

        h2 {
            color: #E30613;
        }

        .form-control {
            padding: 13px;
            border-radius: 8px;
        }

        .login-btn {
            background-color: #E30613;
            color: white;
            border: none;
        }

        .login-btn:hover {
            background-color: #bd0010;
            color: white;
        }

        .home-btn {
            background-color: #292929;
            color: white;
            border: none;
        }

        .home-btn:hover {
            background-color: #444;
            color: white;
        }
    </style>
</head>

<body>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <div class="login-card w-100">

            <h2 class="text-center fw-bold mb-4">Admin Login</h2>

            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <form method="POST">

                <label class="form-label fw-semibold">Username</label>

                <input
                    type="text"
                    name="username"
                    class="form-control mb-4"
                    placeholder="Enter your username"
                    required>

                <label class="form-label fw-semibold">Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control mb-4"
                    placeholder="Enter your password"
                    required>

                <button
                    type="submit"
                    name="login"
                    class="btn login-btn w-100 py-3 mb-2">
                    Login
                </button>

                <a href="index.php" class="btn home-btn w-100 py-2">
                    Return to Home
                </a>

            </form>

        </div>

    </div>

</body>
</html>

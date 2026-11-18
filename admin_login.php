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
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <div class="row justify-content-center">

            <div class="col-md-5">

                <div class="card shadow">

                    <div class="card-body">

                        <h2 class="text-center mb-4">Admin Login</h2>

                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php } ?>

                        <form method="POST">

                            <div class="mb-3">
                                <label class="form-label">Username</label>

                                <input
                                    type="text"
                                    name="username"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    required>
                            </div>

                            <button
                                type="submit"
                                name="login"
                                class="btn btn-primary">
                                Login
                            </button>

                            <a href="index.php" class="btn btn-secondary">Return to Home</a>

                        </form>

                    </div>

                </div>

            </div>

        </div>
    </div>

</body>

</html>
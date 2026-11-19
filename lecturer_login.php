
<?php
session_start();
include 'config_database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $lecturerName = $_POST['lecturer_name'] ?? '';
    $password = $_POST['password'] ?? '';

    if (is_string($lecturerName) && is_string($password)) {
        $stmt = $pdo->prepare(
            'SELECT lecturer_id, lecturer_name, password FROM lecturers WHERE lecturer_name = ?'
        );
        $stmt->execute([trim($lecturerName)]);
        $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lecturer && $password === $lecturer['password']) {
            session_regenerate_id(true);
            $_SESSION['lecturer_id'] = $lecturer['lecturer_id'];
            $_SESSION['lecturer_name'] = $lecturer['lecturer_name'];
            header('Location: lecturer_dashboard.php');
            exit();
        }
    }

    $error = 'Invalid lecturer name or password.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Login - INTI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="lecturer.css?v=2" rel="stylesheet">
</head>

<body>

    <div class="login-card">

        <h2>Lecturer Login</h2>

        <?php if (!empty($error)) { ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php } ?>

        <form method="POST">

            <div class="mb-3">
                <label for="lecturer_name" class="form-label">Lecturer Name</label>
                <input type="text" id="lecturer_name"
                       name="lecturer_name"
                       class="form-control"
                       placeholder="Enter your lecturer name"
                       required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password"
                       name="password"
                       class="form-control"
                       placeholder="Enter your password"
                       required>
            </div>

            <button type="submit" name="login" class="btn btn-login full-width">
                Login
            </button>

            <a href="index.php" class="btn-home">
                Return to Home
            </a>

        </form>

    </div>

</body>
</html>

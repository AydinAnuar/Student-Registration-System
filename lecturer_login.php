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
    <title>Lecturer Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Lecturer Login</h2>
                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php } ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="lecturer_name" class="form-label">Lecturer Name</label>
                                <input id="lecturer_name" type="text" name="lecturer_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary">Login</button>
                            <a href="index.php" class="btn btn-secondary">Return to Home</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

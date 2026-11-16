<?php
session_start();

if (empty($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit();
}

include 'config_database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    if (
        !isset($_POST['csrf_token']) ||
        !is_string($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid request. Please try again.';
    } else {
        $subjectInput = $_POST['subject'] ?? '';
        $messageInput = $_POST['message'] ?? '';
        $subject = is_string($subjectInput) ? trim($subjectInput) : '';
        $message = is_string($messageInput) ? trim($messageInput) : '';

        if ($subject === '' || strlen($subject) > 200 || $message === '') {
            $error = 'Enter a subject (up to 200 characters) and a message.';
        } else {
            $insert = $pdo->prepare(
                'INSERT INTO student_messages (student_id, subject, message) VALUES (?, ?, ?)'
            );
            $insert->execute([(int) $_SESSION['student_id'], $subject, $message]);
            header('Location: student_message.php?sent=1');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message a Lecturer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Message a Lecturer</h2>
        <hr>
        <a href="student_dashboard.php" class="btn btn-secondary mb-3">Return to Dashboard</a>
        <?php if ($error !== '') { ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } elseif (isset($_GET['sent'])) { ?>
            <div class="alert alert-success">Your message has been sent.</div>
        <?php } ?>
        <form method="POST">
            <input type="hidden" name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input id="subject" type="text" name="subject" maxlength="200" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea id="message" name="message" rows="6" class="form-control" required></textarea>
            </div>
            <button type="submit" name="send" class="btn btn-primary">Send Message</button>
        </form>
    </div>
</body>
</html>

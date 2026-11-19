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

$sentMessages = $pdo->prepare(
    'SELECT subject, message, created_at, reply, replied_at
     FROM student_messages
     WHERE student_id = ?
     ORDER BY created_at DESC, id DESC'
);
$sentMessages->execute([(int) $_SESSION['student_id']]);
$sentMessages = $sentMessages->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message a Lecturer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="student.css" rel="stylesheet">
</head>
<body>
    <main class="student-card">
        <h1 class="student-title">Message a Lecturer</h1>
        <a href="student_dashboard.php" class="student-dark student-action mb-4">Return to Dashboard</a>
        <?php if ($error !== '') { ?>
            <div class="alert alert-danger student-alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } elseif (isset($_GET['sent'])) { ?>
            <div class="alert alert-success student-alert">Your message has been sent.</div>
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
                <textarea id="message" name="message" rows="6" class="form-control mb-0" required></textarea>
            </div>
            <button type="submit" name="send" class="student-primary student-action">Send Message</button>
        </form>

        <hr class="my-4">
        <h2 class="student-title">My Messages and Replies</h2>
        <?php if (empty($sentMessages)) { ?>
            <div class="alert alert-info student-alert">You have not sent any messages yet.</div>
        <?php } else { ?>
            <?php foreach ($sentMessages as $sentMessage) { ?>
                <div class="card student-message mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($sentMessage['subject'], ENT_QUOTES, 'UTF-8') ?></h5>
                        <small class="text-muted">
                            Sent <?= htmlspecialchars($sentMessage['created_at'], ENT_QUOTES, 'UTF-8') ?>
                        </small>
                        <p class="mt-2"><?= nl2br(htmlspecialchars($sentMessage['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php if (!empty($sentMessage['reply'])) { ?>
                            <div class="alert alert-success mb-0">
                                <strong>Lecturer reply:</strong>
                                <div><?= nl2br(htmlspecialchars($sentMessage['reply'], ENT_QUOTES, 'UTF-8')) ?></div>
                                <?php if (!empty($sentMessage['replied_at'])) { ?>
                                    <small class="d-block text-muted mt-1">
                                        Replied <?= htmlspecialchars($sentMessage['replied_at'], ENT_QUOTES, 'UTF-8') ?>
                                    </small>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <div class="text-muted"><small>No reply yet.</small></div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </main>
</body>
</html>

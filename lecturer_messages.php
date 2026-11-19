<?php
require 'lecturer_auth.php';
include 'config_database.php';

$replyError = '';
$replySuccess = '';

if (empty($_SESSION['message_reply_token'])) {
    $_SESSION['message_reply_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message_id'])) {
    $token = $_POST['csrf_token'] ?? '';
    $messageId = filter_var($_POST['reply_message_id'], FILTER_VALIDATE_INT);
    $reply = is_string($_POST['reply'] ?? null) ? trim($_POST['reply']) : '';

    if (!is_string($token) || !hash_equals($_SESSION['message_reply_token'], $token)) {
        $replyError = 'Invalid request. Please try again.';
    } elseif (!$messageId || $reply === '' || strlen($reply) > 5000) {
        $replyError = 'Enter a reply between 1 and 5,000 characters.';
    } else {
        $update = $pdo->prepare(
            'UPDATE student_messages
             SET reply = ?, replied_at = NOW(), reply_lecturer_id = ?
             WHERE id = ?'
        );
        $update->execute([$reply, (int) $_SESSION['lecturer_id'], $messageId]);
        $replySuccess = 'Reply sent successfully.';
        $_SESSION['message_reply_token'] = bin2hex(random_bytes(32));
    }
}

$messages = $pdo->query(
    'SELECT m.id, m.subject, m.message, m.created_at, m.reply, m.replied_at,
            s.fullname, s.email
     FROM student_messages m
     INNER JOIN students s ON s.id = m.student_id
     ORDER BY m.created_at DESC, m.id DESC'
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Messages - INTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="lecturer.css" rel="stylesheet">
</head>
<body>
    <div class="list-card">
        <h1>Student Messages</h1>
        <?php if ($replyError !== '') { ?>
            <div class="alert alert-danger"><?= htmlspecialchars($replyError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } elseif ($replySuccess !== '') { ?>
            <div class="alert alert-success"><?= htmlspecialchars($replySuccess, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } ?>
        <?php if (empty($messages)) { ?>
            <div class="alert alert-info">No messages received.</div>
        <?php } else { ?>
            <div class="row g-3">
                <?php foreach ($messages as $message) { ?>
                    <div class="col-12">
                        <div class="card message-card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($message['subject'], ENT_QUOTES, 'UTF-8') ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <?= htmlspecialchars($message['fullname'], ENT_QUOTES, 'UTF-8') ?>
                                    (<?= htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8') ?>)
                                    &middot;
                                    <?= htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?>
                                </h6>
                                <p class="card-text"><?= nl2br(htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                                <?php if (!empty($message['reply'])) { ?>
                                    <div class="alert alert-success mt-3 mb-3">
                                        <strong>Reply:</strong>
                                        <?= nl2br(htmlspecialchars($message['reply'], ENT_QUOTES, 'UTF-8')) ?>
                                        <?php if (!empty($message['replied_at'])) { ?>
                                            <small class="d-block text-muted mt-1">
                                                Sent <?= htmlspecialchars($message['replied_at'], ENT_QUOTES, 'UTF-8') ?>
                                            </small>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars($_SESSION['message_reply_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="reply_message_id"
                                           value="<?= (int) $message['id'] ?>">
                                    <label class="form-label" for="reply-<?= (int) $message['id'] ?>">
                                        <?= empty($message['reply']) ? 'Reply to Student' : 'Update Reply' ?>
                                    </label>
                                    <textarea class="form-control mb-2"
                                              id="reply-<?= (int) $message['id'] ?>"
                                              name="reply" rows="3" maxlength="5000" required><?= htmlspecialchars($message['reply'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                    <button type="submit" class="btn btn-add">
                                        <?= empty($message['reply']) ? 'Send Reply' : 'Update Reply' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <a href="lecturer_dashboard.php" class="btn-back">Return to Dashboard</a>
    </div>
</body>
</html>

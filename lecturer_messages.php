<?php
require 'lecturer_auth.php';
include 'config_database.php';

$messages = $pdo->query(
    'SELECT m.subject, m.message, m.created_at, s.fullname, s.email
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
    <title>Student Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Student Messages</h2>
        <hr>
        <a href="lecturer_dashboard.php" class="btn btn-secondary mb-3">Return to Dashboard</a>
        <?php if (empty($messages)) { ?>
            <div class="alert alert-info">No messages received.</div>
        <?php } else { ?>
            <div class="row g-3">
                <?php foreach ($messages as $message) { ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($message['subject'], ENT_QUOTES, 'UTF-8') ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <?= htmlspecialchars($message['fullname'], ENT_QUOTES, 'UTF-8') ?>
                                    (<?= htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8') ?>)
                                    &middot;
                                    <?= htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?>
                                </h6>
                                <p class="card-text"><?= nl2br(htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</body>
</html>

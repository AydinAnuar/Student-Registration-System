<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

include 'config_database.php';

$replyList = $pdo->prepare(
    'SELECT subject, reply, replied_at
     FROM student_messages
     WHERE student_id = ? AND reply IS NOT NULL
     ORDER BY replied_at DESC, id DESC
     LIMIT 5'
);
$replyList->execute([(int) $_SESSION['student_id']]);
$replies = $replyList->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="student.css" rel="stylesheet">
</head>

<body>

    <main class="student-card">
        <h1 class="student-title">
            Welcome, <?= htmlspecialchars($_SESSION['student_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>.
        </h1>

        <div class="text-center">
            <p class="student-subtitle">You can enroll in courses or message a lecturer.</p>
        </div>

        <?php if (!empty($replies)) { ?>
            <section class="mt-4">
                <h2 class="student-title">Lecturer Replies</h2>
                <?php foreach ($replies as $reply) { ?>
                    <div class="card student-message mb-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($reply['subject'], ENT_QUOTES, 'UTF-8') ?>
                            </h5>
                            <p class="card-text">
                                <?= nl2br(htmlspecialchars($reply['reply'], ENT_QUOTES, 'UTF-8')) ?>
                            </p>
                            <?php if (!empty($reply['replied_at'])) { ?>
                                <small class="text-muted">
                                    Replied <?= htmlspecialchars($reply['replied_at'], ENT_QUOTES, 'UTF-8') ?>
                                </small>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </section>
        <?php } ?>

        <div class="student-menu mt-4">
            <a href="student_enrollment.php" class="student-action student-primary">Enroll Courses</a>
            <a href="student_materials.php" class="student-action student-primary">Course Materials</a>
            <a href="student_message.php" class="student-action student-dark">Message a Lecturer</a>
            <a href="student_logout.php" class="student-action student-dark">Logout</a>
            <a href="index.php" class="student-action student-dark">Return to Home</a>
        </div>
    </main>
</body>

</html>

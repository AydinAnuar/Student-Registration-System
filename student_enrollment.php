<?php
session_start();

if (empty($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit;
}

include 'config_database.php';

$student_id = (int) $_SESSION['student_id'];
$cart_key = 'student_cart_' . $student_id;

if (!isset($_SESSION[$cart_key]) || !is_array($_SESSION[$cart_key])) {
    $_SESSION[$cart_key] = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $course_id = filter_var($_POST['course_id'] ?? null, FILTER_VALIDATE_INT);
    if ($course_id !== false && $course_id !== null && $course_id > 0) {
        $courseCheck = $pdo->prepare('SELECT 1 FROM courses WHERE course_id = ?');
        $courseCheck->execute([$course_id]);

        if ($courseCheck->fetchColumn() && !in_array($course_id, $_SESSION[$cart_key], true)) {
            $_SESSION[$cart_key][] = $course_id;
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalize']) && !empty($_SESSION[$cart_key])) {
    $stmt = $pdo->prepare("
        INSERT INTO enrollments
        (student_id, course_id)
        VALUES(?,?)
    ");

    $pdo->beginTransaction();
    try {
        foreach ($_SESSION[$cart_key] as $course_id) {
            $stmt->execute([$student_id, $course_id]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    $_SESSION[$cart_key] = [];
    $success = 'Successfully enrolled in courses!';
}

$courses = $pdo->query(
    "SELECT * FROM courses"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Enroll Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="student.css" rel="stylesheet">
</head>

<body>

    <main class="student-card">
        <h1 class="student-title">Enroll Courses</h1>
        <a href="student_dashboard.php" class="student-dark student-action mb-4">Return to Dashboard</a>

        <?php if (isset($success)): ?>
            <div class="alert alert-success student-alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="row g-3">
            <?php foreach ($courses as $course) { ?>
                <div class="col-12 col-md-6">
                    <div class="card student-course">
                        <div class="card-body d-flex flex-column">
                            <h2 class="card-title h5">
                                <?= htmlspecialchars($course['course_code'], ENT_QUOTES, 'UTF-8') ?>
                            </h2>
                            <p class="card-text flex-grow-1">
                                <?= htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <form method="POST" class="mb-0">
                                <input type="hidden" name="course_id"
                                       value="<?= (int) $course['course_id'] ?>">
                                <?php if (in_array((int) $course['course_id'], $_SESSION[$cart_key], true)): ?>
                                    <button type="button" class="student-dark student-action" disabled>Added</button>
                                <?php else: ?>
                                    <button type="submit" name="add" class="student-primary student-action">Add Course</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php if (empty($courses)) { ?>
                <div class="col-12">
                    <div class="alert alert-info student-alert mb-0">No courses are available for enrollment.</div>
                </div>
            <?php } ?>
        </div>

        <form method="POST">
            <button class="student-primary student-action mt-4" name="finalize">
                Enroll (<?= count($_SESSION[$cart_key]) ?>)
            </button>
        </form>
    </main>

</body>

</html>

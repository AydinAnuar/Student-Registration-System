<?php
session_start();

include 'config_database.php';

$student_id = $_SESSION['student_id'] ?? 1;


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


if (isset($_POST['add'])) {
    $course_id = $_POST['course_id'];
    if (!in_array($course_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $course_id;
    }
}


if (isset($_POST['finalize'])) {
    $stmt = $pdo->prepare("
        INSERT INTO enrollments
        (student_id, course_id)
        VALUES(?,?)
    ");

    foreach ($_SESSION['cart'] as $course_id) {
        $stmt->execute([
            $student_id,
            $course_id
        ]);
    }
    $_SESSION['cart'] = []; 
    $success = "Successfully enrolled in courses!";
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
                                <?php if (in_array($course['course_id'], $_SESSION['cart'])): ?>
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
                Enroll (<?= count($_SESSION['cart']) ?>)
            </button>
        </form>
    </main>

</body>

</html>

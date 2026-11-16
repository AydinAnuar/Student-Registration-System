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
);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Enroll Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        
        .btn-finalize {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
    </style>
</head>

<body>

    <div class="container mt-5 mb-5">

        <h2>Enroll Courses</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        
        <?php foreach ($courses as $course) { ?>
            <div class="d-flex justify-content-between align-items-center border p-3 rounded mb-3 bg-white shadow-sm" style="max-width: 600px;">
                <div>
                    <strong><?= $course['course_code'] ?></strong> - <?= $course['course_name'] ?>
                </div>

                <form method="POST" class="mb-0">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    
                    <?php if (in_array($course['id'], $_SESSION['cart'])): ?>
                        <button type="button" class="btn btn-secondary btn-sm" disabled>Added</button>
                    <?php else: ?>
                        <button type="submit" name="add" class="btn btn-primary btn-sm">Add</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php } ?>

        
        <form method="POST">
            <button class="btn btn-success btn-lg btn-finalize shadow" name="finalize">
                Enroll (<?= count($_SESSION['cart']) ?>)
            </button>
        </form>

    </div>

</body>

</html>

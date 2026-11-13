<?php
session_start();

include 'config_database.php';

$student_id = $_SESSION['student_id'];

if (isset($_POST['enroll'])) {
    $course_id = $_POST['course_id'];

    $stmt = $pdo->prepare("
        INSERT INTO enrollments
        (student_id,course_id)
        VALUES(?,?)
    ");

    $stmt->execute([
        $student_id,
        $course_id
    ]);
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
</head>

<body>

    <div class="container mt-5">

        <h2>Enroll Courses</h2>

        <form method="POST">

            <select class="form-select"
                name="course_id">
                <?php
                foreach ($courses as $course) {
                ?>
                    <option value="<?= $course['id'] ?>">
                        <?= $course['course_code'] ?>
                        -
                        <?= $course['course_name'] ?>
                    </option>
                <?php
                }
                ?>
            </select>

            <button class="btn btn-primary mt-3"
                name="enroll">
                Enroll
            </button>

        </form>

    </div>

</body>

</html>
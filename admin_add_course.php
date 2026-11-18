<?php

session_start();

include 'config_database.php';

if (isset($_POST['save'])) {
    $sql = $pdo->prepare(
        "INSERT INTO courses
(course_code,course_name,credits)
VALUES(?,?,?)"
    );

    $sql->execute([
        $_POST['course_code'],
        $_POST['course_name'],
        $_POST['credits']
    ]);
}
?>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Add Course</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="course_code" class="form-label">Course Code</label>
                <input type="text" class="form-control" id="course_code" name="course_code" placeholder="Course Code" required>
            </div>
            <div class="mb-3">
                <label for="course_name" class="form-label">Course Name</label>
                <input type="text" class="form-control" id="course_name" name="course_name" placeholder="Course Name" required>
            </div>
            <div class="mb-3">
                <label for="credits" class="form-label">Credits</label>
                <input type="number" class="form-control" id="credits" name="credits" placeholder="Credits" required>
            </div>
            <button type="submit" name="save" class="btn btn-primary">Save</button>
        </form>
    </div>
</body>
</html>
        

</form>
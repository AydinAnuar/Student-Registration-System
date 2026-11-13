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

<form method="POST">

    <h2>Add Course</h2>

    <input type="text"
        name="course_code"
        placeholder="Course Code">

    <input type="text"
        name="course_name"
        placeholder="Course Name">

    <input type="number"
        name="credits"
        placeholder="Credits">

    <button name="save">
        Save
    </button>

</form>
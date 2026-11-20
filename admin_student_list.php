<?php

session_start();

include 'config_database.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    $studentId = filter_var($_POST['delete_student'], FILTER_VALIDATE_INT);

    if ($studentId === false || $studentId < 1) {
        $errorMessage = 'Invalid student ID.';
    } else {
        try {
            $pdo->beginTransaction();

            $deleteEnrollments = $pdo->prepare('DELETE FROM enrollments WHERE student_id = ?');
            $deleteEnrollments->execute([$studentId]);

            $deleteStudent = $pdo->prepare('DELETE FROM students WHERE id = ?');
            $deleteStudent->execute([$studentId]);

            if ($deleteStudent->rowCount() === 0) {
                throw new RuntimeException('Student not found.');
            }

            $pdo->commit();
            $successMessage = 'Student deleted successfully.';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMessage = 'Could not delete the student.';
        }
    }
}

$students = $pdo->query(
    "SELECT s.id, s.fullname, s.email,
            GROUP_CONCAT(DISTINCT CONCAT(c.course_code, ' - ', c.course_name)
                         ORDER BY c.course_code SEPARATOR ', ') AS courses
     FROM students s
     LEFT JOIN enrollments e ON e.student_id = s.id
     LEFT JOIN courses c ON c.course_id = e.course_id
     GROUP BY s.id, s.fullname, s.email
     ORDER BY s.fullname"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information - INTI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f3ee;
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 15px;
        }

        .list-card {
            background: white;
            width: 100%;
            max-width: 850px;
            padding: 40px;
            border-radius: 14px;
            border-top: 5px solid #ed0010;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #ed0010;
            font-size: 34px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        .table {
            margin-bottom: 20px;
            border-color: #f1dada;
        }

        .table thead th {
            background: #fff7f5;
            color: #262626;
            border-bottom-color: #ed0010;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #fffaf8;
        }

        .table-hover tbody tr:hover {
            background-color: #fff0ed;
        }

        .btn-back {
            display: flex;
            width: 100%;
            height: 50px;
            justify-content: center;
            align-items: center;
            border: none;
            border-radius: 8px;
            background: #262626;
            color: white;
            text-decoration: none;
            margin-top: 10px;
        }

        .btn-back:hover {
            background: #444;
            color: white;
        }

        .btn-delete {
            background: #ed0010;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: bold;
        }

        .btn-delete:hover {
            background: #c9000d;
        }

        @media (max-width: 576px) {
            .list-card {
                padding: 25px 20px;
            }

            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="list-card">
        <h1>Student Information</h1>

        <?php if ($successMessage !== '') { ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } ?>
        <?php if ($errorMessage !== '') { ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Course(s)</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student) { ?>
                        <tr>
                            <td><?= htmlspecialchars($student['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['fullname'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['courses'] ?: 'Not applied', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Delete this student and remove their course enrollments?');">
                                    <input type="hidden" name="delete_student" value="<?= (int) $student['id'] ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($students)) { ?>
                        <tr><td colspan="5" class="text-center text-muted">No students registered.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <a href="admin_dashboard.php" class="btn-back">
            Return to Dashboard
        </a>
    </div>
</body>
</html>

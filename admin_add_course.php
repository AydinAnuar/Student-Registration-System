
<?php
session_start();
include 'config_database.php';

$successMessage = '';
$errorMessage = '';
$courseCode = '';
$courseName = '';
$credits = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $courseCode = trim($_POST['course_code'] ?? '');
    $courseName = trim($_POST['course_name'] ?? '');
    $creditsInput = $_POST['credits'] ?? '';
    $credits = filter_var(
        $creditsInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($courseCode === '') {
        $errorMessage = 'Course code is required.';
    } elseif ($courseName === '') {
        $errorMessage = 'Course name is required.';
    } elseif ($credits === false) {
        $errorMessage = 'Credits must be a positive whole number.';
    } else {
        $courseCheck = $pdo->prepare('SELECT 1 FROM courses WHERE course_code = ?');
        $courseCheck->execute([$courseCode]);

        if ($courseCheck->fetchColumn()) {
            $errorMessage = 'A course with that course code already exists.';
        } else {
            $sql = $pdo->prepare(
                'INSERT INTO courses (course_code, course_name, credits)
                 VALUES (?, ?, ?)'
            );

            try {
                $sql->execute([$courseCode, $courseName, $credits]);
                $successMessage = 'Course added successfully.';
                $courseCode = '';
                $courseName = '';
                $credits = '';
            } catch (PDOException $exception) {
                if (($exception->errorInfo[1] ?? null) === 1062) {
                    $errorMessage = 'A course with that course code already exists.';
                } else {
                    throw $exception;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - INTI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f3ee;
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-card {
            background: white;
            width: 100%;
            max-width: 650px;
            padding: 40px;
            border-radius: 14px;
            border-top: 5px solid #ed0010;
            box-shadow: 0 8px 22px rgba(0,0,0,0.10);
        }

        h2 {
            color: #ed0010;
            text-align: center;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: bold;
        }

        .form-control {
            height: 52px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .btn-save {
            background: #ed0010;
            color: white;
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 7px;
        }

        .btn-save:hover {
            background: #c9000d;
            color: white;
        }

        .btn-home {
            display: block;
            background: #262626;
            color: white;
            text-align: center;
            padding: 12px;
            border-radius: 7px;
            text-decoration: none;
            margin-top: 10px;
        }

        .btn-home:hover {
            background: #444;
            color: white;
        }
    </style>
</head>

<body>

    <div class="form-card">

        <h2>Add Course</h2>

        <?php if ($successMessage !== '') { ?>
            <div class="alert alert-success" role="status">
                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php } ?>
        <?php if ($errorMessage !== '') { ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php } ?>

        <form method="POST">

            <label for="course_code" class="form-label">Course Code</label>
            <input type="text" id="course_code" name="course_code"
                   class="form-control" placeholder="Enter course code" required
                   value="<?= htmlspecialchars($courseCode, ENT_QUOTES, 'UTF-8') ?>">

            <label for="course_name" class="form-label">Course Name</label>
            <input type="text" id="course_name" name="course_name"
                   class="form-control" placeholder="Enter course name" required
                   value="<?= htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8') ?>">

            <label for="credits" class="form-label">Credits</label>
            <input type="number" id="credits" name="credits" min="1" step="1"
                   class="form-control" placeholder="Enter credits" required
                   value="<?= htmlspecialchars((string) $credits, ENT_QUOTES, 'UTF-8') ?>">

            <button type="submit" name="save" class="btn-save">
                Save Course
            </button>

            <a href="admin_dashboard.php" class="btn-home">
                Return to Dashboard
            </a>

        </form>

    </div>

</body>
</html>

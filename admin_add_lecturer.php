
<?php
session_start();
include 'config_database.php';

$errors = [];
$success = '';
$courses = $pdo->query(
    'SELECT course_id, course_code, course_name FROM courses ORDER BY course_code'
)->fetchAll(PDO::FETCH_ASSOC);


if (empty($_SESSION['lecturer_form_token'])) {
    $_SESSION['lecturer_form_token'] = bin2hex(random_bytes(32));
}
$csrfValid = is_string($_POST['csrf_token'] ?? null)
    && hash_equals($_SESSION['lecturer_form_token'], $_POST['csrf_token']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_lecturer'])) {
    if (!$csrfValid) {
        $errors[] = 'Your form session expired. Please try again.';
    } else {
        $deleteLecturerId = filter_var(
            $_POST['delete_lecturer'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 4294967295]]
        );

        if ($deleteLecturerId === false) {
            $errors[] = 'Invalid lecturer ID.';
        } else {
            try {
                $deleteStmt = $pdo->prepare('DELETE FROM lecturers WHERE lecturer_id = ?');
                $deleteStmt->execute([$deleteLecturerId]);

                if ($deleteStmt->rowCount() > 0) {
                    $success = 'Lecturer deleted successfully.';
                    $_SESSION['lecturer_form_token'] = bin2hex(random_bytes(32));
                } else {
                    $errors[] = 'Lecturer not found.';
                }
            } catch (PDOException $exception) {
                if (($exception->errorInfo[1] ?? null) === 1451) {
                    $errors[] = 'This lecturer cannot be deleted while related records still reference them.';
                } else {
                    throw $exception;
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lecturer'])) {
    $lecturer_id = trim($_POST['lecturer_id'] ?? '');
    $lecturer_name = trim($_POST['lecturer_name'] ?? '');
    $lecturer_email = trim($_POST['lecturer_email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $course_id = trim($_POST['course_id'] ?? '');

    if (!$csrfValid) {
        $errors[] = 'Your form session expired. Please try again.';
    }

    if (filter_var(
        $lecturer_id,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 4294967295]]
    ) === false) {
        $errors[] = 'Lecturer ID must be a positive whole number.';
    }

    if ($lecturer_name === '') {
        $errors[] = 'Lecturer name is required.';
    }

    if (!filter_var($lecturer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($course_id === '') {
        $errors[] = 'Select a course for the lecturer.';
    } else {
        $courseCheck = $pdo->prepare('SELECT 1 FROM courses WHERE course_id = ?');
        $courseCheck->execute([$course_id]);
        if (!$courseCheck->fetchColumn()) {
            $errors[] = 'The selected course no longer exists. Please choose an existing course.';
        }
    }

    if (!$errors) {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            $errors[] = 'Database connection is unavailable.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO lecturers (lecturer_id, lecturer_name, lecturer_email, password, course_id) VALUES (?, ?, ?, ?, ?)'
            );

            if (!$stmt) {
                $errors[] = 'Unable to prepare the lecturer record.';
            } else {
                try {
                    $stmt->execute([
                        $lecturer_id,
                        $lecturer_name,
                        $lecturer_email,
                        $password,
                        $course_id
                    ]);
                    $success = 'Lecturer added successfully.';
                    $lecturer_id = $lecturer_name = $lecturer_email = $password = $course_id = '';
                    $_SESSION['lecturer_form_token'] = bin2hex(random_bytes(32));
                } catch (PDOException $exception) {
                    if (($exception->errorInfo[1] ?? null) === 1062) {
                        $errors[] = 'A lecturer with that ID or name already exists.';
                    } elseif (($exception->errorInfo[1] ?? null) === 1452) {
                        $errors[] = 'The selected course no longer exists. Please choose an existing course.';
                    } else {
                        throw $exception;
                    }
                }
            }
        }
    }
}

$lecturerRecords = $pdo->query(
    'SELECT l.lecturer_id, l.lecturer_name, l.lecturer_email, c.course_name
     FROM lecturers l
     LEFT JOIN courses c ON c.course_id = l.course_id
     ORDER BY l.lecturer_name'
)->fetchAll(PDO::FETCH_ASSOC);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lecturer - INTI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f7f2ed;
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 15px;
        }

        .form-card {
            background: white;
            width: 100%;
            max-width: 1000px;
            padding: 40px;
            border-radius: 15px;
            border-top: 5px solid #ed0010;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #ed0010;
            font-size: 34px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            color: #ed0010;
            font-size: 24px;
            font-weight: bold;
            margin: 35px 0 20px;
        }

        .form-label {
            font-weight: 600;
            color: #292929;
            margin-bottom: 8px;
        }

        .form-control {
            height: 52px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .form-control:focus {
            border-color: #ed0010;
            box-shadow: 0 0 0 0.15rem rgba(237, 0, 16, 0.12);
        }

        .btn-custom {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            margin-top: 8px;
            transition: 0.2s;
        }

        .btn-add {
            background-color: #ed0010;
            color: white;
        }

        .btn-add:hover {
            background-color: #c9000d;
            color: white;
        }

        .btn-back {
            background-color: #262626;
            color: white;
            text-decoration: none;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn-back:hover {
            background-color: #444;
            color: white;
        }

        .btn-delete {
            background: #ed0010;
            color: #fff;
            border: 0;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: bold;
        }

        .btn-delete:hover {
            background: #c9000d;
        }

        .alert {
            border-radius: 8px;
        }

        @media (max-width: 576px) {
            .form-card {
                padding: 25px 20px;
            }

            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

<div class="form-card">

    <h1>Add Lecturer</h1>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input type="hidden" name="csrf_token"
               value="<?= e($_SESSION['lecturer_form_token']) ?>">

        <label class="form-label" for="lecturer_id">Lecturer ID</label>
        <input type="text" class="form-control"
               id="lecturer_id" name="lecturer_id" placeholder="Enter lecturer ID"
               required maxlength="20">

        <label class="form-label" for="lecturer_name">Lecturer Name</label>
        <input type="text" class="form-control"
               id="lecturer_name" name="lecturer_name" placeholder="Enter lecturer name"
               required maxlength="150">

        <label class="form-label" for="lecturer_email">Email</label>
        <input type="email" class="form-control"
               id="lecturer_email" name="lecturer_email" placeholder="Enter email address"
               required maxlength="254">

        <label class="form-label" for="password">Password</label>
        <input type="password" class="form-control"
               id="password" name="password" placeholder="Enter password"
               required maxlength="255">

        <label class="form-label" for="course_id">Course</label>
        <?php if ($courses): ?>
            <select class="form-control" id="course_id" name="course_id" required>
                <option value="">Select a course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= e((string) $course['course_id']) ?>"
                        <?= (($_POST['course_id'] ?? '') == $course['course_id']) ? 'selected' : '' ?>>
                        <?= e($course['course_code'] . ' - ' . $course['course_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <div class="alert alert-warning">
                No courses are available. <a href="admin_add_course.php">Add a course</a> before adding a lecturer.
            </div>
            <select class="form-control" id="course_id" name="course_id" required disabled>
                <option value="">No courses available</option>
            </select>
        <?php endif; ?>

        <button type="submit" name="add_lecturer" class="btn-custom btn-add">
            Add Lecturer
        </button>

        <a href="admin_dashboard.php" class="btn-custom btn-back">
            Return to Dashboard
        </a>

    </form>

    <h2>Existing Lecturers</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th>Lecturer ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lecturerRecords as $lecturer): ?>
                    <tr>
                        <td><?= e((string) $lecturer['lecturer_id']) ?></td>
                        <td><?= e($lecturer['lecturer_name']) ?></td>
                        <td><?= e((string) ($lecturer['lecturer_email'] ?? '')) ?></td>
                        <td><?= e((string) ($lecturer['course_name'] ?? 'No course assigned')) ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Delete this lecturer record?');">
                                <input type="hidden" name="csrf_token"
                                       value="<?= e($_SESSION['lecturer_form_token']) ?>">
                                <input type="hidden" name="delete_lecturer"
                                       value="<?= e((string) $lecturer['lecturer_id']) ?>">
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($lecturerRecords)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No lecturers available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>

<?php
session_start();

$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'college_db';

$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$schemaReady = $conn->query(
    'CREATE TABLE IF NOT EXISTS subject_courses (
        subject_code VARCHAR(20) NOT NULL,
        course_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (subject_code, course_id),
        KEY idx_subject_courses_course_id (course_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
) !== false;

$subjectCodeColumn = $conn->query("SHOW COLUMNS FROM subject_courses LIKE 'subject_code'");
if ($schemaReady && $subjectCodeColumn && $subjectCodeColumn->num_rows > 0) {
    $column = $subjectCodeColumn->fetch_assoc();
    if (strtolower($column['Type']) !== 'varchar(20)') {
        $schemaReady = $conn->query(
            'ALTER TABLE subject_courses MODIFY COLUMN subject_code VARCHAR(20) NOT NULL'
        ) !== false;
    }
}

$message = '';
$message_type = '';
$selected_course_ids = [];
$courses = [];
$courseResult = $conn->query(
    'SELECT course_id, course_code, course_name FROM courses ORDER BY course_code'
);
if ($courseResult) {
    while ($course = $courseResult->fetch_assoc()) {
        $courses[] = $course;
    }
}
if (!$schemaReady || !$courseResult) {
    $message = 'Unable to load course options. Please check the database setup and try again.';
    $message_type = 'error';
}

if (empty($_SESSION['subject_form_token'])) {
    $_SESSION['subject_form_token'] = bin2hex(random_bytes(32));
}
$csrfValid = is_string($_POST['csrf_token'] ?? null)
    && hash_equals($_SESSION['subject_form_token'], $_POST['csrf_token']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_subject'])) {
    $deleteCode = is_string($_POST['delete_subject']) ? trim($_POST['delete_subject']) : '';

    if (!$csrfValid) {
        $message = 'Your form session expired. Please try again.';
        $message_type = 'error';
    } elseif (!$schemaReady || $deleteCode === '') {
        $message = 'Unable to delete the selected subject.';
        $message_type = 'error';
    } else {
        $conn->begin_transaction();
        $deleteLinks = $conn->prepare('DELETE FROM subject_courses WHERE subject_code = ?');
        $deleteSubject = $conn->prepare('DELETE FROM subjects WHERE subject_code = ?');

        if ($deleteLinks && $deleteSubject) {
            $deleteLinks->bind_param('s', $deleteCode);
            $deleteSubject->bind_param('s', $deleteCode);
            $deleted = $deleteLinks->execute() && $deleteSubject->execute();
            $subjectDeleted = $deleteSubject->affected_rows > 0;
            $deleteLinks->close();
            $deleteSubject->close();

            if ($deleted && $subjectDeleted && $conn->commit()) {
                $message = 'Subject deleted successfully.';
                $message_type = 'success';
                $_SESSION['subject_form_token'] = bin2hex(random_bytes(32));
                $_POST = [];
            } else {
                $conn->rollback();
                $message = $deleted
                    ? 'Subject not found or could not be deleted.'
                    : 'Unable to delete the subject.';
                $message_type = 'error';
            }
        } else {
            $conn->rollback();
            $message = 'Unable to prepare the subject deletion.';
            $message_type = 'error';
            if ($deleteLinks) {
                $deleteLinks->close();
            }
            if ($deleteSubject) {
                $deleteSubject->close();
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $schemaReady && $courseResult) {
    $subject_code = trim($_POST['subject_code'] ?? '');
    $subject_name = trim($_POST['subject_name'] ?? '');
    $credits = trim($_POST['credits'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $selected_course_ids = $_POST['course_ids'] ?? [];

    if (!is_array($selected_course_ids)) {
        $selected_course_ids = [];
    }
    $selected_course_ids = array_values(array_unique(array_filter(
        $selected_course_ids,
        static function ($course_id) {
            return is_string($course_id) && ctype_digit($course_id) && (int)$course_id > 0;
        }
    )));

    if (!$csrfValid) {
        $message = 'Your form session expired. Please try again.';
        $message_type = 'error';
    } elseif ($subject_code === '' || $subject_name === '' || $credits === '' || $semester === '' || empty($selected_course_ids)) {
        $message = 'Please fill in all subject details and select at least one course.';
        $message_type = 'error';
    } elseif (!ctype_digit($credits) || (int)$credits < 1) {
        $message = 'Credits must be a positive whole number.';
        $message_type = 'error';
    } elseif (!ctype_digit($semester) || (int)$semester < 1 || (int)$semester > 8) {
        $message = 'Please select a valid semester.';
        $message_type = 'error';
    } else {
        $course_ids = array_map('intval', $selected_course_ids);
        $courseCheck = $conn->prepare('SELECT 1 FROM courses WHERE course_id = ?');
        if ($courseCheck) {
            $course_id = 0;
            $courseCheck->bind_param('i', $course_id);
            $valid_course_count = 0;
            $validation_failed = false;
            foreach ($course_ids as $course_id) {
                if (!$courseCheck->execute()) {
                    $validation_failed = true;
                    break;
                }
                $courseCheck->store_result();
                if ($courseCheck->num_rows === 1) {
                    $valid_course_count++;
                }
                $courseCheck->free_result();
            }
            $courseCheck->close();

            if ($validation_failed) {
                $message = 'Unable to validate the selected courses.';
                $message_type = 'error';
            } elseif ($valid_course_count !== count($course_ids)) {
                $message = 'One or more selected courses are invalid. Please choose from the listed courses.';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare('INSERT INTO subjects (subject_code, subject_name, credits, semester) VALUES (?, ?, ?, ?)');
                if ($stmt) {
                    $credit_value = (int)$credits;
                    $semester_value = (int)$semester;
                    $stmt->bind_param('ssii', $subject_code, $subject_name, $credit_value, $semester_value);

                    $conn->begin_transaction();
                    if ($stmt->execute()) {
                        $stmt->close();

                        $linkStmt = $conn->prepare('INSERT INTO subject_courses (subject_code, course_id) VALUES (?, ?)');
                        if ($linkStmt) {
                            $linkStmt->bind_param('si', $subject_code, $course_id);
                            $links_saved = true;
                            foreach ($course_ids as $course_id) {
                                if (!$linkStmt->execute()) {
                                    $links_saved = false;
                                    break;
                                }
                            }
                            $linkStmt->close();

                            if ($links_saved && $conn->commit()) {
                                $message = 'Subject added successfully.';
                                $message_type = 'success';
                                $_SESSION['subject_form_token'] = bin2hex(random_bytes(32));
                                $_POST = [];
                                $selected_course_ids = [];
                            } else {
                                $conn->rollback();
                                $message = 'Unable to save the subject course assignments.';
                                $message_type = 'error';
                            }
                        } else {
                            $conn->rollback();
                            $message = 'Unable to prepare the course assignments.';
                            $message_type = 'error';
                        }
                    } else {
                        $conn->rollback();
                        $stmt->close();
                        $message = 'Unable to add subject. The subject code may already exist.';
                        $message_type = 'error';
                    }
                } else {
                    $message = 'Unable to prepare the subject record.';
                    $message_type = 'error';
                }
            }
        } else {
            $message = 'Unable to validate the selected courses.';
            $message_type = 'error';
        }
    }
}

$subjects = [];
$subjectResult = $conn->query(
    "SELECT s.subject_code, s.subject_name, s.credits, s.semester,
            GROUP_CONCAT(DISTINCT c.course_name ORDER BY c.course_name SEPARATOR ', ') AS courses
     FROM subjects s
     LEFT JOIN subject_courses sc ON sc.subject_code = s.subject_code
     LEFT JOIN courses c ON c.course_id = sc.course_id
     GROUP BY s.subject_code, s.subject_name, s.credits, s.semester
     ORDER BY s.subject_code"
);
if ($subjectResult) {
    while ($subject = $subjectResult->fetch_assoc()) {
        $subjects[] = $subject;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject - INTI</title>

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

        .form-card {
            background: #fff;
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

        .form-control,
        .form-select {
            height: 52px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #ed0010;
            box-shadow: 0 0 0 0.15rem rgba(237, 0, 16, 0.12);
        }

        .course-dropdown {
            position: relative;
            margin-bottom: 20px;
        }

        .course-dropdown summary {
            height: 52px;
            padding: 14px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            color: #495057;
            cursor: pointer;
            list-style: none;
        }

        .course-dropdown summary::-webkit-details-marker {
            display: none;
        }

        .course-dropdown summary::after {
            content: "\25BC";
            float: right;
            font-size: 12px;
        }

        .course-dropdown[open] summary {
            border-color: #ed0010;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }

        .course-options {
            position: absolute;
            z-index: 10;
            top: 100%;
            right: 0;
            left: 0;
            max-height: 220px;
            overflow-y: auto;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-top: 0;
            border-radius: 0 0 8px 8px;
            background: #fff;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .course-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            cursor: pointer;
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
        <h1>Add Subject</h1>

        <?php if ($message !== ''): ?>
            <div class="alert <?php echo $message_type === 'success' ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="admin_add_subject.php">
            <input type="hidden" name="csrf_token"
                   value="<?php echo htmlspecialchars($_SESSION['subject_form_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <label class="form-label" for="subject_code">Subject Code</label>
            <input class="form-control" id="subject_code" name="subject_code" type="text" maxlength="20" required
                   value="<?php echo htmlspecialchars($_POST['subject_code'] ?? ''); ?>"
                   placeholder="Enter subject code">

            <label class="form-label" for="subject_name">Subject Name</label>
            <input class="form-control" id="subject_name" name="subject_name" type="text" maxlength="100" required
                   value="<?php echo htmlspecialchars($_POST['subject_name'] ?? ''); ?>"
                   placeholder="Enter subject name">

            <label class="form-label" for="credits">Credit Hours</label>
            <input class="form-control" id="credits" name="credits" type="number" min="1" max="12" required
                   value="<?php echo htmlspecialchars($_POST['credits'] ?? ''); ?>"
                   placeholder="Enter credit hours">

            <label class="form-label" for="semester">Semester</label>
            <select class="form-select" id="semester" name="semester" required>
                <option value="">Select semester</option>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo (($_POST['semester'] ?? '') == $i) ? 'selected' : ''; ?>>
                        Semester <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>

            <div class="form-label">Courses</div>
            <details class="course-dropdown">
                <summary id="course_ids">Select one or more courses</summary>
                <div class="course-options">
                <?php foreach ($courses as $course): ?>
                    <label class="course-option">
                        <input type="checkbox" name="course_ids[]" value="<?php echo (int)$course['course_id']; ?>"
                            <?php echo in_array((string)$course['course_id'], $selected_course_ids, true) ? 'checked' : ''; ?>>
                        <span><?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </label>
                <?php endforeach; ?>
                </div>
            </details>
            <div class="form-text mb-3">Open the menu and check each course this subject belongs to.</div>

            <button type="submit" name="add_subject" class="btn-custom btn-add">Add Subject</button>
            <a href="admin_dashboard.php" class="btn-custom btn-back">Return to Dashboard</a>
        </form>

        <h2>Existing Subjects</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Credits</th>
                        <th>Semester</th>
                        <th>Course(s)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subject['subject_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($subject['subject_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string)$subject['credits'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string)$subject['semester'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($subject['courses'] ?: 'No course assigned', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Delete this subject and its course assignments?');">
                                    <input type="hidden" name="csrf_token"
                                           value="<?php echo htmlspecialchars($_SESSION['subject_form_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="delete_subject"
                                           value="<?php echo htmlspecialchars($subject['subject_code'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($subjects)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No subjects available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

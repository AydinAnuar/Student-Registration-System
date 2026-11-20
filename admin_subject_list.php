<?php
session_start();
include 'config_database.php';

if (empty($_SESSION['subject_form_token'])) {
    $_SESSION['subject_form_token'] = bin2hex(random_bytes(32));
}

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS subject_courses (
        subject_code VARCHAR(20) NOT NULL,
        course_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (subject_code, course_id),
        KEY idx_subject_courses_course_id (course_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$subjectCodeType = $pdo->query(
    "SELECT DATA_TYPE FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'subject_courses'
       AND COLUMN_NAME = 'subject_code'"
)->fetchColumn();
if ($subjectCodeType !== false && strtolower($subjectCodeType) !== 'varchar') {
    $pdo->exec(
        'ALTER TABLE subject_courses
         MODIFY COLUMN subject_code VARCHAR(20) NOT NULL'
    );
}

$subjects = $pdo->query(
    "SELECT s.subject_code, s.subject_name, s.credits, s.semester,
            GROUP_CONCAT(DISTINCT c.course_name
                         ORDER BY c.course_name SEPARATOR ', ') AS courses
     FROM subjects s
     LEFT JOIN subject_courses sc ON sc.subject_code = s.subject_code
     LEFT JOIN courses c ON c.course_id = sc.course_id
     GROUP BY s.subject_code, s.subject_name, s.credits, s.semester
     ORDER BY s.subject_code"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - INTI</title>
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
            max-width: 1000px;
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
            border-color: #f1dada;
        }

        .table thead th {
            background: #fff7f5;
            border-bottom-color: #ed0010;
        }

        .btn-back {
            display: flex;
            width: 100%;
            min-height: 50px;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background: #262626;
            color: white;
            text-decoration: none;
            margin-top: 20px;
        }

        .btn-back:hover {
            background: #444;
            color: white;
        }

        .btn-delete {
            background: #ed0010;
            color: white;
            border: 0;
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
        <h1>Subjects</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
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
                    <?php foreach ($subjects as $subject) { ?>
                        <tr>
                            <td><?= htmlspecialchars($subject['subject_code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($subject['subject_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $subject['credits'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $subject['semester'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($subject['courses'] ?: 'No course assigned', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <form method="post" action="admin_add_subject.php"
                                      onsubmit="return confirm('Delete this subject and its course assignments?');">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars($_SESSION['subject_form_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="delete_subject"
                                           value="<?= htmlspecialchars($subject['subject_code'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($subjects)) { ?>
                        <tr><td colspan="6" class="text-center text-muted">No subjects available.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <a href="admin_dashboard.php" class="btn-back">Return to Dashboard</a>
    </div>
</body>
</html>

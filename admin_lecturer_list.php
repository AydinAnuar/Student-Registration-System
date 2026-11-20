<?php
session_start();
include 'config_database.php';

if (empty($_SESSION['lecturer_form_token'])) {
    $_SESSION['lecturer_form_token'] = bin2hex(random_bytes(32));
}

$lecturers = $pdo->query(
    "SELECT l.lecturer_id, l.lecturer_name, l.lecturer_email, c.course_name
     FROM lecturers l
     LEFT JOIN courses c ON c.course_id = l.course_id
     ORDER BY l.lecturer_name"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturers - INTI</title>
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
        <h1>Lecturers</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Lecturer ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lecturers as $lecturer) { ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $lecturer['lecturer_id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($lecturer['lecturer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($lecturer['lecturer_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?= htmlspecialchars($lecturer['course_name'] ?: 'No course assigned', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <form method="post" action="admin_add_lecturer.php"
                                      onsubmit="return confirm('Delete this lecturer record?');">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= htmlspecialchars($_SESSION['lecturer_form_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="delete_lecturer"
                                           value="<?= htmlspecialchars((string) $lecturer['lecturer_id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($lecturers)) { ?>
                        <tr><td colspan="5" class="text-center text-muted">No lecturers available.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <a href="admin_dashboard.php" class="btn-back">Return to Dashboard</a>
    </div>
</body>
</html>

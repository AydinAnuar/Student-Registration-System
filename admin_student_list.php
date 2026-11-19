<?php

session_start();

include 'config_database.php';

$students = $pdo->query(
    "SELECT * FROM students"
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

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr><th>ID</th><th>Name</th><th>Email</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student) { ?>
                        <tr>
                            <td><?= htmlspecialchars($student['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['fullname'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($students)) { ?>
                        <tr><td colspan="3" class="text-center text-muted">No students registered.</td></tr>
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

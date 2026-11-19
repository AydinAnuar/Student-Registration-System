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

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_code = trim($_POST['subject_code'] ?? '');
    $subject_name = trim($_POST['subject_name'] ?? '');
    $credits = trim($_POST['credits'] ?? '');
    $semester = trim($_POST['semester'] ?? '');

    if ($subject_code === '' || $subject_name === '' || $credits === '' || $semester === '') {
        $message = 'Please fill in all subject details.';
        $message_type = 'error';
    } elseif (!ctype_digit($credits) || (int)$credits < 1) {
        $message = 'Credits must be a positive whole number.';
        $message_type = 'error';
    } else {
        $stmt = $conn->prepare('INSERT INTO subjects (subject_code, subject_name, credits, semester) VALUES (?, ?, ?, ?)');
        if ($stmt) {
            $credit_value = (int)$credits;
            $semester_value = (int)$semester;
            $stmt->bind_param('ssii', $subject_code, $subject_name, $credit_value, $semester_value);

            if ($stmt->execute()) {
                $message = 'Subject added successfully.';
                $message_type = 'success';
                $_POST = [];
            } else {
                $message = 'Unable to add subject. The subject code may already exist.';
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Unable to prepare the subject record.';
            $message_type = 'error';
        }
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
            max-width: 680px;
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

            <button type="submit" class="btn-custom btn-add">Add Subject</button>
            <a href="admin_dashboard.php" class="btn-custom btn-back">Return to Dashboard</a>
        </form>
    </div>
</body>
</html>

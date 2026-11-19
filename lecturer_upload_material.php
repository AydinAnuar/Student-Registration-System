<?php
require 'lecturer_auth.php';
include 'config_database.php';

const MATERIAL_MAX_SIZE = 20971520;

$errors = [];
$success = isset($_GET['uploaded']) ? 'Material uploaded successfully.' : '';
$allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];

if (empty($_SESSION['material_upload_token'])) {
    $_SESSION['material_upload_token'] = bin2hex(random_bytes(32));
}

$titleValue = $_POST['title'] ?? '';
$formTitle = is_string($titleValue) ? $titleValue : '';

$courseQuery = $pdo->prepare(
    'SELECT c.course_id, c.course_code, c.course_name
     FROM lecturers l
     INNER JOIN courses c ON c.course_id = l.course_id
     WHERE l.lecturer_id = ?'
);
$courseQuery->execute([(int) $_SESSION['lecturer_id']]);
$course = $courseQuery->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $title = trim($formTitle);

    if (!is_string($token) || !hash_equals($_SESSION['material_upload_token'], $token)) {
        $errors[] = 'Your form session expired. Please try again.';
    }

    if ($title === '' || strlen($title) > 200) {
        $errors[] = 'Enter a material title up to 200 characters.';
    }

    if (!$course) {
        $errors[] = 'No course is assigned to your lecturer account.';
    }

    $upload = $_FILES['material'] ?? null;
    if (
        !is_array($upload) ||
        !isset($upload['error'], $upload['name'], $upload['size'], $upload['tmp_name']) ||
        !is_string($upload['name']) ||
        !is_string($upload['tmp_name'])
    ) {
        $errors[] = 'Choose a material file to upload.';
    } elseif ((int) $upload['error'] !== UPLOAD_ERR_OK) {
        switch ((int) $upload['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'The selected file exceeds the upload size limit.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'The file was only partially uploaded. Please try again.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'Choose a material file to upload.';
                break;
            default:
                $errors[] = 'The file could not be uploaded. Please try again.';
        }
    } else {
        $originalName = basename(str_replace('\\', '/', (string) $upload['name']));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            $errors[] = 'Allowed file types: PDF, Word, PowerPoint, Excel, and plain text.';
        }

        if ((int) $upload['size'] <= 0 || (int) $upload['size'] > MATERIAL_MAX_SIZE) {
            $errors[] = 'The file must be no larger than 20 MB.';
        }

        if (strlen($originalName) > 255) {
            $errors[] = 'The file name is too long.';
        }

        if (!is_uploaded_file($upload['tmp_name'])) {
            $errors[] = 'The uploaded file could not be verified.';
        }
    }

    if (!$errors && $course) {
        $storageDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'private_materials';
        if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0750, true) && !is_dir($storageDirectory)) {
            $errors[] = 'The material storage directory could not be created.';
        } elseif (!is_writable($storageDirectory)) {
            $errors[] = 'The material storage directory is not writable.';
        } else {
            $storedName = bin2hex(random_bytes(32));
            $destination = $storageDirectory . DIRECTORY_SEPARATOR . $storedName;

            if (!move_uploaded_file($upload['tmp_name'], $destination)) {
                $errors[] = 'The uploaded file could not be saved.';
            } else {
                try {
                    $insert = $pdo->prepare(
                        'INSERT INTO course_materials
                            (course_id, lecturer_id, title, original_filename, stored_filename, file_size)
                         VALUES (?, ?, ?, ?, ?, ?)'
                    );
                    $insert->execute([
                        (int) $course['course_id'],
                        (int) $_SESSION['lecturer_id'],
                        $title,
                        $originalName,
                        $storedName,
                        (int) $upload['size']
                    ]);
                } catch (PDOException $exception) {
                    unlink($destination);
                    throw $exception;
                }

                $_SESSION['material_upload_token'] = bin2hex(random_bytes(32));
                header('Location: lecturer_upload_material.php?uploaded=1');
                exit();
            }
        }
    }
}

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
    <title>Upload Course Material - INTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="lecturer.css" rel="stylesheet">
</head>
<body>
    <main class="list-card">
        <h1>Upload Course Material</h1>

        <?php if ($success !== '') { ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php } ?>
        <?php if ($errors) { ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error) { ?>
                        <li><?= e($error) ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <?php if ($course) { ?>
            <p class="text-muted">
                Course: <strong><?= e($course['course_code'] . ' - ' . $course['course_name']) ?></strong>
            </p>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token"
                       value="<?= e($_SESSION['material_upload_token']) ?>">

                <label for="title" class="form-label">Material Title</label>
                <input id="title" name="title" type="text" class="form-control mb-3"
                       maxlength="200" required value="<?= e($formTitle) ?>">

                <label for="material" class="form-label">File</label>
                <input id="material" name="material" type="file" class="form-control mb-2"
                       accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt" required>
                <small class="form-text text-muted d-block mb-3">
                    Maximum size: 20 MB. Allowed: PDF, Word, PowerPoint, Excel, or plain text.
                </small>

                <button type="submit" class="btn btn-add full-width">Upload Material</button>
            </form>
        <?php } else { ?>
            <div class="alert alert-warning">
                No course is assigned to your lecturer account. Contact an administrator before uploading materials.
            </div>
        <?php } ?>

        <a href="lecturer_dashboard.php" class="btn-back mt-3">Return to Dashboard</a>
    </main>
</body>
</html>

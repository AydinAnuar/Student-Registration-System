<?php
session_start();

if (empty($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit();
}

include 'config_database.php';

if (isset($_GET['download'])) {
    $materialId = filter_var($_GET['download'], FILTER_VALIDATE_INT);
    if (!$materialId || $materialId < 1) {
        http_response_code(404);
        exit('Material not found.');
    }

    $downloadQuery = $pdo->prepare(
        'SELECT m.original_filename, m.stored_filename
         FROM course_materials m
         WHERE m.material_id = ?
           AND EXISTS (
               SELECT 1
               FROM enrollments e
               WHERE e.student_id = ? AND e.course_id = m.course_id
           )'
    );
    $downloadQuery->execute([$materialId, (int) $_SESSION['student_id']]);
    $material = $downloadQuery->fetch(PDO::FETCH_ASSOC);

    if (!$material) {
        http_response_code(404);
        exit('Material not found.');
    }

    $storageDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'private_materials';
    $storedName = basename($material['stored_filename']);
    $filePath = $storageDirectory . DIRECTORY_SEPARATOR . $storedName;

    if (!is_file($filePath) || !is_readable($filePath)) {
        http_response_code(404);
        exit('Material file is unavailable.');
    }

    $downloadName = preg_replace('/[\r\n"]/', '_', basename($material['original_filename']));
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize($filePath));
    header('Content-Disposition: attachment; filename="' . $downloadName . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
    header('X-Content-Type-Options: nosniff');
    readfile($filePath);
    exit();
}

$materialQuery = $pdo->prepare(
    'SELECT m.material_id, m.title, m.original_filename, m.file_size, m.created_at,
            c.course_code, c.course_name
     FROM course_materials m
     INNER JOIN courses c ON c.course_id = m.course_id
     WHERE EXISTS (
         SELECT 1
         FROM enrollments e
         WHERE e.student_id = ? AND e.course_id = m.course_id
     )
     ORDER BY c.course_code, m.created_at DESC, m.material_id DESC'
);
$materialQuery->execute([(int) $_SESSION['student_id']]);
$materials = $materialQuery->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Course Materials - INTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="student.css" rel="stylesheet">
</head>
<body>
    <main class="student-card">
        <h1 class="student-title">Course Materials</h1>
        <a href="student_dashboard.php" class="student-dark student-action mb-4">Return to Dashboard</a>

        <?php if (!$materials) { ?>
            <div class="alert alert-info student-alert">
                No materials are available for your enrolled courses yet.
            </div>
        <?php } else { ?>
            <div class="row g-3">
                <?php foreach ($materials as $material) { ?>
                    <div class="col-12">
                        <div class="card student-message">
                            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div>
                                    <h2 class="card-title h5"><?= e($material['title']) ?></h2>
                                    <p class="mb-1">
                                        <?= e($material['course_code'] . ' - ' . $material['course_name']) ?>
                                    </p>
                                    <small class="text-muted">
                                        <?= e($material['original_filename']) ?>
                                        &middot; <?= number_format((int) $material['file_size'] / 1048576, 2) ?> MB
                                        &middot; <?= e($material['created_at']) ?>
                                    </small>
                                </div>
                                <a class="student-primary student-action student-download px-3"
                                   href="student_materials.php?download=<?= (int) $material['material_id'] ?>">
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </main>
</body>
</html>

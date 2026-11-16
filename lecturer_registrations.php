<?php
require 'lecturer_auth.php';
include 'config_database.php';

$perPage = 20;
$totalRows = $pdo->query(
    'SELECT COUNT(*) FROM enrollments e
     INNER JOIN students s ON s.id = e.student_id
     INNER JOIN courses c ON c.id = e.course_id'
)->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = min($totalPages, max(1, $requestedPage ?: 1));
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    'SELECT s.id AS student_id, s.fullname, s.email, c.course_code, c.course_name
     FROM enrollments e
     INNER JOIN students s ON s.id = e.student_id
     INNER JOIN courses c ON c.id = e.course_id
     ORDER BY s.fullname, c.course_code
     LIMIT :limit OFFSET :offset'
);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registrations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Student Registrations</h2>
        <hr>
        <a href="lecturer_dashboard.php" class="btn btn-secondary mb-3">Return to Dashboard</a>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr><th>Student ID</th><th>Student</th><th>Email</th><th>Course Code</th><th>Course Name</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $registration) { ?>
                        <tr>
                            <td><?= htmlspecialchars($registration['student_id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($registration['fullname'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($registration['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($registration['course_code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($registration['course_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($registrations)) { ?>
                        <tr><td colspan="5" class="text-center text-muted">No course registrations yet.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1) { ?>
            <nav aria-label="Registration pages">
                <ul class="pagination justify-content-center mt-4">
                    <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <li class="page-item<?= $i === $page ? ' active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php } ?>
                    <li class="page-item<?= $page >= $totalPages ? ' disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php } ?>
    </div>
</body>
</html>

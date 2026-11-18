<?php
session_start();

include 'config_database.php';

$errors = [];
$success = '';
$name = $email = $phone = $department = '';

if (empty($_SESSION['lecturer_form_token'])) {
	$_SESSION['lecturer_form_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$lecturer_id = trim($_POST['lecturer_id'] ?? '');
	$lecturer_name = trim($_POST['lecturer_name'] ?? '');
	$lecturer_email = trim($_POST['lecturer_email'] ?? '');
	$password = trim($_POST['password'] ?? '');
	$course_id = trim($_POST['course_id'] ?? '');

	if (!hash_equals($_SESSION['lecturer_form_token'], $_POST['csrf_token'] ?? '')) {
		$errors[] = 'Your form session expired. Please try again.';
	}
	if ($lecturer_name === '') {
		$errors[] = 'Lecturer name is required.';
	}
	if (!filter_var($lecturer_email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = 'Enter a valid email address.';
	}
	if ($course_id === '') {
		$errors[] = 'Course ID is required.';
	}

	if (!$errors) {
		if (!isset($conn) || !($conn instanceof mysqli)) {
			$errors[] = 'Database connection is unavailable.';
		} else {
			$stmt = $conn->prepare(
				'INSERT INTO lecturers (lecturer_id, lecturer_name, lecturer_email, password, course_id) VALUES (?, ?, ?, ?, ?)'
			);
			if (!$stmt) {
				$errors[] = 'Unable to prepare the lecturer record.';
			} else {
				$stmt->bind_param('ssss', $lecturer_id, $lecturer_name, $lecturer_email, $password, $course_id);
				if ($stmt->execute()) {
					$success = 'Lecturer added successfully.';
					$lecturer_id = $lecturer_name = $lecturer_email = $password = $course_id = '';
					$_SESSION['lecturer_form_token'] = bin2hex(random_bytes(32));
				} else {
					$errors[] = $stmt->errno === 1062
						? 'A lecturer with that email already exists.'
						: 'Unable to add lecturer. Please try again.';
				}
				$stmt->close();
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
    <title>Add Lecturer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
	<main>
		<h1>Add Lecturer</h1>

		<?php if ($success !== ''): ?>
			<p role="status"><?= e($success) ?></p>
		<?php endif; ?>
		<?php if ($errors): ?>
			<div role="alert">
				<ul>
					<?php foreach ($errors as $error): ?>
						<li><?= e($error) ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<form method="POST" class="d-flex flex-column flex-grow-1">
			<input type="hidden" name="csrf_token" value="<?= e($_SESSION['lecturer_form_token']) ?>">

			<p>
				<label for="name">Lecturer name</label><br>
				<input id="name" name="name" type="text" maxlength="150" required value="<?= e($name) ?>">
			</p>
			<p>
				<label for="email">Email</label><br>
				<input id="email" name="email" type="email" maxlength="254" required value="<?= e($email) ?>">
			</p>
			<p>
				<label for="phone">Phone</label><br>
				<input id="phone" name="phone" type="tel" maxlength="30" value="<?= e($phone) ?>">
			</p>
			<p>
				<label for="department">Department</label><br>
				<input id="department" name="department" type="text" maxlength="120" required value="<?= e($department) ?>">
			</p>
			<div class="d-flex justify-content-end gap-2">
				<a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
			<button class="btn btn-success btn-lg px-5" type="submit">Add Lecturer</button>
			</div>
		</form>
	</main>
</body>
</html>
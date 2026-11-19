<?php

$host = "localhost";
$dbname = "college_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $replyColumns = [
        'reply' => 'TEXT NULL',
        'replied_at' => 'DATETIME NULL',
        'reply_lecturer_id' => 'INT UNSIGNED NULL'
    ];

    foreach ($replyColumns as $column => $definition) {
        $check = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $check->execute([$dbname, 'student_messages', $column]);

        if ((int) $check->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE student_messages ADD COLUMN `$column` $definition");
        }
    }

    $lecturerColumns = [
        'lecturer_email' => 'VARCHAR(254) NULL',
        'course_id' => 'VARCHAR(20) NULL'
    ];

    foreach ($lecturerColumns as $column => $definition) {
        $check = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $check->execute([$dbname, 'lecturers', $column]);

        if ((int) $check->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE lecturers ADD COLUMN `$column` $definition");
        }
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS course_materials (
            material_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            course_id INT UNSIGNED NOT NULL,
            lecturer_id INT UNSIGNED NOT NULL,
            title VARCHAR(200) NOT NULL,
            original_filename VARCHAR(255) NOT NULL,
            stored_filename CHAR(64) NOT NULL,
            file_size INT UNSIGNED NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (material_id),
            UNIQUE KEY uq_course_materials_stored_filename (stored_filename),
            KEY idx_course_materials_course_id (course_id),
            KEY idx_course_materials_lecturer_id (lecturer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

} catch (PDOException $e) {
    die("Connection Failed: " . $e->getMessage());
}
?>

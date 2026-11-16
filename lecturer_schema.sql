CREATE TABLE IF NOT EXISTS lecturers (
    lecturer_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    lecturer_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    PRIMARY KEY (lecturer_id),
    UNIQUE KEY uq_lecturers_name (lecturer_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS student_messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_student_messages_student_id (student_id),
    KEY idx_student_messages_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

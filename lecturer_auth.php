<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['lecturer_id'])) {
    header('Location: lecturer_login.php');
    exit();
}

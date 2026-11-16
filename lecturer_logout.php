<?php
session_start();
unset($_SESSION['lecturer_id'], $_SESSION['lecturer_name']);
header('Location: lecturer_login.php');
exit();

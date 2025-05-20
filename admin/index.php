<?php
// Redirect to dashboard if logged in, otherwise to login page
require_once "../includes/db.php";
require_once "../includes/functions.php";

start_session_if_not_started();

if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>

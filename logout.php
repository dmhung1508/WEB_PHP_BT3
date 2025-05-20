<?php
require_once "includes/functions.php";

start_session_if_not_started();

// Xóa tất cả các biến session
$_SESSION = array();

// Hủy session
session_destroy();

// Chuyển hướng về trang chủ
header("Location: index.php");
exit();
?>

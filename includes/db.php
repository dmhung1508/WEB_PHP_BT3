<?php
// Thông tin kết nối cơ sở dữ liệu
$host = 'localhost';
$username = 'root';
$password = 'hung1234';
$database = 'ecomer_db';

// Tạo kết nối
$conn = mysqli_connect($host, $username, $password, $database);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Đặt charset là utf8mb4
mysqli_set_charset($conn, "utf8mb4");
?>

<?php
// SET TIMEZONE
define('TIMEZONE_VIETNAM', 'Asia/Ho_Chi_Minh');
date_default_timezone_set(TIMEZONE_VIETNAM); //repair

// 1. Thông tin cấu hình (Credentials)
$host = 'localhost';
$db_name = 'finance_db';
$username = 'root';
$password = '';

// 2. Chuỗi kết nối DSN
$dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";

// 3. Các tùy chọn PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Báo lỗi dạng Exception (dễ bắt lỗi)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Lấy dữ liệu dạng mảng kết hợp (key-value)
    PDO::ATTR_EMULATE_PREPARES => false, // Tắt giả lập prepare (tăng bảo mật)
];

// 4. Thực hiện kết nối
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Lỗi kết nối Database: " . $e->getMessage()); // Tạm thời show lỗi để debug, sau này sẽ ẩn đi
}
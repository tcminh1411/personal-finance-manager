<?php
// config/database.php

// 1. Thông tin cấu hình (Credentials)
$host = 'localhost';
$db_name = 'finance_db';
$username = 'root';      // Mặc định XAMPP là 'root'
$password = '';          // Mặc định XAMPP pass là rỗng (trống)

// 2. Chuỗi kết nối DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";

// 3. Các tùy chọn PDO (Rất quan trọng)
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Báo lỗi dạng Exception (dễ bắt lỗi)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Lấy dữ liệu dạng mảng kết hợp (key-value)
    PDO::ATTR_EMULATE_PREPARES => false,                  // Tắt giả lập prepare (tăng bảo mật)
];

// 4. Thực hiện kết nối trong khối try-catch
try {
    $pdo = new PDO($dsn, $username, $password, $options);
    // Nếu chạy đến đây mà không lỗi nghĩa là kết nối thành công!
} catch (PDOException $e) {
    // Nếu lỗi, ghi vào log và hiển thị thông báo thân thiện (không show pass ra ngoài)
    error_log($e->getMessage());
    die("Lỗi kết nối Database: " . $e->getMessage()); // Tạm thời show lỗi để debug, sau này sẽ ẩn đi
}

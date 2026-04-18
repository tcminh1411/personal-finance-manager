<?php
define('TIMEZONE_VIETNAM', 'Asia/Ho_Chi_Minh');
date_default_timezone_set(TIMEZONE_VIETNAM);

$host = 'localhost';
$db_name = 'finance_db';
$username = 'root';
$password = '';

$dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Report errors as Exception (easy to catch errors)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch data as associative array (key-value)
    PDO::ATTR_EMULATE_PREPARES => false, // Disable prepare emulation (increase security)
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection error: " . $e->getMessage()); // Temporarily show error for debugging, will hide later
}

<?php
/**
 * Registration Process Handler
 * Creates new user account with password hashing
 * Location: auth/register-process.php
 */

// Define constants to avoid string duplication
define('REGISTER_PAGE', '../register');
define('LOGIN_PAGE', '../login');
define('HEADER_LOCATION', 'Location: ');
define('SESSION_REGISTER_ERROR', 'register_error');
define('SESSION_REGISTER_SUCCESS', 'register_success');

session_start();
require_once '../config/database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header(HEADER_LOCATION . REGISTER_PAGE);
    exit;
}

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

// Validate input
$errors = [];

// Validate username
if (empty($username)) {
    $errors[] = 'Vui lòng nhập tên đăng nhập!';
} elseif (strlen($username) < 3 || strlen($username) > 50) {
    $errors[] = 'Tên đăng nhập phải từ 3-50 ký tự!';
} elseif (!preg_match('/^\w+$/', $username)) {
    $errors[] = 'Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới!';
}

// Validate password
if (empty($password)) {
    $errors[] = 'Vui lòng nhập mật khẩu!';
} elseif (strlen($password) < 6) {
    $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự!';
}

// Validate password confirmation
if ($password !== $passwordConfirm) {
    $errors[] = 'Mật khẩu xác nhận không khớp!';
}

// Return errors if any
if (!empty($errors)) {
    $_SESSION[SESSION_REGISTER_ERROR] = implode(' ', $errors);
    header(HEADER_LOCATION . REGISTER_PAGE);
    exit;
}

try {
    // Check if username already exists
    $sql = "SELECT id FROM users WHERE username = :username LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);

    if ($stmt->fetch()) {
        $_SESSION[SESSION_REGISTER_ERROR] = 'Tên đăng nhập đã tồn tại!';
        header(HEADER_LOCATION . REGISTER_PAGE);
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $sql = "INSERT INTO users (username, password, created_at)
            VALUES (:username, :password, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username' => $username,
        ':password' => $hashedPassword
    ]);

    // Success - redirect to login with success message
    $_SESSION[SESSION_REGISTER_SUCCESS] = 'Đăng ký thành công! Vui lòng đăng nhập.';
    header(HEADER_LOCATION . LOGIN_PAGE);
    exit;

} catch (PDOException $e) {
    // Log error (in production, log to file)
    error_log("Registration error: " . $e->getMessage());

    $_SESSION[SESSION_REGISTER_ERROR] = 'Lỗi hệ thống. Vui lòng thử lại!';
    header(HEADER_LOCATION . REGISTER_PAGE);
    exit;
}
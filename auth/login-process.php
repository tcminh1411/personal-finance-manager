<?php
/**
 * Login Process Handler - UNIFIED VERSION
 * Handles both plain text AND hashed passwords
 */

define('LOGIN_PAGE', '../login');
define('MAIN_PAGE', '../index');
define('HEADER_LOCATION', 'Location: ');
define('SESSION_USER_ID', 'user_id');
define('SESSION_USERNAME', 'username');
define('SESSION_LOGGED_IN', 'logged_in');
define('SESSION_LAST_ACTIVITY', 'last_activity');
define('SESSION_LOGIN_ERROR', 'login_error');

session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header(HEADER_LOCATION . LOGIN_PAGE);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION[SESSION_LOGIN_ERROR] = 'Vui lòng nhập đầy đủ thông tin!';
    header(HEADER_LOCATION . LOGIN_PAGE);
    exit;
}

try {
    $sql = "SELECT id, username, password FROM users WHERE username = :username LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION[SESSION_LOGIN_ERROR] = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        header(HEADER_LOCATION . LOGIN_PAGE);
        exit;
    }

    // SMART PASSWORD CHECK - Handles BOTH plain text and hashed
    $passwordMatches = false;

    // Check if password is hashed (bcrypt starts with $2y$)
    if (substr($user['password'], 0, 4) === '$2y$') {
        // Password is hashed → Use password_verify()
        $passwordMatches = password_verify($password, $user['password']);

        // For debugging (remove after fixing):
        error_log("User: {$username} - Using hash verification");
    } else {
        // Password is plain text → Use direct comparison
        $passwordMatches = ($password === $user['password']);

        // For debugging (remove after fixing):
        error_log("User: {$username} - Using plain text comparison (UPGRADE NEEDED!)");

        // Auto-upgrade: Convert to hash for next login
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateSql = "UPDATE users SET password = :password WHERE id = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            ':password' => $hashedPassword,
            ':id' => $user['id']
        ]);
        error_log("Auto-upgraded password for user: {$username}");
    }

    if ($passwordMatches) {
        // Login success
        $_SESSION[SESSION_USER_ID] = $user['id'];
        $_SESSION[SESSION_USERNAME] = $user['username'];
        $_SESSION[SESSION_LOGGED_IN] = true;
        $_SESSION[SESSION_LAST_ACTIVITY] = time();

        session_regenerate_id(true);

        header(HEADER_LOCATION . MAIN_PAGE);
        exit;
    } else {
        // Login failed
        $_SESSION[SESSION_LOGIN_ERROR] = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        header(HEADER_LOCATION . LOGIN_PAGE);
        exit;
    }

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION[SESSION_LOGIN_ERROR] = 'Lỗi hệ thống. Vui lòng thử lại!';
    header(HEADER_LOCATION . LOGIN_PAGE);
    exit;
}
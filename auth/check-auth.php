<?php
/**
 * Authentication Check
 * Include this file at the top of protected pages
 * Location: auth/check-auth.php
 * Usage: require_once 'auth/check-auth';
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // User not logged in - redirect to login page
    header('Location: login');
    exit;
}

// Optional: Check session timeout (30 minutes)
$session_timeout = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];

    if ($elapsed_time > $session_timeout) {
        // Session expired
        session_unset();
        session_destroy();
        header('Location: login');
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();
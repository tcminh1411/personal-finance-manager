<?php
/**
 * Logout Handler
 * Destroys session and redirects to login
 * Location: auth/logout.php
 */

session_start();

// Unset all session variables
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: ../login');
exit;
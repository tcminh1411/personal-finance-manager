<?php

/**
 * Logout Handler
 * Destroys session and redirects to login
 * Location: auth/logout.php
 */

session_start();

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

header('Location: ../login');
exit;

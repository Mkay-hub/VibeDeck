<?php
// Authentication functions for user session management

session_start();

// Check if user is logged in, redirect to login if not
function check_login()
{
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

// Log out user by clearing session data
function logout()
{
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session and clear session cookie
    if (session_id() != '' || isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

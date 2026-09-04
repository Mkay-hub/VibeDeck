<?php
// Authentication functions for user session management

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

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
        setcookie(session_name(), '', time() - 3600, '/', '', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', true);
    }
    session_destroy();
}

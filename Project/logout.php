<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !valid_csrf_token()) {
    http_response_code(403);
    exit('Invalid logout request.');
}

logout();
header('Location: login.php');
exit;

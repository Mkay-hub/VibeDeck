<?php
require_once 'includes/auth.php';
header('Location: ' . (isset($_SESSION['user']) ? 'dashboard.php' : 'login.php'));
exit;

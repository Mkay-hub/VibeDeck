<?php
// Database configuration file
// Sets up PDO connection to MySQL database

// Database connection details
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'socialdb';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

// Create DSN string for PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options for secure and consistent behavior
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Attempt to connect to database
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('The service is temporarily unavailable. Please try again later.');
}

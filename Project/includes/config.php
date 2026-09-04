<?php
// Database configuration file
// Sets up PDO connection to MySQL database

// Database connection details
$host = 'localhost';
$db = 'socialdb';
$user = 'root';
$pass = '';
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
    die("Database connection failed: " . $e->getMessage());
}

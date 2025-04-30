<?php

// Database Connection
$pdo = null;
try {
    // Construct DSN with port and charset
    $dsn = 'mysql:host=' . getenv('DB_HOST') . 
           ';dbname=' . getenv('DB_NAME') . 
           ';port=' . (getenv('DB_PORT') ?: 3306) . 
           ';charset=utf8mb4';
    // Initialize PDO
    $pdo = new PDO(
        $dsn,
        getenv('DB_USER'),
        getenv('DB_PASS')
    );

    // Set PDO attributes
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Enforce native prepared statements
} catch (PDOException $e) {
    // Log error and show generic message
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}

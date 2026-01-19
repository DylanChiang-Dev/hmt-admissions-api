<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use HmtAdmissions\Api\Core\Database;

try {
    $pdo = Database::getConnection();
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id VARCHAR(36) PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    $pdo->exec($sql);
    echo "Database initialized successfully.\n";
} catch (Exception $e) {
    echo "Error initializing database: " . $e->getMessage() . "\n";
}

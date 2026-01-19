<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use HmtAdmissions\Api\Core\Database;

try {
    $pdo = Database::getConnection();

    // Questions Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS questions (
            id VARCHAR(36) PRIMARY KEY,
            exam_path VARCHAR(50) NOT NULL,
            subject VARCHAR(50) NOT NULL,
            track VARCHAR(50) DEFAULT NULL,
            question_type VARCHAR(50) NOT NULL,
            stem TEXT NOT NULL,
            options JSON DEFAULT NULL,
            difficulty TINYINT DEFAULT 1,
            tags JSON DEFAULT NULL,
            answer_key JSON DEFAULT NULL,
            explanation TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Lesson Packs Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lesson_packs (
            id VARCHAR(100) PRIMARY KEY,
            date DATE NOT NULL,
            exam_path VARCHAR(50) NOT NULL,
            track VARCHAR(50) DEFAULT NULL,
            subject VARCHAR(50) DEFAULT NULL,
            question_ids JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_pack (date, exam_path, track, subject)
        )
    ");

    echo "Schema updated successfully (v2).\n";
} catch (Exception $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}

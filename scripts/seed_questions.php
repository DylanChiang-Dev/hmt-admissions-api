<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
use HmtAdmissions\Api\Core\Database;

try {
    $pdo = Database::getConnection();

    // Seed Questions
    $q1Id = 'q-001';
    $qData = [
        $q1Id,
        'undergrad_joint',
        'math',
        'single_choice',
        '1+1=?',
        json_encode([['label'=>'A','content'=>'2'], ['label'=>'B','content'=>'3']]),
        1,
        '"A"'
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO questions (id, exam_path, subject, question_type, stem, options, difficulty, answer_key) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute($qData);

    // Seed Pack
    $packId = 'lp-today-' . date('Y-m-d');
    $packData = [
        $packId,
        date('Y-m-d'),
        'undergrad_joint',
        json_encode([$q1Id])
    ];

    $stmtPack = $pdo->prepare("INSERT IGNORE INTO lesson_packs (id, date, exam_path, question_ids) VALUES (?,?,?,?)");
    $stmtPack->execute($packData);

    echo "Seeded successfully for date: " . date('Y-m-d') . "\n";
} catch (Exception $e) {
    echo "Error seeding: " . $e->getMessage() . "\n";
}

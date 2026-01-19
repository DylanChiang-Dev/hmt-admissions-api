<?php

namespace App\Repositories\MySql;

use App\Repositories\Interfaces\AttemptRepositoryInterface;
use App\Storage\Db;
use App\Utils\Uuid;
use PDO;

class MySqlAttemptRepository implements AttemptRepositoryInterface
{
    public function save(array $attemptData): array
    {
        $pdo = Db::getInstance();

        if (empty($attemptData['id'])) {
            $attemptData['id'] = Uuid::generate();
        }

        // Ensure defaults
        $attemptData['created_at'] = $attemptData['created_at'] ?? date('Y-m-d H:i:s');

        // Prepare JSON fields
        $answerJson = is_array($attemptData['answer_json'])
            ? json_encode($attemptData['answer_json'])
            : $attemptData['answer_json'];

        $sql = "INSERT INTO attempts (
                    id,
                    user_id,
                    question_id,
                    answer_json,
                    correct,
                    elapsed_ms,
                    created_at
                ) VALUES (
                    :id,
                    :user_id,
                    :question_id,
                    :answer_json,
                    :correct,
                    :elapsed_ms,
                    :created_at
                )";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id' => $attemptData['id'],
            ':user_id' => $attemptData['user_id'],
            ':question_id' => $attemptData['question_id'],
            ':answer_json' => $answerJson,
            ':correct' => $attemptData['correct'] ? 1 : 0,
            ':elapsed_ms' => $attemptData['elapsed_ms'],
            ':created_at' => $attemptData['created_at']
        ]);

        return $attemptData;
    }
}

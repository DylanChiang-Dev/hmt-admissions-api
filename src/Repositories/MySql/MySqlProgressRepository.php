<?php

namespace App\Repositories\MySql;

use App\Repositories\Interfaces\ProgressRepositoryInterface;
use App\Storage\Db;
use PDO;

class MySqlProgressRepository implements ProgressRepositoryInterface
{
    public function getUserProgress(string $userId): array
    {
        $pdo = Db::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM user_progress WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            return [
                'user_id' => $userId,
                'streak_current' => 0,
                'streak_best' => 0,
                'daily_goal' => 10,
                'daily_done_count' => 0,
                'subject_mastery_json' => [],
                'last_activity_at' => null
            ];
        }

        // Decode JSON fields
        if (isset($row['subject_mastery_json']) && is_string($row['subject_mastery_json'])) {
            $row['subject_mastery_json'] = json_decode($row['subject_mastery_json'], true);
        } else {
            $row['subject_mastery_json'] = [];
        }

        return $row;
    }

    public function saveUserProgress(string $userId, array $data): void
    {
        $pdo = Db::getInstance();

        $subjectMasteryJson = isset($data['subject_mastery_json'])
            ? json_encode($data['subject_mastery_json'])
            : null;

        $sql = "INSERT INTO user_progress (
                    user_id,
                    streak_current,
                    streak_best,
                    daily_goal,
                    daily_done_count,
                    subject_mastery_json,
                    last_activity_at
                ) VALUES (
                    :user_id,
                    :streak_current,
                    :streak_best,
                    :daily_goal,
                    :daily_done_count,
                    :subject_mastery_json,
                    :last_activity_at
                ) ON DUPLICATE KEY UPDATE
                    streak_current = VALUES(streak_current),
                    streak_best = VALUES(streak_best),
                    daily_goal = VALUES(daily_goal),
                    daily_done_count = VALUES(daily_done_count),
                    subject_mastery_json = VALUES(subject_mastery_json),
                    last_activity_at = VALUES(last_activity_at)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':streak_current' => $data['streak_current'] ?? 0,
            ':streak_best' => $data['streak_best'] ?? 0,
            ':daily_goal' => $data['daily_goal'] ?? 10,
            ':daily_done_count' => $data['daily_done_count'] ?? 0,
            ':subject_mastery_json' => $subjectMasteryJson,
            ':last_activity_at' => $data['last_activity_at'] ?? date('Y-m-d H:i:s')
        ]);
    }
}

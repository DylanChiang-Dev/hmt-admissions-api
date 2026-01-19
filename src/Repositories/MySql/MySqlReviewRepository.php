<?php

namespace App\Repositories\MySql;

use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Storage\Db;
use PDO;

class MySqlReviewRepository implements ReviewRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Db::getInstance();
    }

    public function getQueue(string $userId, ?string $examPath = null): array
    {
        $sql = "
            SELECT
                q.id, q.exam_path, q.track, q.subject, q.question_type,
                q.stem, q.options_json, q.difficulty, q.tags_json, q.knowledge_point_ids_json,
                rq.due_at, rq.interval_days, rq.ease_factor
            FROM review_queue rq
            JOIN questions q ON rq.question_id = q.id
            WHERE rq.user_id = :user_id
            AND rq.due_at <= NOW()
        ";

        $params = ['user_id' => $userId];

        if ($examPath) {
            $sql .= " AND q.exam_path = :exam_path";
            $params['exam_path'] = $examPath;
        }

        $sql .= " ORDER BY rq.due_at ASC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $items = array_map(function ($row) {
            return [
                'id' => $row['id'],
                'exam_path' => $row['exam_path'],
                'track' => $row['track'],
                'subject' => $row['subject'],
                'question_type' => $row['question_type'],
                'stem' => $row['stem'],
                'options' => json_decode($row['options_json'] ?? '[]', true),
                'difficulty' => (int)$row['difficulty'],
                'tags' => json_decode($row['tags_json'] ?? '[]', true),
                'knowledge_point_ids' => json_decode($row['knowledge_point_ids_json'] ?? '[]', true),
                'review_data' => [
                    'due_at' => $row['due_at'],
                    'interval_days' => $row['interval_days'],
                    'ease_factor' => $row['ease_factor']
                ]
            ];
        }, $rows);

        return [
            'items' => $items,
            'total_count' => count($items) // In a real app, might want a separate count query for total pending
        ];
    }

    public function completeReview(string $userId, array $items): array
    {
        // $items expected to be [['question_id' => '...', 'quality' => 0-5], ...]
        // Basic SM-2 Algorithm implementation or similar
        // For simplicity here, we'll assume a successful review increases interval.

        $stmtGet = $this->db->prepare("SELECT * FROM review_queue WHERE user_id = ? AND question_id = ?");
        $stmtUpdate = $this->db->prepare("
            UPDATE review_queue
            SET due_at = ?, interval_days = ?, ease_factor = ?
            WHERE user_id = ? AND question_id = ?
        ");

        foreach ($items as $item) {
            $questionId = $item['question_id'] ?? $item['id'] ?? null;
            // Default quality to 4 (good) if not provided
            $quality = $item['quality'] ?? 4;

            if (!$questionId) continue;

            $stmtGet->execute([$userId, $questionId]);
            $record = $stmtGet->fetch();

            if ($record) {
                $interval = (float)$record['interval_days'];
                $ease = (float)$record['ease_factor'];

                // Simple logic:
                // If quality < 3, reset interval.
                // If quality >= 3, apply ease factor.

                if ($quality < 3) {
                    $interval = 1;
                    // Ease factor can decrease slightly or stay same
                    $ease = max(1.3, $ease - 0.2);
                } else {
                    if ($interval == 0) $interval = 1;
                    else if ($interval == 1) $interval = 6;
                    else $interval = round($interval * $ease);

                    // Update ease
                    // EF' = EF + (0.1 - (5 - q) * (0.08 + (5 - q) * 0.02))
                    $ease = $ease + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
                    if ($ease < 1.3) $ease = 1.3;
                }

                $dueAt = date('Y-m-d H:i:s', strtotime("+$interval days"));

                $stmtUpdate->execute([$dueAt, $interval, $ease, $userId, $questionId]);
            }
        }

        // Return updated progress
        return $this->getUserProgress($userId);
    }

    private function getUserProgress(string $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM user_progress WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row) {
            // Return empty structure if no progress record exists yet
            return [
                "streak_current" => 0,
                "streak_best" => 0,
                "daily_goal" => 10,
                "daily_done_count" => 0,
                "subject_mastery" => [],
                "last_activity_at" => null
            ];
        }

        return [
            "streak_current" => (int)$row['streak_current'],
            "streak_best" => (int)$row['streak_best'],
            "daily_goal" => (int)$row['daily_goal'],
            "daily_done_count" => (int)$row['daily_done_count'],
            "subject_mastery" => json_decode($row['subject_mastery_json'] ?? '[]', true),
            "last_activity_at" => $row['last_activity_at']
        ];
    }
}

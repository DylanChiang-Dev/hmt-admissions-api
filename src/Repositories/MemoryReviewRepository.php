<?php

namespace App\Repositories;

use App\Repositories\Interfaces\ReviewRepositoryInterface;

class MemoryReviewRepository implements ReviewRepositoryInterface
{
    public function getQueue(string $userId): array
    {
        return [
            "items" => [
                [
                    "id" => "q-005",
                    "exam_path" => "master",
                    "subject" => "logic",
                    "question_type" => "single_choice",
                    "stem" => "以下推理中，哪個是有效的演繹推理？",
                    "options" => [
                        ["label" => "A", "content" => "所有人都會死，蘇格拉底是人，所以蘇格拉底會死"],
                        ["label" => "B", "content" => "太陽每天升起，所以明天太陽會升起"],
                        ["label" => "C", "content" => "大多數鳥會飛，企鵝是鳥，所以企鵝會飛"],
                        ["label" => "D", "content" => "我的車是紅色的，所以所有車都是紅色的"]
                    ],
                    "tags" => ["邏輯推理", "演繹"],
                    "knowledge_point_ids" => ["kp-logic-001"]
                ]
            ],
            "total_count" => 1
        ];
    }

    public function completeReview(string $userId, array $items): array
    {
        // In a real implementation, we would process the items and update progress.
        // Here we just return the hardcoded updated progress structure.
        return [
            "streak_current" => 5,
            "streak_best" => 12,
            "daily_goal" => 10,
            "daily_done_count" => 4,
            "subject_mastery" => [
                "culture" => 65,
                "english" => 70,
                "logic" => 55,
                "math" => 62
            ],
            "last_activity_at" => "2025-01-19T10:35:00Z"
        ];
    }
}

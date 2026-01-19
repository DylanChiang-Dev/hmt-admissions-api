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
                    "exam_path" => "undergrad_joint",
                    "track" => "science",
                    "subject" => "physics",
                    "question_type" => "single_choice",
                    "stem" => "一個質量為 2kg 的物體受到 10N 的力作用，求其加速度。",
                    "options" => [
                        ["label" => "A", "content" => "2 m/s²"],
                        ["label" => "B", "content" => "5 m/s²"],
                        ["label" => "C", "content" => "10 m/s²"],
                        ["label" => "D", "content" => "20 m/s²"]
                    ],
                    "difficulty" => 2,
                    "tags" => ["牛頓定律", "力學"],
                    "knowledge_point_ids" => ["kp-physics-001"]
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
                "math" => 65,
                "chinese" => 70,
                "english" => 55,
                "physics" => 62,
                "chemistry" => 50
            ],
            "last_activity_at" => "2025-01-19T10:35:00Z"
        ];
    }
}

<?php

namespace App\Repositories;

use App\Repositories\Interfaces\ProgressRepositoryInterface;

class MemoryProgressRepository implements ProgressRepositoryInterface
{
    public function getUserProgress(string $userId): array
    {
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

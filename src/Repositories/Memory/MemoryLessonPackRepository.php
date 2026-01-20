<?php

namespace App\Repositories\Memory;

use App\Repositories\Interfaces\LessonPackRepositoryInterface;

class MemoryLessonPackRepository implements LessonPackRepositoryInterface
{
    public function getToday(string $examPath, ?string $subject): array
    {
        // 內嵌示例數據
        $items = [
            [
                "id" => "q-001",
                "exam_path" => $examPath,
                "subject" => $subject ?? "culture",
                "question_type" => "single_choice",
                "stem" => "下列何者不是儒家經典「四書」之一？",
                "options" => [
                    ["label" => "A", "content" => "《大學》"],
                    ["label" => "B", "content" => "《中庸》"],
                    ["label" => "C", "content" => "《論語》"],
                    ["label" => "D", "content" => "《詩經》", "correct" => true]
                ],
                "tags" => ["儒學", "經典"],
                "knowledge_point_ids" => ["kp-001"]
            ],
            [
                "id" => "q-002",
                "exam_path" => $examPath,
                "subject" => $subject ?? "math",
                "question_type" => "single_choice",
                "stem" => "若 x² - 5x + 6 = 0，則 x 的值為何？",
                "options" => [
                    ["label" => "A", "content" => "x = 1 或 x = 6"],
                    ["label" => "B", "content" => "x = 2 或 x = 3", "correct" => true],
                    ["label" => "C", "content" => "x = -2 或 x = -3"],
                    ["label" => "D", "content" => "x = 0 或 x = 5"]
                ],
                "tags" => ["代數", "因式分解"],
                "knowledge_point_ids" => ["kp-002"]
            ],
            [
                "id" => "q-003",
                "exam_path" => $examPath,
                "subject" => $subject ?? "english",
                "question_type" => "single_choice",
                "stem" => "Choose the correct word: She _____ to the store yesterday.",
                "options" => [
                    ["label" => "A", "content" => "go"],
                    ["label" => "B", "content" => "goes"],
                    ["label" => "C", "content" => "went", "correct" => true],
                    ["label" => "D", "content" => "going"]
                ],
                "tags" => ["grammar", "past tense"],
                "knowledge_point_ids" => ["kp-003"]
            ]
        ];

        return [
            "id" => "lp-" . date("Ymd"),
            "date" => date("Y-m-d"),
            "exam_path" => $examPath,
            "subject" => $subject,
            "items" => $items,
            "goal_xp" => 30,
            "estimated_minutes" => 5
        ];
    }
}

<?php

namespace App\Repositories\Memory;

use App\Repositories\Interfaces\QuestionRepositoryInterface;

class MemoryQuestionRepository implements QuestionRepositoryInterface
{
    public function save(array $question): void
    {
        // No-op for memory mock
    }

    public function findByFilters(string $examPath, ?string $subject, int $limit): array
    {
        // Return mock questions
        $mockQuestions = [];
        for ($i = 0; $i < $limit; $i++) {
            $mockQuestions[] = [
                'id' => 'mock-q-' . $i,
                'exam_path' => $examPath,
                'subject' => $subject,
                'question_type' => 'single_choice',
                'stem' => 'Mock Question ' . ($i + 1),
                'options' => [
                    ['id' => 'A', 'text' => 'Option A', 'is_correct' => true],
                    ['id' => 'B', 'text' => 'Option B'],
                    ['id' => 'C', 'text' => 'Option C'],
                    ['id' => 'D', 'text' => 'Option D'],
                ],
                'tags' => ['mock'],
                'knowledge_point_ids' => []
            ];
        }
        return $mockQuestions;
    }

    public function findById(string $id): ?array
    {
        return [
            'id' => $id,
            'exam_path' => 'master',
            'subject' => 'culture',
            'question_type' => 'single_choice',
            'stem' => 'Mock Question ' . $id,
            'options' => [
                ['id' => 'A', 'text' => 'Option A', 'is_correct' => true],
                ['id' => 'B', 'text' => 'Option B'],
                ['id' => 'C', 'text' => 'Option C'],
                ['id' => 'D', 'text' => 'Option D'],
            ],
            'tags' => ['mock'],
            'knowledge_point_ids' => [],
            'answer_key_json' => 'A' // Consistent with options
        ];
    }
}

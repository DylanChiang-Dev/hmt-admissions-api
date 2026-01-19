<?php

namespace App\Repositories\Memory;

use App\Repositories\Interfaces\AttemptRepositoryInterface;

class MemoryAttemptRepository implements AttemptRepositoryInterface
{
    public function save(array $attemptData): array
    {
        // Go up from src/Repositories/Memory to root of hmt-admissions (parent of api)
        // src/Repositories/Memory -> 3 levels up to api root -> 1 level up to project root
        // Actually: __DIR__ is .../src/Repositories/Memory
        // dirname(__DIR__) -> .../src/Repositories
        // dirname(..., 2) -> .../src
        // dirname(..., 3) -> .../hmt-admissions-api
        // dirname(..., 4) -> .../hmt-admissions

        $baseDir = dirname(__DIR__, 4);
        $specPath = $baseDir . '/hmt-admissions-spec/examples/attempt.response.json';

        if (!file_exists($specPath)) {
            // Fallback for safety, though file should exist
            return [
                'correct' => false,
                'error' => 'Spec file not found'
            ];
        }

        $json = file_get_contents($specPath);
        $data = json_decode($json, true);

        // Bonus: Check if answer is "B" (case-insensitive)
        if (isset($attemptData['answer'])) {
            $isCorrect = strtoupper($attemptData['answer']) === 'B';
            $data['correct'] = $isCorrect;
        }

        return $data;
    }
}

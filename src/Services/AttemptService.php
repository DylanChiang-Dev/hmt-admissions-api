<?php

namespace App\Services;

use App\Repositories\Interfaces\AttemptRepositoryInterface;
use App\Repositories\Interfaces\ProgressRepositoryInterface;
use App\Repositories\Interfaces\QuestionRepositoryInterface;
use App\Exceptions\NotFoundException;

class AttemptService
{
    private AttemptRepositoryInterface $attemptRepo;
    private ProgressRepositoryInterface $progressRepo;
    private QuestionRepositoryInterface $questionRepo;

    public function __construct(
        AttemptRepositoryInterface $attemptRepo,
        ProgressRepositoryInterface $progressRepo,
        QuestionRepositoryInterface $questionRepo
    ) {
        $this->attemptRepo = $attemptRepo;
        $this->progressRepo = $progressRepo;
        $this->questionRepo = $questionRepo;
    }

    public function submitAttempt(string $userId, array $data): array
    {
        // 1. Fetch question
        $questionId = $data['question_id'] ?? null;
        if (!$questionId) {
            throw new \InvalidArgumentException("Question ID is required");
        }

        $question = $this->questionRepo->findById($questionId);
        if (!$question) {
            throw new NotFoundException("Question not found");
        }

        // 2. Check answer
        $userAnswer = $data['answer'] ?? null;
        $isCorrect = $this->checkAnswer($userAnswer, $question);

        // 3. Save Attempt
        // 將 answer 包裝為數組以確保是有效的 JSON
        $answerForJson = is_array($userAnswer) ? $userAnswer : [$userAnswer];
        
        $attemptData = [
            'user_id' => $userId,
            'question_id' => $questionId,
            'answer_json' => $answerForJson,
            'correct' => $isCorrect,
            'elapsed_ms' => $data['time_spent_ms'] ?? $data['elapsed_ms'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // If ID is provided (e.g. from client generated UUID), use it, otherwise repo handles it
        if (isset($data['id'])) {
            $attemptData['id'] = $data['id'];
        }

        $savedAttempt = $this->attemptRepo->save($attemptData);

        // 4. Update Progress
        $progress = $this->progressRepo->getUserProgress($userId);

        $progress['daily_done_count'] = ($progress['daily_done_count'] ?? 0) + 1;
        $progress['last_activity_at'] = date('Y-m-d H:i:s');

        $this->progressRepo->saveUserProgress($userId, $progress);

        // 5. 獲取正確答案用於返回
        $correctAnswer = $this->getCorrectAnswer($question);
        
        // 6. 計算 XP（正確 +10，錯誤 +0）
        $xpEarned = $isCorrect ? 10 : 0;

        // 返回 AttemptResult 格式
        return [
            'is_correct' => $isCorrect,
            'correct_answer' => $correctAnswer,
            'explanation' => $isCorrect ? '答對了！' : '正確答案是 ' . $correctAnswer,
            'xp_earned' => $xpEarned,
        ];
    }

    private function getCorrectAnswer(array $question): string
    {
        if (isset($question['options']) && is_array($question['options'])) {
            foreach ($question['options'] as $option) {
                $isCorrectOption = $option['correct'] ?? $option['is_correct'] ?? false;
                if ($isCorrectOption) {
                    return $option['label'] ?? $option['id'] ?? '';
                }
            }
        }
        return '';
    }

    private function checkAnswer($userAnswer, array $question): bool
    {
        // Logic to check answer.
        // Priority 1: Check 'answer_key_json' if it exists (decoded from JSON in Repo)
        if (isset($question['answer_key_json'])) {
            // If it's an array, compare. If string, compare.
            // Simple equality check for MVP
            return $userAnswer == $question['answer_key_json'];
        }

        // Priority 2: Check inside options if one is marked correct
        // Structure: [ {'label': 'A', 'content': '...', 'correct': true}, ... ]
        if (isset($question['options']) && is_array($question['options'])) {
            foreach ($question['options'] as $option) {
                // 檢查 'correct' 或 'is_correct' 字段
                $isCorrectOption = $option['correct'] ?? $option['is_correct'] ?? false;
                if ($isCorrectOption) {
                    // userAnswer 為選項 label (如 "A", "B", "C", "D")
                    $optionLabel = $option['label'] ?? $option['id'] ?? null;
                    if ($optionLabel && $userAnswer === $optionLabel) {
                        return true;
                    }
                }
            }
            // 有選項但用戶答案不正確
            return false;
        }

        // Fallback: Return false if we can't verify
        return false;
    }
}

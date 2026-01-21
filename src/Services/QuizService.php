<?php

namespace App\Services;

use App\Repositories\Interfaces\QuestionRepositoryInterface;
use App\Utils\Uuid;
use PDO;

class QuizService
{
    private PDO $db;
    private QuestionRepositoryInterface $questionRepo;

    public function __construct(PDO $db, QuestionRepositoryInterface $questionRepo)
    {
        $this->db = $db;
        $this->questionRepo = $questionRepo;
    }

    /**
     * 获取刷题列表
     * 
     * @param string $userId 用户ID
     * @param string $mode 模式: 5, 10, unlimited
     * @param string|null $examPath 考试路径
     * @param string|null $subject 科目
     * @return array
     */
    public function startQuiz(string $userId, string $mode, ?string $examPath = null, ?string $subject = null): array
    {
        $limit = match($mode) {
            '5' => 5,
            '10' => 10,
            'unlimited' => 50, // 无限模式一次取50题
            default => 10
        };

        // 1. 先获取需要复习的题目（记忆曲线）
        $reviewQuestions = $this->getReviewQuestions($userId, $examPath, min($limit, 3));
        
        // 2. 获取随机题目填充剩余数量
        $remaining = $limit - count($reviewQuestions);
        $randomQuestions = [];
        
        if ($remaining > 0) {
            $excludeIds = array_column($reviewQuestions, 'id');
            $randomQuestions = $this->getRandomQuestions($remaining, $examPath, $subject, $excludeIds);
        }

        // 3. 合并并打乱题目顺序
        $questions = array_merge($reviewQuestions, $randomQuestions);
        shuffle($questions);

        // 4. 打乱每道题的选项顺序
        foreach ($questions as &$question) {
            $question = $this->shuffleOptions($question);
        }

        return [
            'mode' => $mode,
            'total' => count($questions),
            'questions' => $questions
        ];
    }

    /**
     * 打乱选项顺序并重新分配 ABCD 标签
     * 保留 correct 标记以便判断正确答案
     */
    private function shuffleOptions(array $question): array
    {
        if (!isset($question['options']) || !is_array($question['options'])) {
            return $question;
        }

        $options = $question['options'];
        
        // 打乱选项顺序
        shuffle($options);
        
        // 重新分配 ABCD 标签
        $labels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($options as $index => &$option) {
            $option['label'] = $labels[$index] ?? chr(65 + $index);
        }
        
        $question['options'] = $options;
        return $question;
    }


    /**
     * 提交答案
     */
    public function submitAnswer(string $userId, string $questionId, string $answer, int $elapsedMs): array
    {
        // 获取题目
        $question = $this->questionRepo->findById($questionId);
        if (!$question) {
            throw new \Exception('题目不存在');
        }

        // 判断是否正确 - findById 返回的已经是解码后的数据，键名是 options
        $options = $question['options'] ?? [];
        
        $isCorrect = false;
        $correctAnswer = '';
        foreach ($options as $opt) {
            if ($opt['correct'] === true) {
                $correctAnswer = $opt['label'];
                if ($opt['label'] === $answer) {
                    $isCorrect = true;
                }
                break;
            }
        }

        // 记录答题
        $answerId = Uuid::generate();
        $stmt = $this->db->prepare("
            INSERT INTO user_answers (id, user_id, question_id, selected_answer, is_correct, elapsed_ms)
            VALUES (:id, :user_id, :question_id, :answer, :is_correct, :elapsed_ms)
        ");
        $stmt->execute([
            'id' => $answerId,
            'user_id' => $userId,
            'question_id' => $questionId,
            'answer' => $answer,
            'is_correct' => $isCorrect ? 1 : 0,
            'elapsed_ms' => $elapsedMs
        ]);

        // 更新错题库和记忆曲线
        if ($isCorrect) {
            $this->handleCorrectAnswer($userId, $questionId);
        } else {
            $this->handleWrongAnswer($userId, $questionId);
        }

        return [
            'is_correct' => $isCorrect,
            'correct_answer' => $correctAnswer,
            'selected_answer' => $answer,
            'question_id' => $questionId
        ];
    }

    /**
     * 获取错题列表
     */
    public function getWrongQuestions(string $userId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        
        // 获取总数
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM wrong_questions WHERE user_id = :user_id");
        $countStmt->execute(['user_id' => $userId]);
        $total = (int)$countStmt->fetchColumn();

        // 获取错题列表（关联题目信息）
        $stmt = $this->db->prepare("
            SELECT q.*, wq.wrong_count, wq.correct_streak, wq.added_at, wq.last_attempt_at
            FROM wrong_questions wq
            JOIN questions q ON wq.question_id = q.id
            WHERE wq.user_id = :user_id
            ORDER BY wq.added_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $items = $stmt->fetchAll();
        
        // 处理 JSON 字段
        foreach ($items as &$item) {
            $item['options'] = is_string($item['options_json']) 
                ? json_decode($item['options_json'], true) 
                : $item['options_json'];
            unset($item['options_json']);
            $item['tags'] = is_string($item['tags_json']) 
                ? json_decode($item['tags_json'], true) 
                : ($item['tags_json'] ?? []);
            unset($item['tags_json']);
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * 获取错题复习
     */
    public function getWrongQuestionsQuiz(string $userId, int $count = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT q.*
            FROM wrong_questions wq
            JOIN questions q ON wq.question_id = q.id
            WHERE wq.user_id = :user_id
            ORDER BY RAND()
            LIMIT :count
        ");
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('count', $count, PDO::PARAM_INT);
        $stmt->execute();
        
        $items = $stmt->fetchAll();
        
        foreach ($items as &$item) {
            $item['options'] = is_string($item['options_json']) 
                ? json_decode($item['options_json'], true) 
                : $item['options_json'];
            unset($item['options_json']);
            $item['tags'] = is_string($item['tags_json']) 
                ? json_decode($item['tags_json'], true) 
                : ($item['tags_json'] ?? []);
            unset($item['tags_json']);
        }

        return [
            'mode' => 'wrong_review',
            'total' => count($items),
            'questions' => $items
        ];
    }

    // ============ Private Methods ============

    /**
     * 获取需要复习的题目（基于记忆曲线）
     */
    private function getReviewQuestions(string $userId, ?string $examPath, int $limit): array
    {
        $sql = "
            SELECT q.*
            FROM review_queue rq
            JOIN questions q ON rq.question_id = q.id
            WHERE rq.user_id = :user_id 
              AND rq.due_at <= NOW()
        ";
        if ($examPath) {
            $sql .= " AND q.exam_path = :exam_path";
        }
        $sql .= " ORDER BY rq.due_at ASC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('user_id', $userId);
        if ($examPath) {
            $stmt->bindValue('exam_path', $examPath);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();
        
        foreach ($items as &$item) {
            $item['options'] = is_string($item['options_json']) 
                ? json_decode($item['options_json'], true) 
                : $item['options_json'];
            unset($item['options_json']);
            $item['tags'] = is_string($item['tags_json']) 
                ? json_decode($item['tags_json'], true) 
                : ($item['tags_json'] ?? []);
            unset($item['tags_json']);
            $item['is_review'] = true;
        }

        return $items;
    }

    /**
     * 获取随机题目
     */
    private function getRandomQuestions(int $limit, ?string $examPath, ?string $subject, array $excludeIds = []): array
    {
        $sql = "SELECT * FROM questions WHERE is_active = 1";
        $params = [];

        if ($examPath) {
            $sql .= " AND exam_path = :exam_path";
            $params['exam_path'] = $examPath;
        }
        if ($subject) {
            $sql .= " AND subject = :subject";
            $params['subject'] = $subject;
        }
        if (!empty($excludeIds)) {
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $sql .= " AND id NOT IN ($placeholders)";
        }
        
        $sql .= " ORDER BY RAND() LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $paramIndex = 1;
        foreach ($excludeIds as $id) {
            $stmt->bindValue($paramIndex++, $id);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['options'] = is_string($item['options_json']) 
                ? json_decode($item['options_json'], true) 
                : $item['options_json'];
            unset($item['options_json']);
            $item['tags'] = is_string($item['tags_json']) 
                ? json_decode($item['tags_json'], true) 
                : ($item['tags_json'] ?? []);
            unset($item['tags_json']);
        }

        return $items;
    }

    /**
     * 处理答对：更新记忆曲线，可能移除错题
     */
    private function handleCorrectAnswer(string $userId, string $questionId): void
    {
        // 更新错题库的 correct_streak
        $stmt = $this->db->prepare("
            UPDATE wrong_questions 
            SET correct_streak = correct_streak + 1, last_attempt_at = NOW()
            WHERE user_id = :user_id AND question_id = :question_id
        ");
        $stmt->execute(['user_id' => $userId, 'question_id' => $questionId]);

        // 如果连续答对2次，从错题库移除
        $stmt = $this->db->prepare("
            DELETE FROM wrong_questions 
            WHERE user_id = :user_id AND question_id = :question_id AND correct_streak >= 2
        ");
        $stmt->execute(['user_id' => $userId, 'question_id' => $questionId]);

        // 更新记忆曲线（SM-2算法简化版）
        $stmt = $this->db->prepare("SELECT * FROM review_queue WHERE user_id = :user_id AND question_id = :question_id");
        $stmt->execute(['user_id' => $userId, 'question_id' => $questionId]);
        $review = $stmt->fetch();

        if ($review) {
            $interval = $review['interval_days'] * $review['ease_factor'];
            $easeFactor = min(2.5, $review['ease_factor'] + 0.1);
            $nextReview = date('Y-m-d H:i:s', strtotime("+{$interval} days"));
            
            $stmt = $this->db->prepare("
                UPDATE review_queue 
                SET interval_days = :interval, ease_factor = :ease, due_at = :due, review_count = review_count + 1
                WHERE user_id = :user_id AND question_id = :question_id
            ");
            $stmt->execute([
                'interval' => $interval,
                'ease' => $easeFactor,
                'due' => $nextReview,
                'user_id' => $userId,
                'question_id' => $questionId
            ]);
        }
    }

    /**
     * 处理答错：加入错题库，重置记忆曲线
     */
    private function handleWrongAnswer(string $userId, string $questionId): void
    {
        // 加入或更新错题库
        $stmt = $this->db->prepare("
            INSERT INTO wrong_questions (user_id, question_id, wrong_count, correct_streak, added_at, last_attempt_at)
            VALUES (:user_id, :question_id, 1, 0, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                wrong_count = wrong_count + 1, 
                correct_streak = 0,
                last_attempt_at = NOW()
        ");
        $stmt->execute(['user_id' => $userId, 'question_id' => $questionId]);

        // 加入或重置记忆曲线
        $stmt = $this->db->prepare("
            INSERT INTO review_queue (user_id, question_id, due_at, interval_days, ease_factor)
            VALUES (:user_id, :question_id, DATE_ADD(NOW(), INTERVAL 1 DAY), 1, 2.5)
            ON DUPLICATE KEY UPDATE 
                interval_days = 1,
                ease_factor = GREATEST(1.3, ease_factor - 0.2),
                due_at = DATE_ADD(NOW(), INTERVAL 1 DAY)
        ");
        $stmt->execute(['user_id' => $userId, 'question_id' => $questionId]);
    }
}

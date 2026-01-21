<?php

namespace App\Controllers;

use App\Request;
use App\Response;
use App\Services\QuizService;

class QuizController
{
    private QuizService $quizService;

    public function __construct(QuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    /**
     * 开始刷题
     * GET /v1/quiz/start?mode=5|10|unlimited&exam_path=master&subject=culture
     */
    public function start(Request $request): Response
    {
        $userId = $request->getAttribute('user_id');
        $params = $request->getQueryParams();
        
        $mode = $params['mode'] ?? '10';
        $examPath = $params['exam_path'] ?? null;
        $subject = $params['subject'] ?? null;

        $result = $this->quizService->startQuiz($userId, $mode, $examPath, $subject);

        return Response::json($result);
    }

    /**
     * 提交答案
     * POST /v1/quiz/answer
     * { "question_id": "xxx", "answer": "B", "elapsed_ms": 5000 }
     */
    public function answer(Request $request): Response
    {
        $userId = $request->getAttribute('user_id');
        $params = $request->getParams();

        $questionId = $params['question_id'] ?? '';
        $answer = $params['answer'] ?? '';
        $elapsedMs = (int)($params['elapsed_ms'] ?? 0);

        if (empty($questionId) || empty($answer)) {
            return Response::json([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => '缺少必要参数'
                ]
            ], 400);
        }

        try {
            $result = $this->quizService->submitAnswer($userId, $questionId, $answer, $elapsedMs);
            return Response::json($result);
        } catch (\Exception $e) {
            return Response::json([
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * 获取错题列表
     * GET /v1/wrong-questions?page=1&limit=20
     */
    public function getWrongQuestions(Request $request): Response
    {
        $userId = $request->getAttribute('user_id');
        $params = $request->getQueryParams();
        
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);

        $result = $this->quizService->getWrongQuestions($userId, $page, $limit);

        return Response::json($result);
    }

    /**
     * 错题复习
     * GET /v1/wrong-questions/quiz?count=10
     */
    public function wrongQuestionsQuiz(Request $request): Response
    {
        $userId = $request->getAttribute('user_id');
        $params = $request->getQueryParams();
        
        $count = (int)($params['count'] ?? 10);

        $result = $this->quizService->getWrongQuestionsQuiz($userId, $count);

        return Response::json($result);
    }
}

<?php

namespace App\Controllers;

use App\Request;
use App\Response;
use App\Services\AttemptService;
use App\Exceptions\ValidationException;

class AttemptsController
{
    private AttemptService $service;

    public function __construct(AttemptService $service)
    {
        $this->service = $service;
    }

    public function submit(Request $req): Response
    {
        $params = $req->getParams();

        // Validation
        $errors = [];
        if (empty($params['question_id'])) {
            $errors['question_id'] = 'Question ID is required';
        }
        if (empty($params['answer'])) {
            $errors['answer'] = 'Answer is required';
        }
        if (isset($params['elapsed_ms']) && !is_numeric($params['elapsed_ms'])) {
            $errors['elapsed_ms'] = 'Elapsed time must be a number';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // Get user_id from the authenticated request
        $userId = $req->getAttribute('user_id');

        $result = $this->service->submitAttempt($userId, $params);

        return Response::json($result);
    }
}

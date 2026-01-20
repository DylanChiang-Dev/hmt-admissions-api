<?php

namespace App\Controllers;

use App\Request;
use App\Response;
use App\Services\LessonPackService;
use App\Utils\Validator;

class LessonPackController
{
    private LessonPackService $service;

    public function __construct(LessonPackService $service)
    {
        $this->service = $service;
    }

    public function getToday(Request $req): Response
    {
        $params = $req->getQueryParams();

        // Valid exam paths (currently only master)
        $validExamPaths = 'master';

        Validator::validate($params, [
            'exam_path' => "required|in:$validExamPaths"
        ]);

        $examPath = $params['exam_path'];
        $subject = $params['subject'] ?? null;

        $data = $this->service->getToday($examPath, $subject);

        return Response::json($data);
    }
}

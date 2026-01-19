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

        // Define valid exam paths for validation (example list)
        $validExamPaths = 'undergrad_joint,master_joint';

        Validator::validate($params, [
            'exam_path' => "required|in:$validExamPaths"
        ]);

        $examPath = $params['exam_path'];
        $track = $params['track'] ?? null;
        $subject = $params['subject'] ?? null;

        $data = $this->service->getToday($examPath, $track, $subject);

        return Response::json($data);
    }
}

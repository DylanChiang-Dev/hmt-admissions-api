<?php

namespace App\Controllers;

use App\Services\ProgressService;
use App\Request;
use App\Response;

class ProgressController
{
    private ProgressService $service;

    public function __construct(ProgressService $service)
    {
        $this->service = $service;
    }

    public function get(Request $req): Response
    {
        // Extracted from AuthMiddleware
        $userId = $req->getAttribute('user_id');

        $data = $this->service->getUserProgress($userId);
        return Response::json($data);
    }
}

<?php

namespace App\Controllers;

use App\Services\ReviewService;
use App\Request;
use App\Response;
use App\Exceptions\ValidationException;

class ReviewController
{
    private ReviewService $service;

    public function __construct(ReviewService $service)
    {
        $this->service = $service;
    }

    public function getQueue(Request $req): Response
    {
        $userId = $req->getAttribute('user_id');

        $data = $this->service->getQueue($userId);
        return Response::json($data);
    }

    public function complete(Request $req): Response
    {
        $userId = $req->getAttribute('user_id');
        $body = $req->getBody();

        if (!isset($body['items']) || !is_array($body['items'])) {
            throw new ValidationException("Missing or invalid 'items' field");
        }

        $data = $this->service->completeReview($userId, $body['items']);
        return Response::json($data);
    }
}

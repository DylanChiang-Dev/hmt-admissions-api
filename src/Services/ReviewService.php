<?php

namespace App\Services;

use App\Repositories\Interfaces\ReviewRepositoryInterface;

class ReviewService
{
    private ReviewRepositoryInterface $reviewRepo;

    public function __construct(ReviewRepositoryInterface $reviewRepo)
    {
        $this->reviewRepo = $reviewRepo;
    }

    public function getQueue(string $userId): array
    {
        return $this->reviewRepo->getQueue($userId);
    }

    public function completeReview(string $userId, array $items): array
    {
        // Logic to validate items could go here
        $updatedProgress = $this->reviewRepo->completeReview($userId, $items);
        return ['updated_progress' => $updatedProgress];
    }
}

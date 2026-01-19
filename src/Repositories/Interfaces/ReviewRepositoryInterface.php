<?php

namespace App\Repositories\Interfaces;

interface ReviewRepositoryInterface
{
    public function getQueue(string $userId): array;
    public function completeReview(string $userId, array $items): array;
}

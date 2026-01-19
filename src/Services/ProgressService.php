<?php

namespace App\Services;

use App\Repositories\Interfaces\ProgressRepositoryInterface;

class ProgressService
{
    private ProgressRepositoryInterface $progressRepo;

    public function __construct(ProgressRepositoryInterface $progressRepo)
    {
        $this->progressRepo = $progressRepo;
    }

    public function getUserProgress(string $userId): array
    {
        return $this->progressRepo->getUserProgress($userId);
    }
}

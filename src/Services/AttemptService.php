<?php

namespace App\Services;

use App\Repositories\Interfaces\AttemptRepositoryInterface;

class AttemptService
{
    private AttemptRepositoryInterface $repository;

    public function __construct(AttemptRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function submitAttempt(string $userId, array $data): array
    {
        // We can inject the user_id into the data if the repo needs it
        $data['user_id'] = $userId;
        return $this->repository->save($data);
    }
}

<?php

namespace App\Services;

use App\Repositories\Interfaces\LessonPackRepositoryInterface;

class LessonPackService
{
    private LessonPackRepositoryInterface $repository;

    public function __construct(LessonPackRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getToday(string $examPath, ?string $subject): array
    {
        return $this->repository->getToday($examPath, $subject);
    }
}

<?php

namespace App\Repositories\Interfaces;

interface ProgressRepositoryInterface
{
    public function getUserProgress(string $userId): array;

    public function saveUserProgress(string $userId, array $data): void;
}

<?php

namespace App\Repositories\Interfaces;

interface ProgressRepositoryInterface
{
    public function getUserProgress(string $userId): array;
}

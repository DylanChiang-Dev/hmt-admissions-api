<?php

namespace App\Repositories\Interfaces;

interface AttemptRepositoryInterface
{
    public function save(array $attemptData): array;
}

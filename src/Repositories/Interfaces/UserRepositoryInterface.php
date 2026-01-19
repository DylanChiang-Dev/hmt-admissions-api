<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function save(array $user): void;
    public function findByEmail(string $email): ?array;
    public function findById(string $id): ?array;
}

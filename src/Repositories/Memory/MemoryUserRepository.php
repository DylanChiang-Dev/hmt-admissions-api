<?php

namespace App\Repositories\Memory;

use App\Repositories\Interfaces\UserRepositoryInterface;

class MemoryUserRepository implements UserRepositoryInterface
{
    private static array $users = [];

    public function save(array $user): void
    {
        self::$users[$user['id']] = $user;
    }

    public function findByEmail(string $email): ?array
    {
        foreach (self::$users as $user) {
            if (isset($user['email']) && $user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    public function findById(string $id): ?array
    {
        return self::$users[$id] ?? null;
    }
}

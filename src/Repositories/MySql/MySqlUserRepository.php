<?php

namespace App\Repositories\MySql;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Storage\Db;
use PDO;

class MySqlUserRepository implements UserRepositoryInterface
{
    public function save(array $user): void
    {
        $db = Db::getInstance();
        $stmt = $db->prepare('INSERT INTO users (id, email, created_at) VALUES (:id, :email, :created_at)');
        $stmt->execute([
            ':id' => $user['id'],
            ':email' => $user['email'] ?? null,
            ':created_at' => $user['created_at'] ?? date('Y-m-d H:i:s')
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $db = Db::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function findById(string $id): ?array
    {
        $db = Db::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }
}

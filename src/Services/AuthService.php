<?php

namespace App\Services;

use App\Utils\Jwt;
use App\Utils\Uuid;
use App\Config;
use App\Repositories\Interfaces\UserRepositoryInterface;

class AuthService
{
    private string $secret;
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->secret = Config::get('JWT_SECRET');
        $this->userRepository = $userRepository;
    }

    public function anonymous(): array
    {
        $userId = Uuid::generate();
        $now = time();
        $expiresIn = 30 * 24 * 60 * 60; // 30 days

        $this->userRepository->save([
            'id' => $userId,
            'email' => null,
            'created_at' => date('Y-m-d H:i:s', $now)
        ]);

        $payload = [
            'user_id' => $userId,
            'role' => 'anonymous',
            'iat' => $now,
            'exp' => $now + $expiresIn
        ];

        return [
            'access_token' => Jwt::encode($payload, $this->secret),
            'expires_in' => $expiresIn,
            'user_id' => $userId
        ];
    }

    public function login(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);
        $now = time();

        if (!$user) {
            $userId = Uuid::generate();
            $this->userRepository->save([
                'id' => $userId,
                'email' => $email,
                'created_at' => date('Y-m-d H:i:s', $now)
            ]);
        } else {
            $userId = $user['id'];
        }

        $expiresIn = 24 * 60 * 60; // 1 day

        $payload = [
            'user_id' => $userId,
            'email' => $email,
            'role' => 'user',
            'iat' => $now,
            'exp' => $now + $expiresIn
        ];

        return [
            'access_token' => Jwt::encode($payload, $this->secret),
            'expires_in' => $expiresIn,
            'user_id' => $userId
        ];
    }
}

<?php

namespace App\Services;

use App\Utils\Jwt;
use App\Utils\Uuid;
use App\Config;

class AuthService
{
    private string $secret;

    public function __construct()
    {
        $this->secret = Config::get('JWT_SECRET');
    }

    public function anonymous(): array
    {
        $userId = Uuid::generate();
        $now = time();
        $expiresIn = 30 * 24 * 60 * 60; // 30 days

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
        // Mock implementation
        // Use a stable fake ID based on email so it's consistent for testing
        $userId = md5($email);
        $now = time();
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

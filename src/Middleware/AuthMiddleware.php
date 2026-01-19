<?php

namespace App\Middleware;

use App\Request;
use App\Config;
use App\Utils\Jwt;
use App\Exceptions\AuthException;

class AuthMiddleware
{
    public function handle(Request $req, callable $next)
    {
        $authHeader = $req->getHeader('Authorization');

        if (!$authHeader || !preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            throw new AuthException('Missing or invalid token format', 'AUTH_INVALID_TOKEN');
        }

        $token = $matches[1];
        $secret = Config::get('JWT_SECRET');

        // This will throw AuthException if invalid or expired
        $payload = Jwt::decode($token, $secret);

        // Attach user info to request
        $req->setAttribute('user_id', $payload['user_id']);
        if (isset($payload['email'])) {
            $req->setAttribute('email', $payload['email']);
        }
        if (isset($payload['role'])) {
            $req->setAttribute('role', $payload['role']);
        }

        return $next($req);
    }
}

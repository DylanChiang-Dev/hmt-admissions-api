<?php
namespace App\Middleware;

use App\Utils\Jwt;
use App\Config;
use App\Request;
use App\Response;
use Exception;

class AuthMiddleware {
    /**
     * Handle authentication and call next handler
     */
    public function handle(Request $request, callable $next) {
        $authHeader = $request->getHeader('Authorization') ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return Response::json([
                'error' => ['code' => 'AUTH_MISSING_TOKEN', 'message' => '需要登入']
            ], 401);
        }

        $token = $matches[1];
        $secret = Config::get('JWT_SECRET');

        try {
            $decoded = Jwt::decode($token, $secret);
            
            // 将用户信息添加到请求中
            $request->setAttribute('user_id', $decoded['user_id'] ?? null);
            $request->setAttribute('email', $decoded['email'] ?? null);
            $request->setAttribute('role', $decoded['role'] ?? 'user');
            
            // 调用下一个处理器
            return $next($request);
        } catch (Exception $e) {
            return Response::json([
                'error' => ['code' => 'AUTH_INVALID_TOKEN', 'message' => 'Token 無效或已過期']
            ], 401);
        }
    }
}


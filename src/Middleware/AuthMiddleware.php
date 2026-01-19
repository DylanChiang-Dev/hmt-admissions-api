<?php
namespace HmtAdmissions\Api\Middleware;

use HmtAdmissions\Api\Utils\Jwt;
use Exception;

class AuthMiddleware {
    public function handle() {
        if (!function_exists('getallheaders')) {
            function getallheaders() {
                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
                return $headers;
            }
        }

        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Missing token']);
            exit;
        }

        $token = $matches[1];

        try {
            $decoded = Jwt::decode($token);
            return (array) $decoded; // Return payload (user_id, etc)
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Invalid token']);
            exit;
        }
    }
}

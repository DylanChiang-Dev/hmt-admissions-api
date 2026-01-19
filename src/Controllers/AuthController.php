<?php
namespace HmtAdmissions\Api\Controllers;

use HmtAdmissions\Api\Core\Database;
use PDO;

class AuthController {
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            http_response_code(400);
            return ['error' => 'Missing email or password'];
        }

        $pdo = Database::getConnection();

        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            return ['error' => 'Email already exists'];
        }

        // Insert
        $id = uniqid(); // Use UUID in production
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (id, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$id, $email, $hash]);

        return [
            'access_token' => 'fake-jwt-token-' . $id,
            'user_id' => $id,
            'expires_in' => 3600
        ];
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            return ['error' => 'Invalid credentials'];
        }

        $payload = [
            'user_id' => $user['id'],
            'exp' => time() + (86400 * 30) // 30 days
        ];

        // Use full namespace for Jwt if not imported
        $token = \HmtAdmissions\Api\Utils\Jwt::encode($payload);

        return [
            'access_token' => $token,
            'user_id' => $user['id'],
            'expires_in' => 86400 * 30
        ];
    }
}

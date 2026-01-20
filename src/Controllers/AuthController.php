<?php

namespace App\Controllers;

use App\Request;
use App\Response;
use App\Services\AuthService;
use App\Exceptions\ValidationException;
use App\Bootstrap;
use App\Config;
use App\Repositories\Memory\MemoryUserRepository;
use App\Repositories\MySql\MySqlUserRepository;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        // 根據 REPO_TYPE 選擇 Repository
        $repoType = Config::get('REPO_TYPE', 'memory');
        
        if ($repoType === 'mysql') {
            $userRepository = new MySqlUserRepository();
        } else {
            $userRepository = new MemoryUserRepository();
        }
        
        $this->authService = new AuthService($userRepository);
    }

    public function anonymous(Request $req): Response
    {
        $result = $this->authService->anonymous();
        return Response::json($result);
    }

    public function login(Request $req): Response
    {
        $params = $req->getParams();

        if (empty($params['email'])) {
            throw new ValidationException(['email' => 'Email is required']);
        }

        $result = $this->authService->login($params['email']);
        return Response::json($result);
    }

    public function register(Request $req): Response
    {
        $params = $req->getParams();

        if (empty($params['email'])) {
            throw new ValidationException(['email' => 'Email is required']);
        }
        if (empty($params['password'])) {
            throw new ValidationException(['password' => 'Password is required']);
        }

        // 簡化實現：直接使用 login 流程（因為 MVP 階段沒有真正的用戶存儲）
        $result = $this->authService->login($params['email']);
        return Response::json($result, 201);
    }
}

<?php

namespace App\Controllers;

use App\Request;
use App\Response;
use App\Services\AuthService;
use App\Exceptions\ValidationException;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
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
}

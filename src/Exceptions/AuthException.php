<?php

namespace App\Exceptions;

class AuthException extends AppException
{
    public function __construct(string $message = 'Unauthorized', string $errorCode = 'AUTH_ERROR')
    {
        parent::__construct($errorCode, $message, [], 401);
    }
}

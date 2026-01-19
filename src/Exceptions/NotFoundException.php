<?php

namespace App\Exceptions;

class NotFoundException extends AppException
{
    public function __construct(string $message = 'Not Found', string $errorCode = 'NOT_FOUND')
    {
        parent::__construct($errorCode, $message, [], 404);
    }
}

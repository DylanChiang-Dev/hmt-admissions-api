<?php

namespace App\Exceptions;

class ValidationException extends AppException
{
    public function __construct(string $message, array $details = [])
    {
        parent::__construct('VALIDATION_ERROR', $message, $details, 400);
    }
}

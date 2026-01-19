<?php

namespace App\Exceptions;

use Exception;

class AppException extends Exception
{
    protected string $errorCode;
    protected array $details;
    protected int $httpStatus;

    public function __construct(string $errorCode, string $message, array $details = [], int $httpStatus = 400)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->details = $details;
        $this->httpStatus = $httpStatus;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}

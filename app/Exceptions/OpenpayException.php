<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class OpenpayException extends Exception
{
    public function __construct(
        string $message,
        private readonly ?string $errorCode = null,
        private readonly ?string $category = null,
        private readonly ?int $httpStatus = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function configuration(string $message): self
    {
        return new self($message, null, 'configuration');
    }

    public static function connection(string $message): self
    {
        return new self($message, null, 'connection');
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }
}

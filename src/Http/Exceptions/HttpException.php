<?php

namespace PikaJew002\Handrolled\Http\Exceptions;

use Exception, Throwable;

class HttpException extends Exception
{
    public int $httpCode;
    public string $errorMessage;

    public function __construct(int $code, string $message, ?Throwable $e = null)
    {
        $this->httpCode = $code;
        $this->errorMessage = $message;

        parent::__construct("Http $code: $message", $e);
    }
}

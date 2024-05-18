<?php

namespace PikaJew002\Handrolled\Http\Exceptions;

use Exception, Throwable;

class HttpException extends Exception
{
    public function __construct(int $code = 500, ?string $message = null, ?Throwable $exception = null)
    {
        parent::__construct($message ?? "HTTP {$code} Error", $code, $exception);
    }
}

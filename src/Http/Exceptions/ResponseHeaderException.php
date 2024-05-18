<?php
namespace PikaJew002\Handrolled\Http\Exceptions;

use Exception, Throwable;

class ResponseHeaderException extends Exception
{
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message ?? 'Response Header Error', $code, $previous);
    }
}
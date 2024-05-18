<?php
namespace PikaJew002\Handrolled\Auth;

use Exception;
use Throwable;

class EncryptionException extends Exception
{
    public function __construct(string $message, int $code = 0,  ?Throwable $exception = null)
    {
        parent::__construct($message, $code, $exception);
    }
}
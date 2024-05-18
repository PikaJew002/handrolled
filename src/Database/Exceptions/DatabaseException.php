<?php

namespace PikaJew002\Handrolled\Database\Exceptions;

use Exception, Throwable;

class DatabaseException extends Exception
{
    public function __construct(string $message, ?Throwable $e = null)
    {
        parent::__construct("Database Exception: {$message}", 0, $e);
    }
}

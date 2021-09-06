<?php

namespace PikaJew002\Handrolled\Database\Orm\Exceptions;

use Exception, Throwable;

class OrmException extends Exception
{
    public function __construct(string $message, ?Throwable $e = null)
    {
        parent::__construct("Orm Exception: {$message}", $e);
    }
}

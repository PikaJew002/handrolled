<?php

namespace PikaJew002\Handrolled\Database\Exceptions;

use Exception, Throwable;

class DatabaseDriverException extends Exception
{
    public function __construct(string $driver, ?Throwable $e = null)
    {
        parent::__construct("Database driver `{$driver}` is not supported", 0, $e);
    }
}

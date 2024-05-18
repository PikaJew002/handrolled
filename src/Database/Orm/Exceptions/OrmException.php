<?php

namespace PikaJew002\Handrolled\Database\Orm\Exceptions;

use PikaJew002\Handrolled\Database\Exceptions\DatabaseException;
use Throwable;

class OrmException extends DatabaseException
{
    public function __construct(string $message, ?Throwable $e = null)
    {
        parent::__construct("Orm Exception: {$message}", $e);
    }
}

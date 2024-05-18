<?php

namespace PikaJew002\Handrolled\Database\Exceptions;

use Exception, Throwable;

class InvalidComparisonOperatorException extends Exception
{
    public function __construct(string $operator, ?Throwable $e = null)
    {
        parent::__construct("`{$operator}` is not a valid comparison operator", 0, $e);
    }
}

<?php

namespace PikaJew002\Handrolled\Database\Exceptions;

use Exception, Throwable;

class InvalidSortExpressionException extends Exception
{
    public function __construct(string $expression, ?Throwable $e = null)
    {
        parent::__construct("`{$expression}` is not a valid ORDER BY sort expression", 0, $e);
    }
}

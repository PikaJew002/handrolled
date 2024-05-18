<?php

namespace PikaJew002\Handrolled\Database\Orm\Exceptions;

use BadMethodCallException;
use Throwable;
use PikaJew002\Handrolled\Database\Orm\QueryBuilder;

class BadQueryBuilderMethodCallException extends BadMethodCallException
{
    public function __construct(string $method, ?Throwable $e = null)
    {
        $class = QueryBuilder::class;
        parent::__construct("Method `{$method}` not found on `{$class}` class", 0, $e);
    }
}

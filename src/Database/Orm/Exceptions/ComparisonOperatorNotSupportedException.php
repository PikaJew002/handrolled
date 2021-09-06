<?php

namespace PikaJew002\Handrolled\Database\Orm\Exceptions;

use Throwable;

class ComparisonOperatorNotSupportedException extends OrmException
{
    public function __construct(string $operator, ?Throwable $e = null)
    {
        parent::__construct("Comparison Operator `{$operator}` not supported", $e);
    }
}

<?php

namespace PikaJew002\Handrolled\Database\Orm\Exceptions;

use Throwable;

class ModelPropertyNotFoundException extends OrmException
{
    public function __construct(string $columnName, string $modelName, ?Throwable $e = null)
    {
        parent::__construct("Property `{$columnName}` not found on model {$className}", $e);
    }
}

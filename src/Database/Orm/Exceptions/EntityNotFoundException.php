<?php

namespace PikaJew002\Handrolled\Database\Orm\Exceptions;

use Throwable;

class EntityNotFoundException extends OrmException
{
    public function __construct(string $tableName, string $primaryKey, $primaryKeyValue, ?Throwable $e = null)
    {
        parent::__construct("Entity not found in database table `{$tableName}` where `{$primaryKey}` = '{$primaryKeyValue}'. This could be because the entity is not yet persisted.", $e);
    }
}

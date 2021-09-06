<?php

namespace PikaJew002\Handrolled\Database\Orm\Exceptions;

use Throwable;

class DatabaseConnectionException extends OrmException
{
  public function __construct(string $modelName, ?Throwable $e = null)
  {
      parent::__construct("Model `{$modelName}` is missing required property \$tableName", $e);
  }
}

<?php

namespace PikaJew002\Handrolled\Database\Orm\Exceptions;

use Throwable;

class DatabaseDriverException extends OrmException
{
  public function __construct(string $driver, ?Throwable $e = null)
  {
      parent::__construct("Database driver `{$driver}` is not supported", $e);
  }
}

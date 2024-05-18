<?php

namespace PikaJew002\Handrolled\Application\Exceptions;

use Exception;
use Throwable;

class PathDefinitionException extends Exception
{
    public function __construct(string $message, ?Throwable $e = null)
    {
        parent::__construct("Invalid path definition: {$message}", 0, $e);
    }
}

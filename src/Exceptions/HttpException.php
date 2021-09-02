<?php

namespace PikaJew002\Handrolled\Exceptions;

use Exception;

class HttpException extends Exception
{
    public $code;
    public $message;

    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;

        parent::__construct("HTTP {$this->code}: {$this->message}");
    }
}

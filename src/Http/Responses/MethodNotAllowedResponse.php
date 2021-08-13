<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class MethodNotAllowedResponse extends Response implements ResponseInterface
{
    public function __construct(array $allowedMethods)
    {
        parent::__construct('', [
          'Allow' => implode(', ', $allowedMethods),
        ], 405);
    }
}

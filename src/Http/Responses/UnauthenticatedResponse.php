<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class UnauthenticatedResponse extends Response implements ResponseInterface
{
    public function __construct($message = 'Unauthenticated')
    {
        parent::__construct($message, [], 401);
    }

    public function renderBody()
    {
        return $this->body;
    }
}

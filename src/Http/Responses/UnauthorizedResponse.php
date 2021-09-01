<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class UnauthorizedResponse extends Response implements ResponseInterface
{
    public function __construct($message = 'Unauthorized')
    {
        parent::__construct($message, [], 403);
    }

    public function renderBody()
    {
        return $this->body;
    }
}

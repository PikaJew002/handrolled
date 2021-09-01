<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class RequestTimedOutResponse extends Response implements ResponseInterface
{
    public function __construct($message = 'Request timed out.')
    {
        parent::__construct($message, [], 419);
    }

    public function renderBody()
    {
        return $this->body;
    }
}

<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class BadRequestResponse extends Response implements ResponseInterface
{
    public function __construct($message = 'Bad Request')
    {
        parent::__construct($message, [], 400);
    }

    public function renderBody()
    {
        return $this->body;
    }
}

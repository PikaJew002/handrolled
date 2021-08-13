<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class NotFoundResponse extends Response implements ResponseInterface
{
    public function __construct()
    {
        parent::__construct('', [], 404);
    }

    public function renderBody()
    {
        return $this->body;
    }
}

<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class ServerErrorResponse extends HttpErrorResponse implements ResponseInterface
{
    public function __construct(string $message = 'Server Error')
    {
        parent::__construct(500, $message);
    }
}

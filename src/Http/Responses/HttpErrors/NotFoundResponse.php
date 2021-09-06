<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class NotFoundResponse extends HttpErrorResponse implements ResponseInterface
{
    public function __construct(string $message = 'Not Found')
    {
        parent::__construct(404, $message);
    }
}

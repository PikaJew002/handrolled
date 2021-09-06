<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class BadRequestResponse extends HttpErrorResponse implements ResponseInterface
{
    public function __construct($message = 'Bad Request')
    {
        parent::__construct(400, $message);
    }
}

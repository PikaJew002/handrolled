<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class RequestTimeoutResponse extends HttpErrorResponse implements ResponseInterface
{
    public function __construct($message = 'Request Timeout')
    {
        parent::__construct(408, $message);
    }
}

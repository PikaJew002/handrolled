<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class RequestTimeoutResponse extends HttpErrorResponse
{
    public function __construct($message = 'Request Timeout')
    {
        parent::__construct(408, $message);
    }
}

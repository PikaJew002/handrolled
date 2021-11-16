<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class ServerErrorResponse extends HttpErrorResponse
{
    public function __construct(array $headers = [], string $message = 'Server Error')
    {
        parent::__construct(500, $message, $headers);
    }
}

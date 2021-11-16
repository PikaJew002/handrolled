<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class NotFoundResponse extends HttpErrorResponse
{
    public function __construct(array $headers = [], string $message = 'Not Found')
    {
        parent::__construct(404, $message, $headers);
    }
}

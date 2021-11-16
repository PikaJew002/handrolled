<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class UnauthorizedResponse extends HttpErrorResponse
{
    public function __construct(array $headers = [], string $message = 'Unauthorized')
    {
        parent::__construct(401, $message, $header);
    }
}

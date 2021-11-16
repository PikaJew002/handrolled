<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class BadRequestResponse extends HttpErrorResponse
{
    public function __construct(array $headers = [], string $message = 'Bad Request')
    {
        parent::__construct(400, $message, $headers);
    }
}

<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class ForbiddenResponse extends HttpErrorResponse
{
    public function __construct(array $headers = [], string $message = 'Forbidden')
    {
        parent::__construct(403, $message, $headers);
    }
}

<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class RequestTimeoutResponse extends HttpErrorResponse
{
    public function setInitial(int $code = 408, string $message = 'Request Timeout'): void
    {
        parent::setInitial($code, $message);
    }
}

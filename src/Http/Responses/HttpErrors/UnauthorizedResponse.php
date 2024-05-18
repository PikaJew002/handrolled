<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class UnauthorizedResponse extends HttpErrorResponse
{
    public function setInitial(int $code = 401, string $message = 'Unauthorized'): void
    {
        parent::setInitial($code, $message);
    }
}

<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class ForbiddenResponse extends HttpErrorResponse
{
    public function setInitial(int $code = 403, string $message = 'Forbidden'): void
    {
        parent::setInitial($code, $message);
    }
}

<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class NotFoundResponse extends HttpErrorResponse
{
    public function setInitial(int $code = 404, string $message = 'Not Found'): void
    {
        parent::setInitial($code, $message);
    }
}

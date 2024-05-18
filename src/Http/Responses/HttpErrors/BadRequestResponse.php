<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class BadRequestResponse extends HttpErrorResponse
{
    public function setInitial(int $code = 400, string $message = 'Bad Request'): void
    {
        parent::setInitial($code, $message);
    }
}

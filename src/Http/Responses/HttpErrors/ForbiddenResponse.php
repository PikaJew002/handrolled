<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class ForbiddenResponse extends HttpErrorResponse implements ResponseInterface
{
    public function __construct(string $message = 'Forbidden')
    {
        parent::__construct(403, $message);
    }
}

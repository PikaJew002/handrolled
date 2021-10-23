<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class MethodNotAllowedResponse extends HttpErrorResponse
{
    public function __construct(array $allowedMethods, $message = 'Method Not Allowed')
    {
        parent::__construct(405, $message, ['Allow' => implode(', ', $allowedMethods)]);
    }
}

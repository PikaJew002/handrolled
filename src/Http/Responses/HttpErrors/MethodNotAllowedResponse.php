<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;

class MethodNotAllowedResponse extends HttpErrorResponse
{
    public function __construct(array $allowedMethods, array $headers = [], string $message = 'Method Not Allowed')
    {
        parent::__constructs(405, $message, array_merge($headers, ['Allow' => implode(', ', $allowedMethods)]));
    }
}

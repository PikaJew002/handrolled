<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class MethodNotAllowedResponse extends HttpErrorResponse implements ResponseInterface
{
    public function __construct(array $allowedMethods, $message = 'Method Not Allowed')
    {
        parent::__construct(405, $message, ['Allow' => implode(', ', $allowedMethods)]);
    }
}

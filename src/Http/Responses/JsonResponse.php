<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class JsonResponse extends Response implements ResponseInterface
{
    public function __construct(array $jsonBody, int $code = 200)
    {
        parent::__construct($jsonBody, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, private',
        ], $code);
    }
}

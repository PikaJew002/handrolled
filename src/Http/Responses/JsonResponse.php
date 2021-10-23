<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;

class JsonResponse extends Response
{
    public function __construct(array $jsonBody, int $code = 200)
    {
        parent::__construct($jsonBody, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, private',
        ], $code);
    }
}

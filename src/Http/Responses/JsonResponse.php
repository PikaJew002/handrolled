<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class JsonResponse extends Response
{
    public function __construct()
    {
        parent::setIntial(
            200,
            ['Content-Type' => 'application/json', 'Cache-Control' => 'no-cache, private'],
            json_encode([])
        );
    }

    public static function make($code, array $body = [], array $headers = []): ResponseInterface
    {
        if(is_array($code)) {
            $headers = $body;
            $body = $code;
            $code = 200;
        }

        $instance = new static();
        foreach($headers as $key => $header) {
            $instance->setHeader($key, $header);
        }

        return $instance->setBody(json_encode($body));
    }

    public function with(array $body): ResponseInterface
    {
        return $this->setBody(json_encode($body));
    }
}

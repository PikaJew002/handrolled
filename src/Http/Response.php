<?php

namespace PikaJew002\Handrolled\Http;

use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class Response implements ResponseInterface
{
    public $body;
    public array $headers;
    public int $responseCode;

    public function __construct($body, array $headers, int $code = 200)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->responseCode = $code;
    }

    public function renderBodyToJson(): void
    {
        echo json_encode($this->body);
    }

    public function renderBodyAsHtml(): void
    {
        echo $this->body;
    }

    public function renderBody()
    {
        $this->renderBodyToJson();
    }

    public function render()
    {
        http_response_code($this->responseCode);
        foreach($this->headers as $headerKey => $headerValue) {
            header($headerKey.': '.$headerValue);
        }
        $this->renderBody();
    }
}

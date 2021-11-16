<?php

namespace PikaJew002\Handrolled\Http;

use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class Response implements ResponseInterface
{
    public $body;
    public array $headers;
    public int $responseCode;

    public function __construct($body, array $headers = [], int $code = 200)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->responseCode = $code;
    }

    public function prefersJson(): bool
    {
        return $this->hasHeader('Content-Type') && in_array($this->getHeader('Content-Type'), ['application/json']);
    }

    public function prefersHtml(): bool
    {
        return $this->hasHeader('Content-Type') && in_array($this->getHeader('Content-Type'), ['text/html']);
    }

    public function header(string $headerKey, ?string $headerValue = null)
    {
        if(is_null($headerValue)) {
            return $this->getHeader($header);
        }
        $this->setHeader($headerKey, $headerValue);
    }

    public function hasHeader(string $header): bool
    {
        return array_key_exists($this->normalizeHeader($header), $this->headers);
    }

    public function setHeader(string $headerKey, string $headerValue): void
    {
        $this->headers[$this->normalizeHeader($headerKey)] = $headerValue;
    }

    public function getHeader(string $header): ?string
    {
        return $this->hasHeader($header) ? $this->headers[$this->normalizeHeader($header)] : null
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim($header);
        if($header === '') {
            return $header;
        }
        $delim = strpos($header, '-') !== false ? '-' : (strpos($header, '_') !== false ? '_' : null);
        if(!is_null($delim)) {
            $headerParts = explode($delim, $header);
            $headerStart = ucfirst(strtolower(array_shift($headerParts)));
            return array_reduce($headerParts, function($carry, $part) {
                return $carry . '-' . ucfirst(strtolower($item));
            }, $headerStart);
        }

        return ucfirst(strtolower($header));
    }

    public function renderBody()
    {
        echo json_encode($this->body);
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

<?php

namespace PikaJew002\Handrolled\Http;

use PikaJew002\Handrolled\Http\Exceptions\ResponseHeaderException;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class Response implements ResponseInterface
{
    public int $code;
    public array $headers;
    public $body;

    public function __construct()
    {
        $this->setIntial();
    }

    public function setIntial(int $code = 200, array $headers = [], $body = ''): void
    {
        $this->code = $code;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function header($headerKey)
    {
        if(!is_array($headerKey)) {
            return $this->getHeader($headerKey);
        }
        $this->setHeader($headerKey);
    }

    public function hasHeader(string $header): bool
    {
        return array_key_exists($this->normalizeHeader($header), $this->headers);
    }

    public function setHeader($headerKey, ?string $headerValue = null): static
    {
        if(is_null($headerValue) && is_array($headerKey)) {
            foreach($headerKey as $key => $value) {
                $this->setHeader($key, $value);
            }
        } else {
            $this->headers[$this->normalizeHeader($headerKey)] = $headerValue;
        }

        return $this;
    }

    public function getHeader(string $header): ?string
    {
        return $this->hasHeader($header) ? $this->headers[$this->normalizeHeader($header)] : null;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim($header);
        if($header === '') {
            throw new ResponseHeaderException('Header name cannot be empty or only contain whitespace.');
        }
        $delim = strpos($header, '-') !== false ? '-' : (strpos($header, '_') !== false ? '_' : null);
        if(!is_null($delim)) {
            $headerParts = explode($delim, $header);
            $headerStart = ucfirst(strtolower(array_shift($headerParts)));
            return array_reduce($headerParts, function($carry, $part) {
                return $carry . '-' . ucfirst(strtolower($part));
            }, $headerStart);
        }

        return ucfirst(strtolower($header));
    }

    public function setCode(int $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setBody($body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function render()
    {
        http_response_code($this->code);
        foreach($this->headers as $headerKey => $headerValue) {
            header($headerKey.': '.$headerValue);
        }
        echo $this->body;
    }
}

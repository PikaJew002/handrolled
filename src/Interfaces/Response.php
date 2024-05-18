<?php

namespace PikaJew002\Handrolled\Interfaces;

interface Response
{
    public function render();
    public function header($header);
    public function hasHeader(string $header): bool;
    public function getHeader(string $header);
    public function setHeader($headerKey, ?string $headerValue): Response;
    public function getBody();
    public function setBody($body): Response;
    public function getCode(): int;
    public function setCode(int $code): Response;
}

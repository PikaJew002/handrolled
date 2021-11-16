<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;

class HttpErrorResponse extends Response
{
    protected array $error;

    public function __construct(int $code, string $message, array $headers = [])
    {
        $this->error = [
            'http_code' => $code,
            'http_message' => $message,
        ];
        parent::__construct(['message' => $message], $headers, $code);
    }

    public function renderBody()
    {
        $error = $this->error;
        include(__DIR__.'/../Views/error-page.php');
    }
}

<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class HttpErrorResponse extends Response implements ResponseInterface
{
    protected array $error;

    public function __construct(int $code, string $message, array $headers = [])
    {
        $this->error = [
            'http_code' => $code,
            'http_message' => $message,
        ];
        parent::__construct('', $headers, $code);
    }

    public function renderBody()
    {
        $error = $this->error;
        include(__DIR__.'/../Views/error-page.php');
    }
}

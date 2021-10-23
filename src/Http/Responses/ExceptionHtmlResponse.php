<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use Throwable;

class ExceptionHtmlResponse extends Response
{
    public Throwable $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;

        parent::__construct('', [
            'Content-Type' => 'text/html',
        ], 200);
    }

    public function renderBody()
    {
        $exception = $this->exception;
        include(__DIR__.'/../Views/debug-page.php');
    }
}

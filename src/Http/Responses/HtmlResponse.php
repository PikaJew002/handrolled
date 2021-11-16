<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;

class HtmlResponse extends Response
{
    public function __construct(string $htmlBody = '')
    {
        parent::__construct($htmlBody, [
            'Content-Type' => 'text/html',
        ], 200);
    }

    public function renderBody()
    {
        echo $this->body;
    }
}

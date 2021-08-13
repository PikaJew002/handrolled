<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class HtmlResponse extends Response implements ResponseInterface
{
    public ?string $pathToFile;

    public function __construct(string $pathToFile = null)
    {
        $this->pathToFile = $pathToFile;
        parent::__construct('', [
            'Content-Type' => 'text/html',
        ], 200);
    }

    public function renderBody()
    {
        include($this->pathToFile);
    }
}

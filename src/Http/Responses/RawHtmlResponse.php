<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

class RawHtmlResponse extends HtmlResponse implements ResponseInterface
{
    public function __construct(string $rawHtmlBody)
    {
        parent::__construct(null);
        $this->body = $rawHtmlBody;
    }

    public function renderBody()
    {
        echo $this->body;
    }
}

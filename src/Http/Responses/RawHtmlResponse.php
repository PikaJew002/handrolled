<?php

namespace PikaJew002\Handrolled\Http\Responses;

class RawHtmlResponse extends HtmlResponse
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

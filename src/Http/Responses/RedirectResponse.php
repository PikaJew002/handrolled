<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\ResponseUsesApplication;

class RedirectResponse extends Response implements ResponseUsesApplication
{
    public function __construct(string $redirectTo)
    {
        parent::__construct('', [
            'Location' => $redirectTo,
        ], 303);
    }

    public function buildFromApp(Application $app): self
    {
        $this->headers['Location'] = $app->config('app.url').$this->headers['Location'];

        return $this;
    }

    public function renderBody()
    {
        echo $this->body;
    }
}

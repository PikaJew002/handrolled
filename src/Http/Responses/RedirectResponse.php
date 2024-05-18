<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;
use PikaJew002\Handrolled\Support\Configuration;

class RedirectResponse extends Response
{
    protected Configuration $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
        parent::setIntial(
            303,
            ['Content-Type' => 'text/html']
        );
    }

    public function to(string $redirectTo): ResponseInterface
    {
        $appUrl = $this->config->get('app.url');
        if(strlen($redirectTo) > 0) {
            if(!$this->isValidUrl($redirectTo)) {
                if(substr($redirectTo, 0, 1) !== '/') {
                    $redirectTo = $appUrl.'/'.$redirectTo;
                } else {
                    $redirectTo = $appUrl.$redirectTo;
                }
            }
        } else {
            $redirectTo = $appUrl.'/';
        }
        return $this->setHeader('Location', $redirectTo)->setBody(sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url=\'%1$s\'" />
        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($redirectTo, \ENT_QUOTES, 'UTF-8')));
    }

    private function isValidUrl($url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) === $url;
    }
}

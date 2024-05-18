<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Request;
use PikaJew002\Handrolled\Support\Configuration;
use Throwable;
use Twig\Environment;

class ExceptionResponse extends Response
{
    public function __construct(Throwable $exception, Request $request, Configuration $config, Environment $twig)
    {
        $props = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
        ];
        if(($request->acceptsHtml()) || $config->get('app.response_type') === 'text/html') {
            parent::setIntial(
                500,
                ['Content-Type' => 'text/html'],
                $twig->render('debug-page.twig.html', $props)
            );
        } else {
            parent::setIntial(
                500,
                ['Content-Type' => 'application/json', 'Cache-Control' => 'no-cache, private'],
                json_encode($props)
            );
        }
    }
}

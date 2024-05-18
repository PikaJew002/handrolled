<?php

namespace PikaJew002\Handrolled\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;
use PikaJew002\Handrolled\Interfaces\Request;
use PikaJew002\Handrolled\Support\Configuration;
use Twig\Environment as TwigEnvironment;

class MethodNotAllowedResponse extends HttpErrorResponse
{
    public function __construct(array $allowedMethods, Request $request, Configuration $config, TwigEnvironment $twig)
    {
        parent::__construct($request, $config, $twig);

        $this->setHeader(['Allow' => implode(', ', $allowedMethods)]);
    }

    public function setInitial(int $code = 405, string $message = 'Method Not Allowed'): void
    {
        parent::setInitial($code, $message);
    }
}

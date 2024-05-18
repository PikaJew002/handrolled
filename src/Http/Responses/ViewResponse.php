<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;
use PikaJew002\Handrolled\Support\Configuration;
use Twig\Environment as TwigEnvironment;

class ViewResponse extends Response
{
    protected TwigEnvironment $twig;
    protected Configuration $config;

    public function __construct(TwigEnvironment $twig, Configuration $config)
    {
        $this->twig = $twig;
        $this->config = $config;
        parent::setIntial(200, ['Content-Type' => 'text/html']);
    }

    public function use(string $template, array $props = []): ResponseInterface
    {
        $this->body = $this->twig->render($template, array_merge(['app' => $this->config->get('app')], $props));

        return $this;
    }
}

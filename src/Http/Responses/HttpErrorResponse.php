<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Interfaces\Request;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;
use PikaJew002\Handrolled\Support\Configuration;
use Twig\Environment as TwigEnvironment;

class HttpErrorResponse extends Response
{
    protected string $message;
    protected bool $isHtmlReponse;
    protected Configuration $config;
    protected TwigEnvironment $twig;

    public function __construct(Request $request, Configuration $config, TwigEnvironment $twig)
    {
        $this->isHtmlReponse = $request->acceptsHtml();
        $this->config = $config;
        $this->twig = $twig;
        $this->setInitial();
    }

    public function setInitial(int $code = 500, string $message = 'Server Error'): void
    {
        $this->message = $message;
        $props = ['code' => $code, 'message' => $this->message];
        if ($this->isHtmlResponse()) {
            parent::setIntial(
                $code,
                ['Content-Type' => 'text/html'],
                $this->twig->render('error-page.twig.html', $props)
            );
        } else {
            parent::setIntial(
                $code,
                ['Content-Type' => 'application/json', 'Cache-Control' => 'no-cache, private'],
                json_encode($props)
            );
        }
    }

    private function isHtmlResponse(): bool
    {
        return $this->isHtmlReponse || $this->config->get('app.response_type', 'application/json') === 'text/html';
    }

    public function setCode(int $code): static
    {
        $this->code = $code;
        $this->resetBody();

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        $this->resetBody();

        return $this;
    }

    private function resetBody(): void
    {
        $props = ['code' => $this->code, 'message' => $this->message];
        if ($this->isHtmlResponse()) {
            $this->setBody($this->twig->render('error-page.twig.html', $props));
        } else {
            $this->setBody(json_encode($props));
        }
    }
}

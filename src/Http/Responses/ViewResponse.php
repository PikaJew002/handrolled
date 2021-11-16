<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Interfaces\ResponseUsesApplication;
use PikaJew002\Handrolled\Support\Configuration;
use Twig\Environment;

class ViewResponse extends HtmlResponse implements ResponseUsesApplication
{
    protected string $template;
    protected array $props;

    public function __construct(string $template, array $props = [])
    {
        parent::__construct();
        $this->template = $template;
        $this->props = $props;
    }

    public function buildFromApp(Application $app): self
    {
        $this->body = $app->get(Environment::class)->render($this->template, array_merge(['app' => $app->config('app')], $this->props));

        return $this;
    }
}

<?php

namespace PikaJew002\Handrolled\Http\Responses;

use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;
use PikaJew002\Handrolled\Interfaces\Container;
use Twig\Environment;

class ViewResponse extends RawHtmlResponse implements ResponseInterface
{
    protected string $template;
    protected array $props;

    public function __construct(string $template, array $props = [])
    {
        parent::__construct('');
        $this->template = $template;
        $this->props = $props;
    }

    public function buildFromContainer(Container $container): self
    {
        $this->body = $container->get(Environment::class)->render($this->template, $this->props);
        return $this;
    }

    public function renderBody()
    {
        echo $this->body;
    }
}

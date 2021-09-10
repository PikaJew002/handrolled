<?php

namespace PikaJew002\Handrolled\Router\Definition;

use Closure;

class Route
{
    public string $method;
    public string $uri;
    public array $handler = [
        'class' => null,
        'method' => null,
        'closure' => null,
        'middleware' => [],
    ];
    /**
     * Array (
     *  [class] => string,
     *  [method] => string,
     *  [closure] => Closure,
     *  [middleware] => Array,
     * )
     *
     */

    public function __construct(string $method, string $uri, $handler)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->handler['middleware'] = [];
        $this->processHandler($handler);
    }

    public function addMiddleware(array $middleware): self
    {
        $this->handler['middleware'] = array_merge($this->handler['middleware'], $middleware);

        return $this;
    }

    protected function processHandler($handler): void
    {
        if(is_array($handler)) {
            if(count($handler) === 2) {
                $this->handler['class'] = $handler[0];
                $this->handler['method'] = $handler[1];
            } else {
                $strError = var_export($handler, true);
                throw new Exception("Badly formed route handler: {$strError}");
            }
        } else if(is_callable($handler)) {
            $this->handler['closure'] = $handler;
        } else if(is_string($handler)) {
            $this->handler['class'] = $handler;
            $this->handler['method'] = '__invoke';
        } else {
            $strError = var_export($handler, true);
            throw new Exception("Badly formed route handler: {$strError}");
        }
    }
}

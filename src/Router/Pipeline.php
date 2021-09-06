<?php

namespace PikaJew002\Handrolled\Router;

use PikaJew002\Handrolled\Interfaces\Container;
use PikaJew002\Handrolled\Http\Request;
use Closure, Exception;

class Pipeline
{
    protected Container $container;
    protected Request $request;
    protected array $pipes;

    public function __construct(Container $container, Request $request, array $pipes)
    {
        $this->container = $container;
        $this->request = $request;
        $this->pipes = $pipes;
    }

    public function resolveToResponse(Closure $resolver)
    {
        $pipeline = array_reduce(
            array_reverse($this->getPipes()),
            $this->carryFunc(),
            $this->prepareResolver($resolver)
        );

        return $pipeline($this->request);
    }

    public function getPipes()
    {
        return $this->pipes;
    }

    protected function prepareResolver(Closure $resolver)
    {
        return function($request) use ($resolver) {
            return $resolver($request);
        };
    }

    protected function carryFunc()
    {
        return function($stack, $pipe) {
            return function($request) use ($stack, $pipe) {
                return $this->container->get($pipe)->handler($request, $stack);
            };
        };
    }
}

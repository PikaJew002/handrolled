<?php

namespace PikaJew002\Handrolled\Router;

use Closure;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Interfaces\Container;
use PikaJew002\Handrolled\Interfaces\Response;

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

    // credit to Taylor Otwell (Laravel framework)
    // https://github.com/laravel/framework/blob/8.x/src/Illuminate/Pipeline/Pipeline.php#L97
    public function resolveToResponse(Closure $resolver): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->getPipes()),
            $this->carryFunc(),
            $this->prepareResolver($resolver)
        );

        return $pipeline($this->request);
    }

    public function getPipes(): array
    {
        return $this->pipes;
    }

    protected function prepareResolver(Closure $resolver): Closure
    {
        return function($request) use ($resolver) {
            return $resolver($request);
        };
    }

    protected function carryFunc(): Closure
    {
        return function($stack, $pipe) {
            return function($request) use ($stack, $pipe) {
                return $this->container->get($pipe)->handler($request, $stack);
            };
        };
    }
}

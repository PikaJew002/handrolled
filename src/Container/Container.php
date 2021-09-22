<?php

namespace PikaJew002\Handrolled\Container;

use Exception;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;

class Container implements ContainerInterface
{
    protected static $containerInstance;
    protected array $objectBindings;
    protected array $aliasBindings;
    protected array $valueBindings;

    public static function setInstance(ContainerInterface $container = null)
    {
        return static::$containerInstance = $container;
    }

    public static function getInstance()
    {
        if(is_null(static::$containerInstance)) {
            static::$containerInstance = new static;
        }

        return static::$containerInstance;
    }

    public function get($abstract): object
    {
        // get object binding from alias(es), if present
        $abstract = $this->getAlias($abstract);
        // Cheack for abstract in object bindings
        if(isset($this->objectBindings[$abstract])) {
            return $this->objectBindings[$abstract]($this);
        }
        $reflection = new ReflectionClass($abstract);
        $dependencies = $this->buildDependencies($reflection, $abstract);
        $this->set($abstract, function(self $c) use ($abstract, $dependencies) {
            return new $abstract(...$dependencies);
        });

        return $reflection->newInstanceArgs($dependencies);
    }

    public function getAlias(string $abstract): string
    {
        return isset($this->aliasBindings[$abstract]) ? $this->getAlias($this->aliasBindings[$abstract]) : $abstract;
    }

    public function getValue($abstract, $pos)
    {
        return $this->valueBindings[$abstract.':'.$pos];
    }

    public function hasValue($abstract, $pos): bool
    {
        return isset($this->valueBindings[$abstract.':'.$pos]);
    }

    public function setAlias($alias, $abstract): bool
    {
        $this->aliasBindings[$alias] = $abstract;
        return true;
    }

    public function setValue($abstract, $pos, $value): void
    {
        if(isset($this->valueBindings[$abstract.':'.$pos])) {
            return;
        }

        $this->valueBindings[$abstract.':'.$pos] = $value;
    }

    public function set($abstract, callable $factory)
    {
        $this->objectBindings[$abstract] = $factory;
    }

    protected function buildDependencies(ReflectionClass $reflection, string $abstract): array
    {
        $constructor = $reflection->getConstructor();
        if(is_null($constructor)) {
            return [];
        }
        $parameters = [];
        foreach($constructor->getParameters() as $index => $param) {
            $parameterType = $param->getType();
            if(!($parameterType instanceof ReflectionNamedType)) {
                // Union types are not supported
                throw new Exception("Parameters must be type-hinted with class name. No union types allowed. Class: $abstract, ParamType: $parameterType");
            }
            if($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
                continue;
            }
            $parameters[] = $this->get($parameterType->getName());
        }

        return $parameters;
    }
}

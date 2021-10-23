<?php

namespace PikaJew002\Handrolled\Container;

use Exception;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;

class Container implements ContainerInterface
{
    protected static $containerInstance;
    protected array $factories;
    protected array $instances;
    protected array $singletons;
    protected array $aliases;

    public function __construct()
    {
        $this->instances = [];
        $this->factories = [];
        $this->aliases = [];
        $this->singletons = [];
    }

    public static function setInstance(?ContainerInterface $container = null)
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

    public function set($abstract, callable $factory): void
    {
        $this->factories[$abstract] = $factory;
    }

    public function hasFactory($abstract): bool
    {
        return isset($this->factories[$abstract]);
    }

    public function setAlias($alias, $abstract): void
    {
        $this->aliases[$alias] = $abstract;
    }

    public function hasAlias($alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    public function registerSingletons($abstracts): void
    {
        foreach((array) $abstracts as $abstract) {
            if(!$this->hasSingleton($abstract)) {
                $this->singletons[] = $abstract;
            }
        }
    }

    public function hasSingleton($abstract): bool
    {
        return in_array($abstract, $this->singletons);
    }

    public function get($abstract): object
    {
        // get object binding from alias(es), if present
        $abstract = $this->getAlias($abstract);
        // check for existing instance
        if(isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        // check for abstract in object bindings
        if(isset($this->factories[$abstract])) {
            if(in_array($abstract, $this->singletons)) {
                return $this->instances[$abstract] = $this->factories[$abstract]($this);
            }
            return $this->factories[$abstract]($this);
        }
        $reflection = new ReflectionClass($abstract);
        $dependencies = $this->buildDependencies($reflection, $abstract);
        if(in_array($abstract, $this->singletons)) {
            return $this->instances[$abstract] = $reflection->newInstanceArgs($dependencies);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    public function getAlias($abstract): string
    {
        return isset($this->aliases[$abstract]) ? $this->getAlias($this->aliases[$abstract]) : $abstract;
    }

    public function flush(): void
    {
        $this->instances = [];
        $this->factories = [];
        $this->aliases = [];
        $this->singletons = [];
    }

    protected function buildDependencies(ReflectionClass $reflection, string $abstract): array
    {
        $constructor = $reflection->getConstructor();
        if(is_null($constructor)) {
            return [];
        }
        $parameters = [];
        foreach($constructor->getParameters() as $index => $param) {
            if($param->isDefaultValueAvailable()) {
                $parameters[] = $param->getDefaultValue();
                continue;
            }
            $parameterType = $param->getType();
            if(!($parameterType instanceof ReflectionNamedType)) {
                // Union types are not supported
                $pos = $index + 1;
                throw new Exception("Parameters must be type-hinted with class name. No union types allowed. Class: $abstract, Argument $pos, ParamType: $parameterType");
            }
            if($parameterType->isBuiltin()) {
                // type string|int|bool, etc

            }
            $parameters[] = $this->get($parameterType->getName());
        }

        return $parameters;
    }
}

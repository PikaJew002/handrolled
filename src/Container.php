<?php

namespace PikaJew002\Handrolled;

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
        $this->factories = [];
        $this->instances = [];
        $this->singletons = [];
        $this->aliases = [];
        static::setInstance($this);
    }

    public static function setInstance(?ContainerInterface $container = null): void
    {
        static::$containerInstance = $container ?? new static;
    }

    public static function getInstance(): self
    {
        return static::$containerInstance ?? static::$containerInstance = new static;
    }

    public function set($abstract, callable $factory): void
    {
        $this->factories[$abstract] = $factory;
    }

    public function hasFactory($abstract): bool
    {
        return isset($this->factories[$this->getAlias($abstract)]);
    }

    public function setAlias($alias, $abstract): void
    {
        $this->aliases[$alias] = $abstract;
    }

    public function hasAlias($alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    public function getAlias($abstract)
    {
        return $this->hasAlias($abstract) ? $this->getAlias($this->aliases[$abstract]) : $abstract;
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

    public function hasInstance($abstract): bool
    {
        return isset($this->instances[$this->getAlias($abstract)]);
    }

    public function get($abstract, array $parameters = [], bool $useFactory = true): object
    {
        // get object binding from alias(es), if present
        $abstract = $this->getAlias($abstract);
        // check for existing instance
        if(isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        // check for abstract in factory bindings
        if($useFactory && $this->hasFactory($abstract)) {
            if($this->hasSingleton($abstract)) {
                return $this->instances[$abstract] = $this->factories[$abstract]($this, ...$parameters);
            }
            return $this->factories[$abstract]($this, ...$parameters);
        }
        $reflection = new ReflectionClass($abstract);
        $dependencies = $this->buildDependencies($reflection, $abstract, $parameters);
        if($this->hasSingleton($abstract)) {
            return $this->instances[$abstract] = $reflection->newInstanceArgs($dependencies);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    public function flush(): void
    {
        $this->instances = [];
        $this->factories = [];
        $this->aliases = [];
        $this->singletons = [];
    }

    protected function buildDependencies(ReflectionClass $reflection, string $abstract, array $paramsProvidors): array
    {
        $constructor = $reflection->getConstructor();
        if(is_null($constructor)) {
            return [];
        }
        $parameters = [];
        foreach($constructor->getParameters() as $index => $param) {
            if(array_key_exists($index, $paramsProvidors)) {
                $parameters[] = $paramsProvidors[$index];
                continue;
            }
            if($param->isDefaultValueAvailable()) {
                $parameters[] = $param->getDefaultValue();
                continue;
            }
            $parameterType = $param->getType();
            // Union and Intersection types are not supported, throw Exception
            if(!($parameterType instanceof ReflectionNamedType)) {
                $pos = $index + 1;
                throw new Exception("Parameter $pos of type $parameterType of class $abstract must be type-hinted with single class name. No union or intersection types allowed.");
            }
            if($parameterType->isBuiltin()) {
                
            }
            // param type is class that can be instantiated from contianer
            $parameters[] = $this->get($parameterType->getName());
        }

        return $parameters;
    }
}

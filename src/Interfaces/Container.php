<?php

namespace PikaJew002\Handrolled\Interfaces;

interface Container
{
    public function hasInstance($abstract): bool;
    public function get($abstract, array $params = [], bool $useFactory = true): object;
    public function set($abstract, callable $factory): void;
    public function setAlias($alias, $abstract): void;
    public function getAlias($abstract);
}

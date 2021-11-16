<?php

namespace PikaJew002\Handrolled\Interfaces;

interface Container
{
    public function get($abstract): object;
    public function set($abstract, callable $factory): void;
    public function setAlias($alias, $abstract): void;
    public function getAlias($abstract);
}

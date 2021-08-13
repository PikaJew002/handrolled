<?php

namespace PikaJew002\Handrolled\Interfaces;

interface Container
{
    public function get($abstract);
    public function set($abstract, callable $factory);
    public function setValue($abstract, $pos, $value);
    public function setAlias($alias, $abstract);
    public function hasValue($abstract, $pos);
    public function getValue($abstract, $pos);
}

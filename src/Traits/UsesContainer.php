<?php

namespace PikaJew002\Handrolled\Traits;

use PikaJew002\Handrolled\Application\Application;

trait UsesContainer
{
    public static $container;

    public static function setContainer(): void
    {
        static::$container = Application::getInstance();
    }

    public static function getContainer(): Application
    {
        if(is_null(static::$container)) {
            static::setContainer();
        }
        return static::$container;
    }
}

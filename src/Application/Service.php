<?php

namespace PikaJew002\Handrolled\Application;

use PikaJew002\Handrolled\Application\Application;

class Service
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
}

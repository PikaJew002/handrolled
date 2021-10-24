<?php

namespace PikaJew002\Handrolled\Interfaces;

use PikaJew002\Handrolled\Application\Application;

interface ResponseUsesApplication
{
    public function buildFromApp(Application $app);
}

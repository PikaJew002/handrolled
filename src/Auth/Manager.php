<?php

namespace PikaJew002\Handrolled\Auth;

use PikaJew002\Handrolled\Interfaces\User;
use PikaJew002\Handrolled\Support\Configuration;

class Manager
{
    public string $userClass;
    public Configuration $config;

    public function __construct(User $user, Configuration $config)
    {
        $this->userClass = $user::class;
        $this->config = $config;
    }
}
